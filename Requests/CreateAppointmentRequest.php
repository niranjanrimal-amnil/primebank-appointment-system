<?php

namespace AppointmentSystem\Requests;

class CreateAppointmentRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'account_number' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|min:10|max:15',
            'purpose_id' => 'required|uuid',
            'purpose_name' => 'sometimes|string',
            'location_id' => 'nullable|uuid',
            'location_name' => 'sometimes|string',
            'assigned_staff_id' => 'sometimes|uuid',
            'staff_name' => 'sometimes|string',
            'proposed_date_time' => 'required|date',
            'scheduled_date_time' => 'required|date|after_or_equal:today',
            'remarks' => 'sometimes|string|max:1000',
            'appointment_metadata' => 'sometimes|string',
            'customer_timezone' => 'sometimes|string',
            'agent_timezone' => 'sometimes|string',
        ];
    }

    public function messages(): array
    {
        return [
            'account_number.required' => 'Account number is required',
            'customer_name.required' => 'Customer name is required',
            'customer_email.required' => 'Email is required',
            'customer_email.email' => 'Please provide a valid email address',
            'customer_phone.required' => 'Phone number is required',
            'customer_phone.min' => 'Phone number must be at least 10 digits',
            'purpose_id.required' => 'Purpose is required',
            'purpose_id.uuid' => 'Invalid purpose ID format',
            'location_id.uuid' => 'Invalid location ID format',
            'assigned_staff_id.uuid' => 'Invalid staff ID format',
            'proposed_date_time.required' => 'Proposed date time is required',
            'scheduled_date_time.required' => 'Scheduled date time is required',
            'scheduled_date_time.after_or_equal' => 'Scheduled date must be today or in the future',
            'remarks.max' => 'Remarks cannot exceed 1000 characters',
        ];
    }
}