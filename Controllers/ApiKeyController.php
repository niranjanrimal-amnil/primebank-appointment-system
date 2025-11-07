<?php

namespace AppointmentSystem\Controllers;

use Illuminate\Routing\Controller;
use AppointmentSystem\Services\ApiKeyService;
use AppointmentSystem\Requests\StoreApiKeyRequest;
use AppointmentSystem\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;

class ApiKeyController extends Controller
{
    private ApiKeyService $apiKeyService;

    public function __construct(ApiKeyService $apiKeyService)
    {
        $this->apiKeyService = $apiKeyService;
    }

    /**
     * Store API key
     */
    public function store(StoreApiKeyRequest $request): JsonResponse
    {
        $result = $this->apiKeyService->storeApiKey(
            $request->purpose_id,
            $request->purpose_name,
            $request->api_key
        );

        if ($result['success']) {
            return ResponseHelper::success($result['data'], $result['message'], 201);
        }

        return ResponseHelper::error($result['message'], null, 400, $result['error_code'] ?? null);
    }

    /**
     * Get all API keys
     */
    public function index(): JsonResponse
    {
        $result = $this->apiKeyService->getAllApiKeys();

        if ($result['success']) {
            return ResponseHelper::success($result['data'], 'API keys retrieved successfully');
        }

        return ResponseHelper::error($result['message'], null, 400, $result['error_code'] ?? null);
    }

    /**
     * Toggle API key status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $result = $this->apiKeyService->toggleStatus($id);

        if ($result['success']) {
            return ResponseHelper::success($result['data'], $result['message']);
        }

        return ResponseHelper::error($result['message'], null, 400, $result['error_code'] ?? null);
    }

    /**
     * Delete API key
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->apiKeyService->deleteApiKey($id);

        if ($result['success']) {
            return ResponseHelper::success(null, $result['message']);
        }

        return ResponseHelper::error($result['message'], null, 400, $result['error_code'] ?? null);
    }
}