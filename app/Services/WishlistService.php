<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\WishlistItem;
use App\Models\WishlistShare;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class WishlistService
{
    /**
     * Get all wishlist items for a customer with product details.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get_wishlist(Customer $customer): array
    {
        $wishlist_items = $customer->wishlist_items()->with('product.store')->get();

        return $wishlist_items->map(fn (WishlistItem $item) => [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'product' => [
                'id' => $item->product->id,
                'name' => $item->product->name,
                'slug' => $item->product->slug,
                'image' => $item->product->image ? asset('storage/'.$item->product->image) : null,
                'price' => $item->product->price,
                'stock' => $item->product->stock,
                'status' => $item->product->status->value,
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
     * Add a product to the customer's wishlist.
     *
     * @throws ModelNotFoundException
     */
    public function add_to_wishlist(Customer $customer, int $product_id): WishlistItem
    {
        $product = Product::query()->findOrFail($product_id);

        // Check if product already exists in wishlist
        $wishlist_item = WishlistItem::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $product_id)
            ->first();

        if ($wishlist_item) {
            return $wishlist_item;
        }

        // Create new wishlist item
        $wishlist_item = WishlistItem::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $product_id,
        ]);

        return $wishlist_item;
    }

    /**
     * Remove an item from the wishlist.
     *
     * @throws ModelNotFoundException
     */
    public function remove_from_wishlist(Customer $customer, int $wishlist_item_id): bool
    {
        $wishlist_item = WishlistItem::query()
            ->where('id', $wishlist_item_id)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        return $wishlist_item->delete();
    }

    /**
     * Check if a product is in the customer's wishlist.
     */
    public function is_in_wishlist(Customer $customer, int $product_id): bool
    {
        return WishlistItem::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $product_id)
            ->exists();
    }

    /**
     * Get the total count of items in the wishlist.
     */
    public function get_wishlist_count(Customer $customer): int
    {
        return WishlistItem::query()
            ->where('customer_id', $customer->id)
            ->count();
    }

    /**
     * Generate or update a share link for the customer's wishlist.
     */
    public function generate_share_link(Customer $customer, ?string $custom_message = null): WishlistShare
    {
        $wishlist_share = $customer->wishlist_share;

        if (! $wishlist_share) {
            $wishlist_share = WishlistShare::query()->create([
                'customer_id' => $customer->id,
                'share_token' => WishlistShare::generate_token(),
                'custom_message' => $custom_message,
                'is_active' => true,
            ]);
        } else {
            if ($custom_message !== null) {
                $wishlist_share->custom_message = $custom_message;
            }
            $wishlist_share->save();
        }

        return $wishlist_share;
    }

    /**
     * Get a shared wishlist by share token (public access).
     *
     * @return array<string, mixed>|null
     */
    public function get_shared_wishlist(string $share_token): ?array
    {
        $wishlist_share = WishlistShare::query()
            ->where('share_token', $share_token)
            ->where('is_active', true)
            ->with('customer')
            ->first();

        if (! $wishlist_share) {
            return null;
        }

        $customer = $wishlist_share->customer;
        $wishlist_items = $this->get_wishlist($customer);

        return [
            'share' => [
                'id' => $wishlist_share->id,
                'share_token' => $wishlist_share->share_token,
                'custom_message' => $wishlist_share->custom_message,
                'views_count' => $wishlist_share->views_count,
            ],
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
            ],
            'wishlist_items' => $wishlist_items,
        ];
    }

    /**
     * Increment the view count for a shared wishlist.
     */
    public function increment_share_views(string $share_token): void
    {
        $wishlist_share = WishlistShare::query()
            ->where('share_token', $share_token)
            ->where('is_active', true)
            ->first();

        if ($wishlist_share) {
            $wishlist_share->increment_views();
        }
    }

    /**
     * Get the customer's share info.
     */
    public function get_share_info(Customer $customer): ?WishlistShare
    {
        return $customer->wishlist_share;
    }

    /**
     * Update the custom message for a share link.
     */
    public function update_share_message(Customer $customer, string $custom_message): WishlistShare
    {
        $wishlist_share = $customer->wishlist_share;

        if (! $wishlist_share) {
            return $this->generate_share_link($customer, $custom_message);
        }

        $wishlist_share->custom_message = $custom_message;
        $wishlist_share->save();

        return $wishlist_share;
    }

    /**
     * Toggle the active status of a share link.
     */
    public function toggle_share_active(Customer $customer, bool $is_active): WishlistShare
    {
        $wishlist_share = $customer->wishlist_share;

        if (! $wishlist_share) {
            // If no share exists and we're trying to activate, create one
            if ($is_active) {
                return $this->generate_share_link($customer);
            }

            throw new ModelNotFoundException('Wishlist share not found');
        }

        $wishlist_share->is_active = $is_active;
        $wishlist_share->save();

        return $wishlist_share;
    }
}
