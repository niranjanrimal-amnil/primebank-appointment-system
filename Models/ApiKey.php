<?php

namespace AppointmentSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ApiKey extends Model
{
    protected $fillable = [
        'purpose_id',
        'purpose_name',
        'api_key',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Encrypt API key when setting
    // public function setApiKeyAttribute($value)
    // {
    //     $this->attributes['api_key'] = Crypt::encryptString($value);
    // }

    // // Decrypt API key when getting
    // public function getApiKeyAttribute($value)
    // {
    //     return Crypt::decryptString($value);
    // }

    // Get active API key by purpose ID
    public static function getByPurposeId(string $purposeId): ?string
    {
        $apiKey = self::where('purpose_id', $purposeId)
            ->where('is_active', true)
            ->first();

        return $apiKey?->api_key;
    }
}