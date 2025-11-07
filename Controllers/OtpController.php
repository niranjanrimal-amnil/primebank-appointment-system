<?php

namespace AppointmentSystem\Controllers;

use Illuminate\Routing\Controller;
use AppointmentSystem\Services\OtpService;
use AppointmentSystem\Requests\GenerateOtpRequest;
use AppointmentSystem\Requests\VerifyOtpRequest;
use AppointmentSystem\Requests\ResendOtpRequest;
use AppointmentSystem\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;

class OtpController extends Controller
{
    private OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Generate and send OTP
     */
    public function generate(GenerateOtpRequest $request): JsonResponse
    {
        $result = $this->otpService->generateAndSend(
            $request->account_number,
            $request->email,
            $request->mobile,
            $request->send_type ?? 'both'
        );

        if ($result['success']) {
            return ResponseHelper::success($result, $result['message']);
        }

        return ResponseHelper::error($result['message'], null, 400, $result['error_code'] ?? null);
    }

    /**
     * Resend OTP
     */
    public function resend(ResendOtpRequest $request): JsonResponse
    {
        $result = $this->otpService->resend($request->account_number);

        if ($result['success']) {
            return ResponseHelper::success($result, $result['message']);
        }

        return ResponseHelper::error($result['message'], null, 400, $result['error_code'] ?? null);
    }

    /**
     * Verify OTP
     */
    public function verify(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->otpService->verify(
            $request->account_number,
            $request->otp_code
        );

        if ($result['success']) {
            return ResponseHelper::success($result, $result['message']);
        }

        return ResponseHelper::error($result['message'], null, 400, $result['error_code'] ?? null);
    }

    /**
     * Check OTP status
     */
    public function checkStatus(string $accountNumber): JsonResponse
    {
        $result = $this->otpService->canRequestOtp($accountNumber);

        return ResponseHelper::success($result, 'OTP status retrieved successfully');
    }
}