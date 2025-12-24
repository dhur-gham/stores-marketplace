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
     * @param  string  $identifier  The store ID or slug.
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
    public function index(Request $request, string $identifier): JsonResponse
    {
        $store = is_numeric($identifier)
            ? Store::query()->find($identifier)
            : Store::query()->where('slug', $identifier)->first();

        if (! $store) {
            return $this->error_response('Store not found', 404);
        }

        $per_page = (int) $request->query('per_page', 15);
        $customer = $request->user();
        $result = $this->product_service->get_products_by_store($store, $per_page, $customer);

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
     *         stock: int,
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
    public function latest(Request $request): JsonResponse
    {
        $customer = $request->user();
        $products = $this->product_service->get_latest_products(5, $customer);

        return $this->success_response($products, 'Latest products retrieved successfully');
    }

    /**
     * List all products from all stores with filtering and sorting.
     *
     * Returns a paginated list of products from all stores with filtering and sorting options.
     * Products include details like name, price, stock, status, type, and store information.
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
     *         price: int,
     *         stock: int,
     *         store: array{
     *             id: int,
     *             name: string,
     *             slug: string,
     *             image: string|null
     *         }
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
    #[QueryParameter('page', description: 'Page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'Search products by name (case-insensitive, partial match).', type: 'string', required: false, example: 'tech')]
    #[QueryParameter('store_id', description: 'Filter by store ID.', type: 'int', required: false, example: 1)]
    #[QueryParameter('type', description: 'Filter by type: digital or physical.', type: 'string', required: false, example: 'digital')]
    #[QueryParameter('price_min', description: 'Minimum price filter.', type: 'int', required: false, example: 100)]
    #[QueryParameter('price_max', description: 'Maximum price filter.', type: 'int', required: false, example: 1000)]
    #[QueryParameter('sort_by', description: 'Sort by: name, price, created_at, or store_name.', type: 'string', required: false, example: 'price')]
    #[QueryParameter('sort_order', description: 'Sort order: asc or desc.', type: 'string', required: false, example: 'desc')]
    public function all(Request $request): JsonResponse
    {
        $per_page = (int) $request->query('per_page', 15);
        $search = $request->query('search');
        $store_id = $request->query('store_id') ? (int) $request->query('store_id') : null;
        $type = $request->query('type');
        $price_min = $request->query('price_min') ? (int) $request->query('price_min') : null;
        $price_max = $request->query('price_max') ? (int) $request->query('price_max') : null;
        $sort_by = $request->query('sort_by');
        $sort_order = $request->query('sort_order', 'desc');

        $customer = $request->user();
        $result = $this->product_service->get_all_products(
            $per_page,
            $search,
            $store_id,
            $type,
            $price_min,
            $price_max,
            $sort_by,
            $sort_order,
            $customer
        );

        return $this->paginated_response($result['paginator'], $result['data'], 'Products retrieved successfully');
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
    public function show(Request $request, string $identifier): JsonResponse
    {
        $customer = $request->user();
        $product = $this->product_service->get_product_by_id_or_slug($identifier, $customer);

        if (! $product) {
            return $this->error_response('Product not found', 404);
        }

        return $this->success_response($product, 'Product retrieved successfully');
    }
}
