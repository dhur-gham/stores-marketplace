<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\City;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

/**
 * @Group City API
 */
#[Group('Cities', weight: 4)]
class CityController extends BaseController
{
    /**
     * Get all cities.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array<int, array{id: int, name: string}>
     * }
     */
    public function index(): JsonResponse
    {
        $cities = City::query()->orderBy('name')->get();

        $data = $cities->map(fn (City $city) => [
            'id' => $city->id,
            'name' => $city->name,
        ])->toArray();

        return $this->success_response($data, 'Cities retrieved successfully');
    }
}
