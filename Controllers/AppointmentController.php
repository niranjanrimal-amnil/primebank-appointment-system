<?php

namespace AppointmentSystem\Controllers;

use Illuminate\Routing\Controller;
use AppointmentSystem\Services\AppointmentService;
use AppointmentSystem\Requests\CreateAppointmentRequest;
use AppointmentSystem\Requests\GetSlotsRequest;
use AppointmentSystem\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    private AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * Get purposes
     */
    public function getPurposes(string $purposeName = null): JsonResponse
    {
        $result = $this->appointmentService->getPurposes($purposeName);

        if ($result['success']) {
            return ResponseHelper::success($result['data'], 'Purposes retrieved successfully');
        }

        return ResponseHelper::error($result['message'], null, 400, $result['error_code'] ?? null);
    }

    /**
     * Get locations by purpose
     */
    public function getLocations(Request $request, string $purposeId): JsonResponse
    {
        $targetDate = $request->query('target_date');
        
        $result = $this->appointmentService->getLocations($purposeId, $targetDate);

        if ($result['success']) {
            return ResponseHelper::success($result['data'], 'Locations retrieved successfully');
        }

        return ResponseHelper::error($result['message'], null, 400, $result['error_code'] ?? null);
    }

    /**
     * Get users by purpose
     */
    public function getUsers(string $purposeId): JsonResponse
    {
        $result = $this->appointmentService->getUsers($purposeId);

        if ($result['success']) {
            return ResponseHelper::success($result['data'], 'Users retrieved successfully');
        }

        return ResponseHelper::error($result['message'], null, 400, $result['error_code'] ?? null);
    }

    /**
     * Get available slots
     */
    public function getAvailableSlots(GetSlotsRequest $request): JsonResponse
    {
        $result = $this->appointmentService->getAvailableSlots($request->validated());

        if ($result['success']) {
            return ResponseHelper::success($result['data'], 'Available slots retrieved successfully');
        }

        return ResponseHelper::error($result['message'], null, 400, $result['error_code'] ?? null);
    }

    /**
     * Create appointment
     */
    public function create(CreateAppointmentRequest $request): JsonResponse
    {
        $result = $this->appointmentService->createAppointment($request->validated());

        if ($result['success']) {
            return ResponseHelper::success($result['data'], $result['message'], 201);
        }

        return ResponseHelper::error($result['message'], null, 400, $result['error_code'] ?? null);
    }

    /**
     * Get appointments by account number
     */
    public function getByAccount(Request $request, string $accountNumber): JsonResponse
    {
        $status = $request->query('status');
        
        $result = $this->appointmentService->getAppointmentsByAccount($accountNumber, $status);

        if ($result['success']) {
            return ResponseHelper::success($result['data'], 'Appointments retrieved successfully');
        }

        return ResponseHelper::error($result['message'], null, 400, $result['error_code'] ?? null);
    }

    /**
     * Cancel appointment
     */
    public function cancel(Request $request, int $appointmentId): JsonResponse
    {
        $request->validate([
            'account_number' => 'required|string',
        ]);

        $result = $this->appointmentService->cancelAppointment(
            $appointmentId,
            $request->account_number
        );

        if ($result['success']) {
            return ResponseHelper::success(null, $result['message']);
        }

        return ResponseHelper::error($result['message'], null, 400, $result['error_code'] ?? null);
    }
}