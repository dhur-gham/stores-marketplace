<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends BaseController
{
    public function __construct(public StoreService $store_service) {}

    public function index(Request $request): JsonResponse
    {
        $per_page = (int) $request->query('per_page', 15);
        $result = $this->store_service->get_all_stores($per_page);

        return $this->paginated_response($result['paginator'], $result['data'], 'Stores retrieved successfully');
    }
}
