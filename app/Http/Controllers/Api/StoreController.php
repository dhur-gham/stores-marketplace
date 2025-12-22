<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

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
    public function index(Request $request): JsonResponse
    {
        $per_page = (int) $request->query('per_page', 15);
        $result = $this->store_service->get_all_stores($per_page);

        return $this->paginated_response($result['paginator'], $result['data'], 'Stores retrieved successfully');
    }
}
