<?php

namespace AppointmentSystem\Requests;

class ResendOtpRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'account_number' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'account_number.required' => 'Account number is required',
        ];
    }
}