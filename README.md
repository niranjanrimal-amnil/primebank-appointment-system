# Appointment System Package

A Laravel package for managing appointments with external API integration, OTP verification, and comprehensive booking functionality.

## Features

- 🔐 OTP Generation & Verification (Email/SMS)
- 📅 Appointment Slot Management
- 🏢 Location & Purpose Dropdown
- 👥 User/Staff Management
- 🔑 Secure API Key Management
- 📊 Appointment History & Tracking
- ⏰ Daily Rate Limiting for OTP
- 🚀 Easy Integration with Laravel Projects

## Installation

### Step 1: Add to composer.json (Local Development)
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../core-php"
        }
    ],
    "require": {
        "your-company/appointment-system": "*"
    }
}
```

### Step 2: Install Package
```bash
composer require your-company/appointment-system
```

### Step 3: Publish Config
```bash
php artisan vendor:publish --tag=appointment-config
```

### Step 4: Run Migrations
```bash
php artisan migrate
```

### Step 5: Configure Environment

Add to your `.env`:
```env
EXTERNAL_API_BASE_URL=https://api.external-service.com
```

## Usage

### 1. Store API Keys (Admin)
```bash
POST /api/appointment-system/api-keys
```

**Request:**
```json
{
    "purpose_id": "uuid-here",
    "purpose_name": "Account Opening",
    "api_key": "your-secret-api-key"
}
```

### 2. Generate OTP
```bash
POST /api/appointment-system/otp/generate
```

**Request:**
```json
{
    "account_number": "1111111111",
    "email": "test@example.com",
    "mobile": "9876543210",
    "send_type": "both"
}
```

**Response:**
```json
{
    "success": true,
    "message": "OTP sent successfully",
    "data": {
        "expires_in_minutes": 10,
        "remaining_attempts": 4
    }
}
```

### 3. Verify OTP
```bash
POST /api/appointment-system/otp/verify
```

**Request:**
```json
{
    "account_number": "1111111111",
    "otp_code": "123456"
}
```

### 4. Get Purposes
```bash
GET /api/appointment-system/appointments/purposes/{purposeName}
```

**Response:**
```json
{
    "success": true,
    "message": "Purposes retrieved successfully",
    "data": [
        {
            "id": "uuid",
            "system_name": "AccountOpening",
            "display_name": "Account Opening",
            "is_active": true
        }
    ]
}
```

### 5. Get Locations
```bash
GET /api/appointment-system/appointments/locations/{purposeId}?target_date=2025-11-10
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "location_id": "uuid",
            "name": "Branch Name",
            "time_zone": "Asia/Kathmandu",
            "address": "Location Address",
            "is_default": true
        }
    ]
}
```

### 6. Get Available Slots
```bash
POST /api/appointment-system/appointments/slots
```

**Request:**
```json
{
    "date": "2025-11-10",
    "location_id": "uuid",
    "purpose_id": "uuid",
    "timezone": "Asia/Kathmandu",
    "assigned_staff_ids": ["uuid1", "uuid2"]
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "date": "2025-11-10",
        "available_staffs": ["uuid1"],
        "slots": [
            {
                "start_time": "09:00",
                "end_time": "09:30",
                "available_staffs": ["uuid1"],
                "is_valid": true,
                "invalid_reason": null
            }
        ]
    }
}
```

### 7. Create Appointment
```bash
POST /api/appointment-system/appointments/create
```

**Request:**
```json
{
    "account_number": "1111111111",
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "customer_phone": "9876543210",
    "purpose_id": "uuid",
    "purpose_name": "Account Opening",
    "location_id": "uuid",
    "customer_timezone":"Asia/Kathmandu",
    "location_name": "Main Branch",
    "assigned_staff_id": "uuid",
    "staff_name": "Staff Name",
    "proposed_date_time": "2025-11-10T09:00:00",
    "scheduled_date_time": "2025-11-10T09:00:00",
    "remarks": "First time appointment",
    "customer_timezone": "Asia/Kathmandu"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Appointment created successfully",
    "data": {
        "appointment_id": 1,
        "account_number": "1111111111",
        "scheduled_date_time": "2025-11-10 09:00:00",
        "location_name": "Main Branch",
        "purpose_name": "Account Opening",
        "status": "confirmed"
    }
}
```

### 8. Get Appointments by Account
```bash
GET /api/appointment-system/appointments/account/{accountNumber}?status=confirmed
```

### 9. Cancel Appointment
```bash
POST /api/appointment-system/appointments/{appointmentId}/cancel
```

**Request:**
```json
{
    "account_number": "1111111111"
}
```

## API Endpoints Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/otp/generate` | Generate and send OTP |
| POST | `/otp/resend` | Resend OTP |
| POST | `/otp/verify` | Verify OTP |
| GET | `/otp/status/{accountNumber}` | Check OTP status |
| GET | `/appointments/purposes` | Get all purposes |
| GET | `/appointments/locations/{purposeId}` | Get locations by purpose |
| GET | `/appointments/users/{purposeId}` | Get users by purpose |
| POST | `/appointments/slots` | Get available slots |
| POST | `/appointments/create` | Create appointment |
| GET | `/appointments/account/{accountNumber}` | Get appointments |
| POST | `/appointments/{id}/cancel` | Cancel appointment |
| GET | `/api-keys` | List API keys |
| POST | `/api-keys` | Store API key |
| PATCH | `/api-keys/{id}/toggle-status` | Toggle API key status |
| DELETE | `/api-keys/{id}` | Delete API key |

## Configuration

Edit `config/appointment.php`:
```php
return [
    'external_api_base_url' => env('EXTERNAL_API_BASE_URL'),
    
    'otp' => [
        'max_attempts_per_day' => 5,
        'max_resend_attempts' => 5,
        'expiry_minutes' => 10,
        'length' => 6,
    ],
    
    'appointment' => [
        'default_timezone' => 'Asia/Kathmandu',
        'slot_duration_minutes' => 30,
    ],
];
```

## OTP Limitations

- Maximum 5 OTP requests per account per day
- Maximum 5 resend attempts per day
- OTP expires in 10 minutes
- Maximum 3 verification attempts per OTP

## Security

- API keys are encrypted in database
- OTP codes are never exposed in logs
- Rate limiting on OTP generation
- Account-level restrictions

## Error Codes

| Code | Description |
|------|-------------|
| `DAILY_LIMIT_EXCEEDED` | Daily OTP limit reached |
| `RESEND_LIMIT_EXCEEDED` | Resend limit reached |
| `INVALID_OTP` | Wrong OTP code |
| `OTP_EXPIRED` | OTP has expired |
| `API_KEY_NOT_FOUND` | No API key for purpose |
| `VALIDATION_ERROR` | Validation failed |

## Support

For issues or questions, contact your development team.

## License

MIT License
```

---

## Step 10: Push to GitLab

Now let's create the Git commands to push this package:

**Create .gitignore**
```
/vendor/
composer.lock
.env
.DS_Store
.idea/
*.log