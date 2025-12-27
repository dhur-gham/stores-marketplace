<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\SavedAddress;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @Group Saved Addresses API
 */
#[Group('Saved Addresses', weight: 4)]
class SavedAddressController extends BaseController
{
    /**
     * Get all saved addresses for the authenticated customer.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array<int, array{
     *         id: int,
     *         label: string,
     *         city_id: int,
     *         city: array{id: int, name: string}|null,
     *         address: string,
     *         is_default: bool,
     *         created_at: string
     *     }>
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();

        $addresses = $customer->saved_addresses()
            ->with('city')
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($address) {
                return [
                    'id' => $address->id,
                    'label' => $address->label,
                    'city_id' => $address->city_id,
                    'city' => $address->city ? [
                        'id' => $address->city->id,
                        'name' => $address->city->name,
                    ] : null,
                    'address' => $address->address,
                    'is_default' => $address->is_default,
                    'created_at' => $address->created_at->toISOString(),
                ];
            });

        return $this->success_response($addresses->toArray(), 'Saved addresses retrieved successfully');
    }

    /**
     * Create a new saved address.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         id: int,
     *         label: string,
     *         city_id: int,
     *         city: array{id: int, name: string}|null,
     *         address: string,
     *         is_default: bool
     *     }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'address' => ['required', 'string', 'min:5'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $customer = $request->user();

        DB::beginTransaction();

        try {
            // If this is set as default, unset other defaults
            if ($validated['is_default'] ?? false) {
                $customer->saved_addresses()->update(['is_default' => false]);
            }

            $address = SavedAddress::query()->create([
                'customer_id' => $customer->id,
                'label' => $validated['label'],
                'city_id' => $validated['city_id'],
                'address' => $validated['address'],
                'is_default' => $validated['is_default'] ?? false,
            ]);

            $address->load('city');

            DB::commit();

            return $this->success_response([
                'id' => $address->id,
                'label' => $address->label,
                'city_id' => $address->city_id,
                'city' => $address->city ? [
                    'id' => $address->city->id,
                    'name' => $address->city->name,
                ] : null,
                'address' => $address->address,
                'is_default' => $address->is_default,
            ], 'Address saved successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error_response('Failed to save address: '.$e->getMessage(), 500);
        }
    }

    /**
     * Update a saved address.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         id: int,
     *         label: string,
     *         city_id: int,
     *         city: array{id: int, name: string}|null,
     *         address: string,
     *         is_default: bool
     *     }
     * }
     */
    public function update(Request $request, SavedAddress $saved_address): JsonResponse
    {
        // Ensure customer owns this address
        if ($saved_address->customer_id !== $request->user()->id) {
            return $this->error_response('Unauthorized', 403);
        }

        $validated = $request->validate([
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'city_id' => ['sometimes', 'required', 'integer', 'exists:cities,id'],
            'address' => ['sometimes', 'required', 'string', 'min:5'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();

        try {
            // If this is set as default, unset other defaults
            if (isset($validated['is_default']) && $validated['is_default']) {
                $request->user()->saved_addresses()
                    ->where('id', '!=', $saved_address->id)
                    ->update(['is_default' => false]);
            }

            $saved_address->update($validated);
            $saved_address->load('city');

            DB::commit();

            return $this->success_response([
                'id' => $saved_address->id,
                'label' => $saved_address->label,
                'city_id' => $saved_address->city_id,
                'city' => $saved_address->city ? [
                    'id' => $saved_address->city->id,
                    'name' => $saved_address->city->name,
                ] : null,
                'address' => $saved_address->address,
                'is_default' => $saved_address->is_default,
            ], 'Address updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error_response('Failed to update address: '.$e->getMessage(), 500);
        }
    }

    /**
     * Delete a saved address.
     *
     * @response array{
     *     status: bool,
     *     message: string
     * }
     */
    public function destroy(Request $request, SavedAddress $saved_address): JsonResponse
    {
        // Ensure customer owns this address
        if ($saved_address->customer_id !== $request->user()->id) {
            return $this->error_response('Unauthorized', 403);
        }

        $saved_address->delete();

        return $this->success_response(null, 'Address deleted successfully');
    }
}
