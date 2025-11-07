<?php

return [
    // External API base URL
    'external_api_base_url' => env('EXTERNAL_API_BASE_URL', 'https://api.external-service.com'),
    
    // OTP Settings
    'otp' => [
        'max_attempts_per_day' => 5,
        'max_resend_attempts' => 5,
        'expiry_minutes' => 10,
        'length' => 6,
    ],
    
    // Appointment Settings
    'appointment' => [
        'default_timezone' => 'Asia/Kathmandu',
        'slot_duration_minutes' => 30,
    ],
    
    // Cache settings
    'cache' => [
        'ttl' => 3600, // 1 hour
        'prefix' => 'appointment_system_',
    ],
];