<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartService
{
    /**
     * Get all cart items for a customer with product details.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get_cart(Customer $customer): array
    {
        $cart_items = $customer->cart_items()->with('product.store')->get();

        return $cart_items->map(fn (CartItem $item) => [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'price' => $item->price,
            'subtotal' => $item->quantity * $item->price,
            'product' => [
                'id' => $item->product->id,
                'name' => $item->product->name,
                'slug' => $item->product->slug,
                'image' => $item->product->image ? asset('storage/'.$item->product->image) : null,
                'price' => $item->product->price,
                'stock' => $item->product->stock,
                'store' => [
                    'id' => $item->product->store->id,
                    'name' => $item->product->store->name,
                    'slug' => $item->product->store->slug,
                    'type' => $item->product->store->type->value,
                ],
            ],
        ])->toArray();
    }

    /**
     * Add or update a product in the customer's cart.
     *
     * @throws ModelNotFoundException
     */
    public function add_to_cart(Customer $customer, int $product_id, int $quantity = 1): CartItem
    {
        $product = Product::query()->findOrFail($product_id);

        // Validate product is active
        if ($product->status !== ProductStatus::Active) {
            throw new \InvalidArgumentException('Product is not available');
        }

        // Check stock availability
        if ($product->stock < $quantity) {
            throw new \InvalidArgumentException('Insufficient stock available');
        }

        // Check if product already exists in cart
        $cart_item = CartItem::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $product_id)
            ->first();

        if ($cart_item) {
            // Update quantity, but check total doesn't exceed stock
            $new_quantity = $cart_item->quantity + $quantity;
            if ($product->stock < $new_quantity) {
                throw new \InvalidArgumentException('Insufficient stock available');
            }
            $cart_item->quantity = $new_quantity;
            $cart_item->save();
        } else {
            // Create new cart item with price snapshot
            $cart_item = CartItem::query()->create([
                'customer_id' => $customer->id,
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $product->price,
            ]);
        }

        return $cart_item;
    }

    /**
     * Update cart item quantity.
     *
     * @throws ModelNotFoundException
     */
    public function update_cart_item(Customer $customer, int $cart_item_id, int $quantity): CartItem
    {
        $cart_item = CartItem::query()
            ->where('id', $cart_item_id)
            ->where('customer_id', $customer->id)
            ->with('product')
            ->firstOrFail();

        // Validate quantity
        if ($quantity < 1) {
            throw new \InvalidArgumentException('Quantity must be at least 1');
        }

        // Check stock availability
        if ($cart_item->product->stock < $quantity) {
            throw new \InvalidArgumentException('Insufficient stock available');
        }

        $cart_item->quantity = $quantity;
        $cart_item->save();

        return $cart_item;
    }

    /**
     * Remove an item from the cart.
     *
     * @throws ModelNotFoundException
     */
    public function remove_from_cart(Customer $customer, int $cart_item_id): bool
    {
        $cart_item = CartItem::query()
            ->where('id', $cart_item_id)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        return $cart_item->delete();
    }

    /**
     * Clear all items from the cart.
     */
    public function clear_cart(Customer $customer): bool
    {
        return CartItem::query()
            ->where('customer_id', $customer->id)
            ->delete() > 0;
    }

    /**
     * Get the total price of all items in the cart.
     */
    public function get_cart_total(Customer $customer): int
    {
        return CartItem::query()
            ->where('customer_id', $customer->id)
            ->get()
            ->sum(fn (CartItem $item) => $item->quantity * $item->price);
    }

    /**
     * Get the total count of items in the cart.
     */
    public function get_cart_count(Customer $customer): int
    {
        return (int) CartItem::query()
            ->where('customer_id', $customer->id)
            ->sum('quantity');
    }
}
