<?php

namespace AppointmentSystem\Requests;

class GetSlotsRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'date' => 'required|date|after_or_equal:today',
            'location_id' => 'nullable|uuid',
            'purpose_id' => 'required|uuid',
            'timezone' => 'sometimes|string',
            'assigned_staff_ids' => 'sometimes|array',
            'assigned_staff_ids.*' => 'uuid',
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Date is required',
            'date.date' => 'Please provide a valid date',
            'date.after_or_equal' => 'Date must be today or in the future',
            'location_id.uuid' => 'Invalid location ID format',
            'purpose_id.required' => 'Purpose is required',
            'purpose_id.uuid' => 'Invalid purpose ID format',
            'assigned_staff_ids.array' => 'Assigned staff IDs must be an array',
            'assigned_staff_ids.*.uuid' => 'Invalid staff ID format',
        ];
    }
}