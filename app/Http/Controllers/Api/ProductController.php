<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Store;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends BaseController
{
    public function __construct(public ProductService $product_service) {}

    public function index(Store $store): JsonResponse
    {
        $products = $this->product_service->get_products_by_store($store);

        return $this->success_response($products, 'Products retrieved successfully');
    }
}

