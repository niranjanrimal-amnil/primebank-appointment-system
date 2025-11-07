<?php

namespace AppointmentSystem\Requests;

class StoreApiKeyRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'purpose_id' => 'required|uuid',
            'purpose_name' => 'required|string|max:255',
            'api_key' => 'required|string|min:10',
        ];
    }

    public function messages(): array
    {
        return [
            'purpose_id.required' => 'Purpose ID is required',
            'purpose_id.uuid' => 'Invalid purpose ID format',
            'purpose_name.required' => 'Purpose name is required',
            'api_key.required' => 'API key is required',
            'api_key.min' => 'API key must be at least 10 characters',
        ];
    }
}