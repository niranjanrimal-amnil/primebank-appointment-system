<?php

namespace AppointmentSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OtpDailyLimit extends Model
{
    protected $fillable = [
        'account_number',
        'date',
        'send_count',
        'resend_count',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Get or create today's limit
    public static function getTodayLimit(string $accountNumber): self
    {
        return self::firstOrCreate(
            [
                'account_number' => $accountNumber,
                'date' => Carbon::today(),
            ],
            [
                'send_count' => 0,
                'resend_count' => 0,
            ]
        );
    }

    // Check if limit exceeded
    public function hasExceededSendLimit(int $maxLimit = 5): bool
    {
        return $this->send_count >= $maxLimit;
    }

    public function hasExceededResendLimit(int $maxLimit = 5): bool
    {
        return $this->resend_count >= $maxLimit;
    }

    // Increment counters
    public function incrementSendCount(): void
    {
        $this->increment('send_count');
    }

    public function incrementResendCount(): void
    {
        $this->increment('resend_count');
    }
}