<?php

namespace AppointmentSystem\Requests;

class VerifyOtpRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'account_number' => 'required|string',
            'otp_code' => 'required|string|digits:6',
        ];
    }

    public function messages(): array
    {
        return [
            'account_number.required' => 'Account number is required',
            'otp_code.required' => 'OTP code is required',
            'otp_code.digits' => 'OTP code must be 6 digits',
        ];
    }
}