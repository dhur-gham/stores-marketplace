<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Store;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function __construct(public ProductService $product_service) {}

    public function index(Request $request, Store $store): JsonResponse
    {
        $per_page = (int) $request->query('per_page', 15);
        $result = $this->product_service->get_products_by_store($store, $per_page);

        return $this->paginated_response($result['paginator'], $result['data'], 'Products retrieved successfully');
    }
}
