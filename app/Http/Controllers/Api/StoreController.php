<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\StoreService;
use Illuminate\Http\JsonResponse;

class StoreController extends BaseController
{
    public function __construct(public StoreService $store_service) {}

    public function index(): JsonResponse
    {
        $stores = $this->store_service->get_all_stores();

        return $this->success_response($stores, 'Stores retrieved successfully');
    }
}

