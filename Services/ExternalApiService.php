<?php

namespace AppointmentSystem\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ExternalApiService
{
    private Client $client;
    private string $baseUrl;
    private int $cacheTtl;

    public function __construct()
    {
        $this->baseUrl = config('appointment.external_api_base_url');
        $this->cacheTtl = config('appointment.cache.ttl', 3600);
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'verify' => false,
        ]);
    }

    /**
     * Get location dropdown
     */
    public function getLocationDropdown(string $apiKey, ?string $purposeId = null, ?string $targetDate = null): array
    {
        try {
            $params = array_filter([
                'PurposeId' => $purposeId,
                'TargetDate' => $targetDate,
            ]);


            $cacheKey = $this->getCacheKey('locations', $apiKey, $params);

            return Cache::remember($cacheKey, $this->cacheTtl, function () use ($apiKey, $params) {
                $response = $this->client->get('/core-api/api/app/external-api/external-location-dropdown', [
                    'headers' => [
                        'api-key' => $apiKey,
                        'Accept' => 'application/json',
                    ],
                    'query' => $params,
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                
                return $this->formatLocationResponse($data);
            });
        } catch (GuzzleException $e) {
            Log::error('External API Error - Location Dropdown', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            
            throw new \Exception('Failed to fetch locations: ' . $e->getMessage());
        }
    }

    /**
     * Get purpose dropdown
     */
    public function getPurposeDropdown(string $apiKey): array
    {
        try {
            $cacheKey = $this->getCacheKey('purposes', $apiKey);

            return Cache::remember($cacheKey, $this->cacheTtl, function () use ($apiKey) {
                $response = $this->client->get('/core-api/api/app/external-api/external-purpose-dropdown', [
                    'headers' => [
                        'api-key' => $apiKey,
                        'Accept' => 'application/json',
                    ],
                ]);

                
                $data = json_decode($response->getBody()->getContents(), true);
                return $this->formatPurposeResponse($data);
            });
        } catch (GuzzleException $e) {
            Log::error('External API Error - Purpose Dropdown', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            
            throw new \Exception('Failed to fetch purposes: ' . $e->getMessage());
        }
    }

    /**
     * Get user dropdown
     */
    public function getUserDropdown(string $apiKey): array
    {
        try {
            $cacheKey = $this->getCacheKey('users', $apiKey);

            return Cache::remember($cacheKey, $this->cacheTtl, function () use ($apiKey) {
                $response = $this->client->get('/core-api/api/app/external-api/external-user-dropdown', [
                    'headers' => [
                        'api-key' => $apiKey,
                        'Accept' => 'application/json',
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                
                return $this->formatUserResponse($data);
            });
        } catch (GuzzleException $e) {
            Log::error('External API Error - User Dropdown', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            
            throw new \Exception('Failed to fetch users: ' . $e->getMessage());
        }
    }

    /**
     * Get appointment slots
     */
    public function getAppointmentSlots(
        string $apiKey,
        string $date,
        string $locationId,
        string $purposeId,
        string $timeZoneId,
        array $assignedStaffIds = []
    ): array {
        try {
            $params = [
                'Date' => $date,
                'LocationId' => $locationId,
                'PurposeId' => $purposeId,
                'TimeZoneId' => $timeZoneId,
            ];

            if (!empty($assignedStaffIds)) {
                $params['AssignedStaffIds'] = $assignedStaffIds;
            }

            // Don't cache slots - they change frequently
            $response = $this->client->get('/core-api/api/app/external-api/external-appointment-slots', [
                'headers' => [
                    'api-key' => $apiKey,
                    'Accept' => 'application/json',
                ],
                'query' => $params,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            return $this->formatSlotsResponse($data);
        } catch (GuzzleException $e) {
            Log::error('External API Error - Appointment Slots', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'params' => $params,
            ]);
            
            throw new \Exception('Failed to fetch appointment slots: ' . $e->getMessage());
        }
    }

    /**
     * Create appointment
     */
    public function createAppointment(string $apiKey, array $appointmentData): array
    {
        try {
            $response = $this->client->post('/core-api/api/app/external-api/external-appointment', [
                'headers' => [
                    'api-key' => $apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => $appointmentData,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            Log::info('Appointment Created Successfully', [
                'account_number' => $appointmentData['customerIdentificationNumber'] ?? null,
            ]);

            return $data;
        } catch (GuzzleException $e) {
            Log::error('External API Error - Create Appointment', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'data' => $appointmentData,
            ]);
            
            throw new \Exception('Failed to create appointment: ' . $e->getMessage());
        }
    }

    /**
     * Format location response with isDefault flag
     */
    private function formatLocationResponse(array $locations): array
    {
        return array_map(function ($location) {
            return [
                'location_id' => $location['locationId'] ?? null,
                'name' => $location['name'] ?? null,
                'time_zone' => $location['timeZone'] ?? null,
                'address' => $location['address'] ?? null,
                'is_default' => $location['isDefault'] ?? false,
                'created_at' => $location['createdAt'] ?? null,
                'purpose_id' => $location['purposeId'] ?? null,
                'purpose_name' => $location['purposeName'] ?? null,
            ];
        }, $locations);
    }

    /**
     * Format purpose response
     */
    private function formatPurposeResponse(array $purposes): array
    {
        return array_map(function ($purpose) {
            return [
                'id' => $purpose['id'] ?? null,
                'system_name' => $purpose['systemName'] ?? null,
                'display_name' => $purpose['displayName'] ?? null,
                'is_active' => $purpose['isActive'] ?? false,
                'created_at' => $purpose['creationTime'] ?? null,
            ];
        }, $purposes);
    }

    /**
     * Format user response
     */
    private function formatUserResponse(array $users): array
    {
        return array_map(function ($user) {
            return [
                'id' => $user['id'] ?? null,
                'name' => $user['name'] ?? null,
                'email' => $user['email'] ?? null,
                'is_active' => $user['isActive'] ?? false,
                'created_at' => $user['creationTime'] ?? null,
            ];
        }, $users);
    }

    /**
     * Format slots response
     */
    private function formatSlotsResponse(array $slotsData): array
    {
        $slots = [];
        
        if (isset($slotsData['validAppointmentSlots'])) {
            foreach ($slotsData['validAppointmentSlots'] as $slot) {
                $slots[] = [
                    'start_time' => $slot['startTime'] ?? null,
                    'end_time' => $slot['endTime'] ?? null,
                    'available_staffs' => $slot['availableStaffs'] ?? [],
                    'is_valid' => $slot['isValid'] ?? false,
                    'invalid_reason' => $slot['invalidReason'] ?? null,
                ];
            }
        }

        return [
            'date' => $slotsData['date'] ?? null,
            'available_staffs' => $slotsData['availableStaffs'] ?? [],
            'slots' => $slots,
        ];
    }

    /**
     * Generate cache key
     */
    private function getCacheKey(string $type, string $apiKey, array $params = []): string
    {
        $prefix = config('appointment.cache.prefix', 'appointment_system_');
        $keyHash = substr(md5($apiKey), 0, 8);
        $paramsHash = empty($params) ? '' : '_' . md5(json_encode($params));
        
        return $prefix . $type . '_' . $keyHash . $paramsHash;
    }

    /**
     * Clear cache for specific type
     */
    public function clearCache(string $type, string $apiKey): void
    {
        $cacheKey = $this->getCacheKey($type, $apiKey);
        Cache::forget($cacheKey);
    }
}