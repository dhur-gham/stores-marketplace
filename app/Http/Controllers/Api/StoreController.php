<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Store;
use App\Services\StoreService;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @Group Stores API
 */
#[Group('Stores', weight: 0)]
class StoreController extends BaseController
{
    public function __construct(public StoreService $store_service) {}

    /**
     * List all stores.
     *
     * Returns a paginated list of all available stores with their product counts.
     * Each store includes basic information like name, bio, image, and type.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array<int, array{
     *         id: int,
     *         name: string,
     *         slug: string,
     *         bio: string|null,
     *         image: string|null,
     *         type: string,
     *         products_count: int
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
    #[QueryParameter('per_page', description: 'Number of stores per page.', type: 'int', default: 15, example: 10)]
    #[QueryParameter('search', description: 'Search stores by name (case-insensitive, partial match).', type: 'string', required: false, example: 'tech')]
    public function index(Request $request): JsonResponse
    {
        $per_page = (int) $request->query('per_page', 15);
        $search = $request->query('search');
        $result = $this->store_service->get_all_stores($per_page, $search);

        return $this->paginated_response($result['paginator'], $result['data'], 'Stores retrieved successfully');
    }

    /**
     * Get a single store by ID or slug.
     *
     * Returns detailed information about a specific store including all its basic details.
     *
     * @param  string  $identifier  The store ID or slug.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         id: int,
     *         name: string,
     *         slug: string,
     *         bio: string|null,
     *         image: string|null,
     *         type: string,
     *         products_count: int
     *     }
     * }
     *
     * @unauthenticated
     */
    public function show(string $identifier): JsonResponse
    {
        $store_data = $this->store_service->get_store_by_id_or_slug($identifier);

        if (! $store_data) {
            return $this->error_response('Store not found', 404);
        }

        return $this->success_response($store_data, 'Store retrieved successfully');
    }

    /**
     * Get delivery prices for a store's cities.
     *
     * Returns delivery prices for all cities that the store delivers to.
     *
     * @param  int  $store  The store ID.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array<int, array{city_id: int, price: int}>
     * }
     *
     * @unauthenticated
     */
    public function deliveryPrices(int $store): JsonResponse
    {
        $store_model = Store::query()->findOrFail($store);
        
        $delivery_prices = $store_model->cities()
            ->get()
            ->map(fn ($city) => [
                'city_id' => $city->id,
                'price' => (int) $city->pivot->price,
            ])
            ->toArray();

        return $this->success_response($delivery_prices, 'Delivery prices retrieved successfully');
    }
}
