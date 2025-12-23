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

    /**
     * Get the latest products from all stores.
     *
     * Returns the first 5 latest active products from all stores with their store information.
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
     *         price: int,
     *         store: array{
     *             id: int,
     *             name: string,
     *             slug: string
     *         }
     *     }>
     * }
     *
     * @unauthenticated
     */
    public function latest(): JsonResponse
    {
        $products = $this->product_service->get_latest_products(5);

        return $this->success_response($products, 'Latest products retrieved successfully');
    }

    /**
     * Get a single product by ID or slug.
     *
     * Returns detailed information about a specific product including store information.
     *
     * @param  string  $identifier  The product ID or slug.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         id: int,
     *         name: string,
     *         slug: string,
     *         image: string|null,
     *         description: string|null,
     *         sku: string|null,
     *         status: string,
     *         type: string,
     *         price: int,
     *         stock: int,
     *         store: array{
     *             id: int,
     *             name: string,
     *             slug: string,
     *             image: string|null
     *         }
     *     }
     * }
     *
     * @unauthenticated
     */
    public function show(string $identifier): JsonResponse
    {
        $product = $this->product_service->get_product_by_id_or_slug($identifier);

        if (! $product) {
            return $this->error_response('Product not found', 404);
        }

        return $this->success_response($product, 'Product retrieved successfully');
    }
}
