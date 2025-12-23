<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Services\CartService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @Group Cart API
 */
#[Group('Cart', weight: 2)]
class CartController extends BaseController
{
    public function __construct(public CartService $cart_service) {}

    /**
     * Get the authenticated customer's cart.
     *
     * Returns all cart items with product details and cart totals.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         items: array<int, array{
     *             id: int,
     *             product_id: int,
     *             quantity: int,
     *             price: int,
     *             subtotal: int,
     *             product: array{
     *                 id: int,
     *                 name: string,
     *                 slug: string,
     *                 image: string|null,
     *                 price: int,
     *                 stock: int,
     *                 store: array{
     *                     id: int,
     *                     name: string,
     *                     slug: string
     *                 }
     *             }
     *         }>,
     *         total: int,
     *         count: int
     *     }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();
        $items = $this->cart_service->get_cart($customer);
        $total = $this->cart_service->get_cart_total($customer);
        $count = $this->cart_service->get_cart_count($customer);

        return $this->success_response([
            'items' => $items,
            'total' => $total,
            'count' => $count,
        ], 'Cart retrieved successfully');
    }

    /**
     * Add a product to the cart.
     *
     * Adds a product to the customer's cart. If the product already exists, the quantity is incremented.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         items: array<int, array<string, mixed>>,
     *         total: int,
     *         count: int
     *     }
     * }
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        try {
            $customer = $request->user();
            $this->cart_service->add_to_cart(
                $customer,
                $request->product_id,
                $request->quantity
            );

            $items = $this->cart_service->get_cart($customer);
            $total = $this->cart_service->get_cart_total($customer);
            $count = $this->cart_service->get_cart_count($customer);

            return $this->success_response([
                'items' => $items,
                'total' => $total,
                'count' => $count,
            ], 'Product added to cart successfully');
        } catch (\InvalidArgumentException $e) {
            return $this->error_response($e->getMessage(), 422);
        }
    }

    /**
     * Update a cart item's quantity.
     *
     * Updates the quantity of a specific cart item.
     *
     * @param  int  $cart_item  The cart item ID.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         items: array<int, array<string, mixed>>,
     *         total: int,
     *         count: int
     *     }
     * }
     */
    public function update(UpdateCartItemRequest $request, int $cart_item): JsonResponse
    {
        try {
            $customer = $request->user();
            $this->cart_service->update_cart_item(
                $customer,
                $cart_item,
                $request->quantity
            );

            $items = $this->cart_service->get_cart($customer);
            $total = $this->cart_service->get_cart_total($customer);
            $count = $this->cart_service->get_cart_count($customer);

            return $this->success_response([
                'items' => $items,
                'total' => $total,
                'count' => $count,
            ], 'Cart item updated successfully');
        } catch (\InvalidArgumentException $e) {
            return $this->error_response($e->getMessage(), 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error_response('Cart item not found', 404);
        }
    }

    /**
     * Remove an item from the cart.
     *
     * Removes a specific item from the customer's cart.
     *
     * @param  int  $cart_item  The cart item ID.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         items: array<int, array<string, mixed>>,
     *         total: int,
     *         count: int
     *     }
     * }
     */
    public function destroy(Request $request, int $cart_item): JsonResponse
    {
        try {
            $customer = $request->user();
            $this->cart_service->remove_from_cart($customer, $cart_item);

            $items = $this->cart_service->get_cart($customer);
            $total = $this->cart_service->get_cart_total($customer);
            $count = $this->cart_service->get_cart_count($customer);

            return $this->success_response([
                'items' => $items,
                'total' => $total,
                'count' => $count,
            ], 'Item removed from cart successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error_response('Cart item not found', 404);
        }
    }

    /**
     * Clear the entire cart.
     *
     * Removes all items from the customer's cart.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         items: array,
     *         total: int,
     *         count: int
     *     }
     * }
     */
    public function clear(Request $request): JsonResponse
    {
        $customer = $request->user();
        $this->cart_service->clear_cart($customer);

        return $this->success_response([
            'items' => [],
            'total' => 0,
            'count' => 0,
        ], 'Cart cleared successfully');
    }
}
