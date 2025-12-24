<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Wishlist\AddToWishlistRequest;
use App\Services\WishlistService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @Group Wishlist API
 */
#[Group('Wishlist', weight: 4)]
class WishlistController extends BaseController
{
    public function __construct(public WishlistService $wishlist_service) {}

    /**
     * Get the authenticated customer's wishlist.
     *
     * Returns all wishlist items with product details.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         items: array<int, array{
     *             id: int,
     *             product_id: int,
     *             product: array{
     *                 id: int,
     *                 name: string,
     *                 slug: string,
     *                 image: string|null,
     *                 price: int,
     *                 stock: int,
     *                 status: string,
     *                 store: array{
     *                     id: int,
     *                     name: string,
     *                     slug: string,
     *                     type: string
     *                 }
     *             }
     *         }>,
     *         count: int
     *     }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();
        $items = $this->wishlist_service->get_wishlist($customer);
        $count = $this->wishlist_service->get_wishlist_count($customer);

        return $this->success_response([
            'items' => $items,
            'count' => $count,
        ], 'Wishlist retrieved successfully');
    }

    /**
     * Add a product to the wishlist.
     *
     * Adds a product to the customer's wishlist. If the product already exists, returns the existing item.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         items: array<int, array<string, mixed>>,
     *         count: int
     *     }
     * }
     */
    public function store(AddToWishlistRequest $request): JsonResponse
    {
        try {
            $customer = $request->user();
            $this->wishlist_service->add_to_wishlist($customer, $request->product_id);

            $items = $this->wishlist_service->get_wishlist($customer);
            $count = $this->wishlist_service->get_wishlist_count($customer);

            return $this->success_response([
                'items' => $items,
                'count' => $count,
            ], 'Product added to wishlist successfully');
        } catch (\InvalidArgumentException $e) {
            return $this->error_response($e->getMessage(), 422);
        }
    }

    /**
     * Remove an item from the wishlist.
     *
     * Removes a specific item from the customer's wishlist.
     *
     * @param  int  $wishlist_item  The wishlist item ID.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         items: array<int, array<string, mixed>>,
     *         count: int
     *     }
     * }
     */
    public function destroy(Request $request, int $wishlist_item): JsonResponse
    {
        try {
            $customer = $request->user();
            $this->wishlist_service->remove_from_wishlist($customer, $wishlist_item);

            $items = $this->wishlist_service->get_wishlist($customer);
            $count = $this->wishlist_service->get_wishlist_count($customer);

            return $this->success_response([
                'items' => $items,
                'count' => $count,
            ], 'Item removed from wishlist successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error_response('Wishlist item not found', 404);
        }
    }

    /**
     * Check if a product is in the wishlist.
     *
     * @param  int  $product_id  The product ID.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         in_wishlist: bool,
     *         wishlist_item_id: int|null
     *     }
     * }
     */
    public function check(Request $request, int $product_id): JsonResponse
    {
        $customer = $request->user();
        $in_wishlist = $this->wishlist_service->is_in_wishlist($customer, $product_id);

        $wishlist_item_id = null;
        if ($in_wishlist) {
            $wishlist_item = \App\Models\WishlistItem::query()
                ->where('customer_id', $customer->id)
                ->where('product_id', $product_id)
                ->first();
            $wishlist_item_id = $wishlist_item?->id;
        }

        return $this->success_response([
            'in_wishlist' => $in_wishlist,
            'wishlist_item_id' => $wishlist_item_id,
        ], 'Wishlist status retrieved successfully');
    }

    /**
     * Get or generate a share link for the wishlist.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         share_token: string,
     *         share_url: string,
     *         custom_message: string|null,
     *         is_active: bool,
     *         views_count: int
     *     }
     * }
     */
    public function share(Request $request): JsonResponse
    {
        $customer = $request->user();
        $custom_message = $request->input('custom_message');

        $wishlist_share = $this->wishlist_service->generate_share_link($customer, $custom_message);

        $share_url = url("/wishlist/share/{$wishlist_share->share_token}");

        return $this->success_response([
            'share_token' => $wishlist_share->share_token,
            'share_url' => $share_url,
            'custom_message' => $wishlist_share->custom_message,
            'is_active' => $wishlist_share->is_active,
            'views_count' => $wishlist_share->views_count,
        ], 'Share link retrieved successfully');
    }

    /**
     * View a shared wishlist (public endpoint).
     *
     * @param  string  $token  The share token.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         share: array{
     *             id: int,
     *             share_token: string,
     *             custom_message: string|null,
     *             views_count: int
     *         },
     *         customer: array{
     *             id: int,
     *             name: string
     *         },
     *         wishlist_items: array<int, array<string, mixed>>
     *     }
     * }
     */
    public function shared(string $token): JsonResponse
    {
        $shared_wishlist = $this->wishlist_service->get_shared_wishlist($token);

        if (! $shared_wishlist) {
            return $this->error_response('Shared wishlist not found or inactive', 404);
        }

        // Increment view count
        $this->wishlist_service->increment_share_views($token);

        return $this->success_response($shared_wishlist, 'Shared wishlist retrieved successfully');
    }

    /**
     * Update the custom message for the share link.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         share_token: string,
     *         share_url: string,
     *         custom_message: string,
     *         is_active: bool,
     *         views_count: int
     *     }
     * }
     */
    public function updateShareMessage(Request $request): JsonResponse
    {
        $request->validate([
            'custom_message' => 'required|string|max:500',
        ]);

        $customer = $request->user();
        $wishlist_share = $this->wishlist_service->update_share_message($customer, $request->custom_message);

        $share_url = url("/wishlist/share/{$wishlist_share->share_token}");

        return $this->success_response([
            'share_token' => $wishlist_share->share_token,
            'share_url' => $share_url,
            'custom_message' => $wishlist_share->custom_message,
            'is_active' => $wishlist_share->is_active,
            'views_count' => $wishlist_share->views_count,
        ], 'Share message updated successfully');
    }

    /**
     * Toggle the active status of the share link.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         share_token: string,
     *         share_url: string,
     *         custom_message: string|null,
     *         is_active: bool,
     *         views_count: int
     *     }
     * }
     */
    public function toggleShare(Request $request): JsonResponse
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        try {
            $customer = $request->user();
            $wishlist_share = $this->wishlist_service->toggle_share_active($customer, $request->is_active);

            $share_url = url("/wishlist/share/{$wishlist_share->share_token}");

            return $this->success_response([
                'share_token' => $wishlist_share->share_token,
                'share_url' => $share_url,
                'custom_message' => $wishlist_share->custom_message,
                'is_active' => $wishlist_share->is_active,
                'views_count' => $wishlist_share->views_count,
            ], 'Share status updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error_response('Share link not found. Generate one first.', 404);
        }
    }
}
