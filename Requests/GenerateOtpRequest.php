<?php

namespace AppointmentSystem\Requests;

class GenerateOtpRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'account_number' => 'required|string|min:5|max:50',
            'email' => 'required|email|max:255',
            'mobile' => 'required|string|min:10|max:15',
            'send_type' => 'sometimes|in:email,sms,both',
        ];
    }

    public function messages(): array
    {
        return [
            'account_number.required' => 'Account number is required',
            'account_number.min' => 'Account number must be at least 5 characters',
            'email.required' => 'Email is required',
            'email.email' => 'Please provide a valid email address',
            'mobile.required' => 'Mobile number is required',
            'mobile.min' => 'Mobile number must be at least 10 digits',
            'send_type.in' => 'Send type must be email, sms, or both',
        ];
    }
}