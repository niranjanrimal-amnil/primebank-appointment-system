<?php

namespace AppointmentSystem\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'account_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'purpose_id',
        'purpose_name',
        'location_id',
        'location_name',
        'assigned_staff_id',
        'staff_name',
        'proposed_date_time',
        'scheduled_date_time',
        'remarks',
        'appointment_metadata',
        'customer_timezone',
        'agent_timezone',
        'appointment_taken_at',
        'appointment_confirmed_at',
        'status',
    ];

    protected $casts = [
        'proposed_date_time' => 'datetime',
        'scheduled_date_time' => 'datetime',
        'appointment_taken_at' => 'datetime',
        'appointment_confirmed_at' => 'datetime',
    ];

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeByAccountNumber($query, string $accountNumber)
    {
        return $query->where('account_number', $accountNumber);
    }
}