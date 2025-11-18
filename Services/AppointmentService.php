<?php

namespace AppointmentSystem\Services;

use AppointmentSystem\Models\Appointment;
use AppointmentSystem\Models\ApiKey;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    private ExternalApiService $externalApiService;

    public function __construct(ExternalApiService $externalApiService)
    {
        $this->externalApiService = $externalApiService;
    }

    /**
     * Get locations for a purpose
     */
    public function getLocations(string $purposeId, ?string $targetDate = null): array
    {
        try {

            if (is_null($targetDate)) {
                $targetDate = Carbon::today()->toDateString(); 
            }

            if ($targetDate) {
                try {
                    $parsedDate = Carbon::parse($targetDate)->startOfDay();
                    if ($parsedDate->isBefore(Carbon::today())) {
                        return [
                            'success' => false,
                            'message' => 'Target date cannot be in the past.',
                            'error_code' => 'INVALID_DATE',
                        ];
                    }
                } catch (\Exception $e) {
                    return [
                        'success' => false,
                        'message' => 'Invalid date format for target_date. Use YYYY-MM-DD.',
                        'error_code' => 'INVALID_DATE_FORMAT',
                    ];
                }
            }
            $apiKey = ApiKey::getByPurposeId($purposeId);
            
            if (!$apiKey) {
                return [
                    'success' => false,
                    'message' => 'API key not found for this purpose',
                    'error_code' => 'API_KEY_NOT_FOUND',
                ];
            }

            $locations = $this->externalApiService->getLocationDropdown($apiKey, $purposeId, $targetDate);
                        $formattedLocations = array_map(function ($location) {
                unset($location['created_at']);
                return $location;
            }, $locations);

            return [
                'success' => true,
                'data' => $formattedLocations,
            ];
        } catch (\Exception $e) {
            Log::error('Get Locations Error', [
                'purpose_id' => $purposeId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to fetch locations',
                'error_code' => 'FETCH_FAILED',
            ];
        }
    }

    /**
     * Get all purposes
     */
    public function getPurposes(string $purposeName = null): array
    {
        try {
            // Get any active API key to fetch purposes
            $apiKey = ApiKey::where('purpose_name', $purposeName)
                            ->where('is_active', true)
                            ->first();          
        if (!$apiKey) {
                return [
                    'success' => false,
                    'message' => 'No active API key found',
                    'error_code' => 'NO_API_KEY',
                ];
            }
            // dd($apiKey->api_key);
            $allPurposes = $this->externalApiService->getPurposeDropdown($apiKey->api_key);
            $foundPurpose = null;
             $normalizedPurposeName = str_replace('-', ' ', strtolower($purposeName));
            foreach ($allPurposes as $purpose) {
                // Check system_name and display_name for a match
                if (str_contains(strtolower($purpose['system_name']), strtolower($normalizedPurposeName)) || 
                    str_contains(strtolower($purpose['display_name']), strtolower($normalizedPurposeName))) {
                    
                    $foundPurpose = $purpose;
                    break; 
                }
            }
            if ($foundPurpose) {
                if (empty($apiKey->purpose_id) && !empty($foundPurpose['id'])) {
                    $apiKey->purpose_id = $foundPurpose['id'];
                    $apiKey->save();
                }
                unset($foundPurpose['created_at']);
            }
            return [
                'success' => true,
                'data' => $foundPurpose,
            ];
        } catch (\Exception $e) {
            Log::error('Get Purposes Error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to fetch purposes',
                'error_code' => 'FETCH_FAILED',
            ];
        }
    }

    /**
     * Get users/staff for a purpose
     */
    public function getUsers(string $purposeId): array
    {
        try {
            $apiKey = ApiKey::getByPurposeId($purposeId);
            
            if (!$apiKey) {
                return [
                    'success' => false,
                    'message' => 'API key not found for this purpose',
                    'error_code' => 'API_KEY_NOT_FOUND',
                ];
            }

            $allUsers = $this->externalApiService->getUserDropdown($apiKey);
                        $activeUsers = array_filter($allUsers, function ($user) {
                return $user['is_active'] === true;
            });

                        $formattedUsers = array_map(function ($user) {
                unset($user['created_at']); 
                return $user;
            }, $activeUsers);

            return [
                'success' => true,
                'data' => array_values($formattedUsers),
            ];
        } catch (\Exception $e) {
            Log::error('Get Users Error', [
                'purpose_id' => $purposeId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to fetch users',
                'error_code' => 'FETCH_FAILED',
            ];
        }
    }

    /**
     * Get available appointment slots
     */
    public function getAvailableSlots(array $params): array
    {
        try {
            $apiKey = ApiKey::getByPurposeId($params['purpose_id']);
            
            if (!$apiKey) {
                return [
                    'success' => false,
                    'message' => 'API key not found for this purpose',
                    'error_code' => 'API_KEY_NOT_FOUND',
                ];
            }

             $targetDate = $params['date'] ?? null;
             $locationsResult = $this->getLocations($params['purpose_id'], $targetDate);
            if (!$locationsResult['success']) {
                return $locationsResult;
            }
            $defaultLocationId = null;
            foreach ($locationsResult['data'] as $location) {
                if ($location['is_default'] === true) {
                    $defaultLocationId = $location['location_id'];
                    break;
                }
            }
            if (!$defaultLocationId) {
                return [
                    'success' => false,
                    'message' => 'No default location could be found for this purpose.',
                    'error_code' => 'NO_DEFAULT_LOCATION',
                ];
            }

            //  dd($locationsResult);

            $slots = $this->externalApiService->getAppointmentSlots(
                $apiKey,
                $params['date'],
                $defaultLocationId,
                $params['purpose_id'],
                $params['timezone'] ?? config('appointment.appointment.default_timezone'),
                $params['assigned_staff_ids'] ?? []
            );

            return [
                'success' => true,
                'data' => $slots,
            ];
        } catch (\Exception $e) {
            Log::error('Get Available Slots Error', [
                'params' => $params,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to fetch available slots',
                'error_code' => 'FETCH_FAILED',
            ];
        }
    }

    /**
     * Create appointment
     */
    public function createAppointment(array $data): array
    {
        DB::beginTransaction();
        
        try {
            // Validate OTP is verified
            $otpLog = \AppointmentSystem\Models\OtpLog::where('account_number', $data['account_number'])
                ->where('is_verified', true)
                ->where('status', 'verified')
                ->latest()
                ->first();

            if (!$otpLog) {
                return [
                    'success' => false,
                    'message' => 'Please verify OTP first',
                    'error_code' => 'OTP_NOT_VERIFIED',
                ];
            }

            // Check if OTP was verified within last 30 minutes
            if (Carbon::now()->diffInMinutes($otpLog->verified_at) > 30) {
                return [
                    'success' => false,
                    'message' => 'OTP verification expired. Please verify again',
                    'error_code' => 'OTP_EXPIRED',
                ];
            }

            $apiKey = ApiKey::getByPurposeId($data['purpose_id']);
            
            if (!$apiKey) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'API key not found for this purpose',
                    'error_code' => 'API_KEY_NOT_FOUND',
                ];
            }

            $targetDate = $data['date'] ?? null;
             $locationsResult = $this->getLocations($data['purpose_id'], $targetDate);
            if (!$locationsResult['success']) {
                return $locationsResult;
            }
            $defaultLocationId = null;
            foreach ($locationsResult['data'] as $location) {
                if ($location['is_default'] === true) {
                    $defaultLocationId = $location['location_id'];
                    break;
                }
            }

            // Prepare appointment data for external API
            $appointmentPayload = [
                'customerIdentificationNumber' => $data['account_number'],
                'customerName' => $data['customer_name'],
                'customerEmail' => $data['customer_email'],
                'customerPhoneNumber' => $data['customer_phone'],
                'proposedDateTime' => $data['proposed_date_time'],
                'scheduledDateTime' => $data['scheduled_date_time'] ?? $data['proposed_date_time'],
                'purposeId' => $data['purpose_id'],
                'remarks' => $data['remarks'] ?? '',
                'appointmentMetadata' => $data['appointment_metadata'] ?? '',
                'appointmentTakenDateTime' => Carbon::now()->toIso8601String(),
                'appointmentConfirmedDateTime' => null,
                'customerTimeZone' => $data['customer_timezone'] ?? config('appointment.appointment.default_timezone'),
                'agentTimeZone' => $data['agent_timezone'] ?? config('appointment.appointment.default_timezone'),
                'customerLocationId' => $data['location_id'] ?? $defaultLocationId,
                'assignedStaffId' => $data['assigned_staff_id'] ?? null,
                'referenceIdentifier' => 'APP-' . strtoupper(uniqid()),
            ];

            // Create appointment in external system
            $externalResponse = $this->externalApiService->createAppointment($apiKey, $appointmentPayload);

            // Save appointment locally
            $appointment = Appointment::create([
                'account_number' => $data['account_number'],
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'purpose_id' => $data['purpose_id'],
                'purpose_name' => $data['purpose_name'] ?? '',
                'location_id' => $data['location_id'],
                'location_name' => $data['location_name'] ?? '',
                'assigned_staff_id' => $data['assigned_staff_id'] ?? null,
                'staff_name' => $data['staff_name'] ?? null,
                'proposed_date_time' => $data['proposed_date_time'],
                'scheduled_date_time' => $data['scheduled_date_time'],
                'remarks' => $data['remarks'] ?? null,
                'appointment_metadata' => $data['appointment_metadata'] ?? null,
                'customer_timezone' => $data['customer_timezone'] ?? config('appointment.appointment.default_timezone'),
                'agent_timezone' => $data['agent_timezone'] ?? null,
                'appointment_taken_at' => Carbon::now(),
                'status' => 'confirmed',
            ]);

            DB::commit();

            Log::info('Appointment Created Successfully', [
                'appointment_id' => $appointment->id,
                'account_number' => $data['account_number'],
            ]);

            return [
                'success' => true,
                'message' => 'Appointment created successfully',
                'data' => [
                    'appointment_id' => $appointment->id,
                    'account_number' => $appointment->account_number,
                    'scheduled_date_time' => $appointment->scheduled_date_time->format('Y-m-d H:i:s'),
                    'location_name' => $appointment->location_name,
                    'purpose_name' => $appointment->purpose_name,
                    'status' => $appointment->status,
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Create Appointment Error', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create appointment: ' . $e->getMessage(),
                'error_code' => 'CREATE_FAILED',
            ];
        }
    }

    /**
     * Get appointment by account number
     */
    public function getAppointmentsByAccount(string $accountNumber, string $status = null): array
    {
        try {
            $query = Appointment::byAccountNumber($accountNumber);
            
            if ($status) {
                $query->where('status', $status);
            }

            $appointments = $query->orderBy('scheduled_date_time', 'desc')->get();

            return [
                'success' => true,
                'data' => $appointments->map(function ($appointment) {
                    return [
                        'id' => $appointment->id,
                        'account_number' => $appointment->account_number,
                        'customer_name' => $appointment->customer_name,
                        'scheduled_date_time' => $appointment->scheduled_date_time->format('Y-m-d H:i:s'),
                        'purpose_name' => $appointment->purpose_name,
                        'location_name' => $appointment->location_name,
                        'staff_name' => $appointment->staff_name,
                        'status' => $appointment->status,
                        'remarks' => $appointment->remarks,
                    ];
                }),
            ];
        } catch (\Exception $e) {
            Log::error('Get Appointments Error', [
                'account_number' => $accountNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to fetch appointments',
                'error_code' => 'FETCH_FAILED',
            ];
        }
    }

    /**
     * Cancel appointment
     */
    public function cancelAppointment(int $appointmentId, string $accountNumber): array
    {
        try {   
            $appointment = Appointment::where('id', $appointmentId)
                ->where('account_number', $accountNumber)
                ->first();

            if (!$appointment) {
                return [
                    'success' => false,
                    'message' => 'Appointment not found',
                    'error_code' => 'NOT_FOUND',
                ];
            }

            if ($appointment->status === 'cancelled') {
                return [
                    'success' => false,
                    'message' => 'Appointment already cancelled',
                    'error_code' => 'ALREADY_CANCELLED',
                ];
            }

            if ($appointment->status === 'completed') {
                return [
                    'success' => false,
                    'message' => 'Cannot cancel completed appointment',
                    'error_code' => 'ALREADY_COMPLETED',
                ];
            }

            $appointment->update(['status' => 'cancelled']);

            Log::info('Appointment Cancelled', [
                'appointment_id' => $appointmentId,
                'account_number' => $accountNumber,
            ]);

            return [
                'success' => true,
                'message' => 'Appointment cancelled successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Cancel Appointment Error', [
                'appointment_id' => $appointmentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to cancel appointment',
                'error_code' => 'CANCEL_FAILED',
            ];
        }
    }
}