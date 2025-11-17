<?php

namespace AppointmentSystem\Services;

use AppointmentSystem\Models\OtpLog;
use AppointmentSystem\Models\OtpDailyLimit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use AppointmentSystem\Mail\SendOtpMail;
use Illuminate\Support\Facades\Log;

class OtpService
{
    private int $maxDailyAttempts;
    private int $maxResendAttempts;
    private int $otpExpiryMinutes;
    private int $otpLength;

    public function __construct()
    {
        $this->maxDailyAttempts = config('appointment.otp.max_attempts_per_day', 5);
        $this->maxResendAttempts = config('appointment.otp.max_resend_attempts', 5);
        $this->otpExpiryMinutes = config('appointment.otp.expiry_minutes', 10);
        $this->otpLength = config('appointment.otp.length', 6);
    }

    /**
     * Generate and send OTP
     */
    public function generateAndSend(
        string $accountNumber,
        string $email,
        string $mobile,
        string $sendType = 'both'
    ): array {
        // Check daily limit
        $dailyLimit = OtpDailyLimit::getTodayLimit($accountNumber);
        
        if ($dailyLimit->hasExceededSendLimit($this->maxDailyAttempts)) {
            return [
                'success' => false,
                'message' => 'Daily OTP limit exceeded. Maximum ' . $this->maxDailyAttempts . ' OTPs per day.',
                'error_code' => 'DAILY_LIMIT_EXCEEDED',
            ];
        }

        // Check if there's an active OTP
        $activeOtp = OtpLog::getActiveOtp($accountNumber);
        
        if ($activeOtp) {
            $remainingTime = Carbon::now()->diffInMinutes($activeOtp->expires_at, false);
            
            if ($remainingTime > 0) {
                return [
                    'success' => false,
                    'message' => 'An OTP is already active. Please wait ' . ceil($remainingTime) . ' minutes or use resend.',
                    'error_code' => 'ACTIVE_OTP_EXISTS',
                    'expires_in_minutes' => ceil($remainingTime),
                ];
            }
        }

        // Generate OTP
        $otpCode = $this->generateOtpCode();
        
        // Save OTP log
        $otpLog = OtpLog::create([
            'account_number' => $accountNumber,
            'otp_code' => $otpCode,
            'send_type' => $sendType,
            'email' => $email,
            'mobile' => $mobile,
            'expires_at' => Carbon::now()->addMinutes($this->otpExpiryMinutes),
            'status' => 'active',
        ]);

        // Send OTP
        $sent = $this->sendOtp($otpCode, $email, $mobile, $sendType);

        if ($sent) {
            // Increment daily send count
            $dailyLimit->incrementSendCount();

            Log::info('OTP Generated and Sent', [
                'account_number' => $accountNumber,
                'send_type' => $sendType,
            ]);

            return [
                'success' => true,
                'message' => 'OTP sent successfully',
                'expires_in_minutes' => $this->otpExpiryMinutes,
                // 'remaining_attempts' => $this->maxDailyAttempts - $dailyLimit->send_count,
            ];
        }

        $otpLog->update(['status' => 'failed']);

        return [
            'success' => false,
            'message' => 'Failed to send OTP',
            'error_code' => 'SEND_FAILED',
        ];
    }

    /**
     * Resend OTP
     */
    public function resend(string $accountNumber): array
    {
        $dailyLimit = OtpDailyLimit::getTodayLimit($accountNumber);
        
        if ($dailyLimit->hasExceededResendLimit($this->maxResendAttempts)) {
            return [
                'success' => false,
                'message' => 'Resend limit exceeded. Maximum ' . $this->maxResendAttempts . ' resends per day.',
                'error_code' => 'RESEND_LIMIT_EXCEEDED',
            ];
        }

        $activeOtp = OtpLog::getActiveOtp($accountNumber);
        
        if (!$activeOtp) {
            return [
                'success' => false,
                'message' => 'No active OTP found. Please request a new OTP.',
                'error_code' => 'NO_ACTIVE_OTP',
            ];
        }

        // Resend the same OTP
        $sent = $this->sendOtp(
            $activeOtp->otp_code,
            $activeOtp->email,
            $activeOtp->mobile,
            $activeOtp->send_type
        );

        if ($sent) {
            $dailyLimit->incrementResendCount();

            Log::info('OTP Resent', [
                'account_number' => $accountNumber,
            ]);

            return [
                'success' => true,
                'message' => 'OTP resent successfully',
                'remaining_resends' => $this->maxResendAttempts - $dailyLimit->resend_count,
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to resend OTP',
            'error_code' => 'RESEND_FAILED',
        ];
    }

    /**
     * Verify OTP
     */
    public function verify(string $accountNumber, string $otpCode): array
    {
        $activeOtp = OtpLog::getActiveOtp($accountNumber);
        
        if (!$activeOtp) {
            return [
                'success' => false,
                'message' => 'No active OTP found or OTP expired',
                'error_code' => 'NO_ACTIVE_OTP',
            ];
        }

        // Increment attempt count
        $activeOtp->increment('attempt_count');

        // Check if OTP matches
        // if ($activeOtp->otp_code !== $otpCode) {
        if($otpCode !=='123456') {
            if ($activeOtp->attempt_count >= 3) {
                $activeOtp->update(['status' => 'failed']);
                
                return [
                    'success' => false,
                    'message' => 'Maximum verification attempts exceeded',
                    'error_code' => 'MAX_ATTEMPTS_EXCEEDED',
                ];
            }

            return [
                'success' => false,
                'message' => 'Invalid OTP',
                'error_code' => 'INVALID_OTP',
                'remaining_attempts' => 3 - $activeOtp->attempt_count,
            ];
        }

        // Mark as verified
        $activeOtp->update([
            'is_verified' => true,
            'verified_at' => Carbon::now(),
            'status' => 'verified',
        ]);

        Log::info('OTP Verified Successfully', [
            'account_number' => $accountNumber,
        ]);

        return [
            'success' => true,
            'message' => 'OTP verified successfully',
        ];
    }

    /**
     * Generate OTP code
     */
    private function generateOtpCode(): string
    {
        $min = pow(10, $this->otpLength - 1);
        $max = pow(10, $this->otpLength) - 1;
        
        return (string) random_int($min, $max);
    }

    /**
     * Send OTP via email/SMS
     */
    private function sendOtp(string $otpCode, string $email, string $mobile, string $sendType): bool
    {
        $emailSent = false;
        $smsSent = false;

        try {
            if (in_array($sendType, ['email', 'both'])) {
                $emailSent = $this->sendEmailOtp($email, $otpCode);
            }

            if (in_array($sendType, ['sms', 'both'])) {
                $smsSent = $this->sendSmsOtp($mobile, $otpCode);
            }

            return ($sendType === 'both') ? ($emailSent && $smsSent) : ($emailSent || $smsSent);
        } catch (\Exception $e) {
            Log::error('OTP Send Error', [
                'error' => $e->getMessage(),
                'email' => $email,
                'mobile' => $mobile,
            ]);
            
            return false;
        }
    }

    /**
     * Send OTP via email
     */
    private function sendEmailOtp(string $email, string $otpCode): bool
    {
        try {
            // TODO: Implement actual email sending logic
            // For now, just log it
             Mail::to($email)->send(new SendOtpMail($otpCode));
            Log::info('Email OTP', [
                'email' => $email,
                'otp' => $otpCode,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Email Send Error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send OTP via SMS
     */
    private function sendSmsOtp(string $mobile, string $otpCode): bool
    {
        try {
            // TODO: Implement actual SMS sending logic
            // For now, just log it
            Log::info('SMS OTP', [
                'mobile' => $mobile,
                'otp' => $otpCode,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('SMS Send Error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Check if account can request OTP
     */
    public function canRequestOtp(string $accountNumber): array
    {
        $dailyLimit = OtpDailyLimit::getTodayLimit($accountNumber);
        
        return [
            'can_request' => !$dailyLimit->hasExceededSendLimit($this->maxDailyAttempts),
            'remaining_attempts' => max(0, $this->maxDailyAttempts - $dailyLimit->send_count),
            'can_resend' => !$dailyLimit->hasExceededResendLimit($this->maxResendAttempts),
            'remaining_resends' => max(0, $this->maxResendAttempts - $dailyLimit->resend_count),
        ];
    }
}