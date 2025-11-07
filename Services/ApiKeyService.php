<?php

namespace AppointmentSystem\Services;

use AppointmentSystem\Models\ApiKey;
use Illuminate\Support\Facades\Log;

class ApiKeyService
{
    /**
     * Store API key for a purpose
     */
    public function storeApiKey(string $purposeId, string $purposeName, string $apiKey): array
    {
        try {
            // Check if API key already exists for this purpose
            $existing = ApiKey::where('purpose_id', $purposeId)->first();

            if ($existing) {
                // Update existing
                $existing->update([
                    'purpose_name' => $purposeName,
                    'api_key' => $apiKey,
                    'is_active' => true,
                ]);

                Log::info('API Key Updated', [
                    'purpose_id' => $purposeId,
                    'purpose_name' => $purposeName,
                ]);

                return [
                    'success' => true,
                    'message' => 'API key updated successfully',
                    'data' => [
                        'id' => $existing->id,
                        'purpose_id' => $existing->purpose_id,
                        'purpose_name' => $existing->purpose_name,
                    ],
                ];
            }

            // Create new
            $newApiKey = ApiKey::create([
                'purpose_id' => $purposeId,
                'purpose_name' => $purposeName,
                'api_key' => $apiKey,
                'is_active' => true,
            ]);

            Log::info('API Key Created', [
                'purpose_id' => $purposeId,
                'purpose_name' => $purposeName,
            ]);

            return [
                'success' => true,
                'message' => 'API key stored successfully',
                'data' => [
                    'id' => $newApiKey->id,
                    'purpose_id' => $newApiKey->purpose_id,
                    'purpose_name' => $newApiKey->purpose_name,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Store API Key Error', [
                'purpose_id' => $purposeId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to store API key',
                'error_code' => 'STORE_FAILED',
            ];
        }
    }

    /**
     * Get all API keys (without exposing actual keys)
     */
    public function getAllApiKeys(): array
    {
        try {
            $apiKeys = ApiKey::orderBy('created_at', 'desc')->get();

            return [
                'success' => true,
                'data' => $apiKeys->map(function ($key) {
                    return [
                        'id' => $key->id,
                        'purpose_id' => $key->purpose_id,
                        'purpose_name' => $key->purpose_name,
                        'is_active' => $key->is_active,
                        'created_at' => $key->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
            ];
        } catch (\Exception $e) {
            Log::error('Get All API Keys Error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to fetch API keys',
                'error_code' => 'FETCH_FAILED',
            ];
        }
    }

    /**
     * Get API key by purpose ID (for internal use only)
     */
    public function getApiKeyByPurpose(string $purposeId): ?string
    {
        return ApiKey::getByPurposeId($purposeId);
    }

    /**
     * Toggle API key status
     */
    public function toggleStatus(int $id): array
    {
        try {
            $apiKey = ApiKey::find($id);

            if (!$apiKey) {
                return [
                    'success' => false,
                    'message' => 'API key not found',
                    'error_code' => 'NOT_FOUND',
                ];
            }

            $apiKey->update(['is_active' => !$apiKey->is_active]);

            Log::info('API Key Status Toggled', [
                'id' => $id,
                'is_active' => $apiKey->is_active,
            ]);

            return [
                'success' => true,
                'message' => 'API key status updated successfully',
                'data' => [
                    'id' => $apiKey->id,
                    'is_active' => $apiKey->is_active,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Toggle API Key Status Error', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update API key status',
                'error_code' => 'UPDATE_FAILED',
            ];
        }
    }

    /**
     * Delete API key
     */
    public function deleteApiKey(int $id): array
    {
        try {
            $apiKey = ApiKey::find($id);

            if (!$apiKey) {
                return [
                    'success' => false,
                    'message' => 'API key not found',
                    'error_code' => 'NOT_FOUND',
                ];
            }

            $apiKey->delete();

            Log::info('API Key Deleted', [
                'id' => $id,
            ]);

            return [
                'success' => true,
                'message' => 'API key deleted successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Delete API Key Error', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to delete API key',
                'error_code' => 'DELETE_FAILED',
            ];
        }
    }
}