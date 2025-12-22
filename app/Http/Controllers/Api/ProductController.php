<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Store;
use App\Services\ProductService;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @Group Products API
 */
#[Group('Products', weight: 1)]
class ProductController extends BaseController
{
    public function __construct(public ProductService $product_service) {}

    /**
     * List products for a store.
     *
     * Returns a paginated list of products belonging to a specific store.
     * Products include details like name, price, stock, status, and type.
     *
     * @param  Store  $store  The store to retrieve products from.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array<int, array{
     *         id: int,
     *         name: string,
     *         slug: string,
     *         image: string|null,
     *         description: string|null,
     *         sku: string|null,
     *         status: string,
     *         type: string,
     *         price: string,
     *         stock: int
     *     }>,
     *     meta: array{
     *         current_page: int,
     *         last_page: int,
     *         per_page: int,
     *         total: int
     *     }
     * }
     *
     * @unauthenticated
     */
    #[QueryParameter('per_page', description: 'Number of products per page.', type: 'int', default: 15, example: 10)]
    public function index(Request $request, Store $store): JsonResponse
    {
        $per_page = (int) $request->query('per_page', 15);
        $result = $this->product_service->get_products_by_store($store, $per_page);

        return $this->paginated_response($result['paginator'], $result['data'], 'Products retrieved successfully');
    }
}
