<?php

namespace AppointmentSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OtpLog extends Model


{
    protected $table = 'otp_logs';
    protected $fillable = [
        'account_number',
        'otp_code',
        'send_type',
        'email',
        'mobile',
        'is_verified',
        'verified_at',
        'expires_at',
        'attempt_count',
        'status',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Check if OTP is expired
    public function isExpired(): bool
    {
        return Carbon::now()->greaterThan($this->expires_at);
    }

    // Check if OTP is still valid
    public function isValid(): bool
    {
        return $this->status === 'active' 
            && !$this->is_verified 
            && !$this->isExpired();
    }

    // Get active OTP for account
    public static function getActiveOtp(string $accountNumber): ?self
    {
        return self::where('account_number', $accountNumber)
            ->where('status', 'active')
            ->where('is_verified', false)
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();
    }
}