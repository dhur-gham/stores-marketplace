<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'store_id',
        'user_id',
        'name',
        'slug',
        'image',
        'description',
        'sku',
        'status',
        'type',
        'price',
        'stock',
        'plan_id',
        'discounted_price',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Product $product): void {
            // Auto-set user_id to authenticated user if not set
            if (empty($product->user_id) && auth()->check()) {
                $product->user_id = auth()->id();
            }

            // Auto-generate slug from name if not set
            if (empty($product->slug) && ! empty($product->name)) {
                $product->slug = static::generate_unique_slug($product->name);
            }
        });

        static::updating(function (Product $product): void {
            // Auto-regenerate slug when name changes
            if ($product->isDirty('name')) {
                $product->slug = static::generate_unique_slug($product->name, $product->id);
            }
        });
    }

    /**
     * Generate a unique slug from the name.
     */
    protected static function generate_unique_slug(string $name, ?int $exclude_id = null): string
    {
        $slug = Str::slug($name);
        $original_slug = $slug;
        $counter = 1;

        while (static::slug_exists($slug, $exclude_id)) {
            $slug = $original_slug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if a slug already exists.
     */
    protected static function slug_exists(string $slug, ?int $exclude_id = null): bool
    {
        $query = static::query()->where('slug', $slug);

        if ($exclude_id !== null) {
            $query->where('id', '!=', $exclude_id);
        }

        return $query->exists();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'type' => ProductType::class,
            'price' => 'integer',
            'stock' => 'integer',
            'discounted_price' => 'integer',
        ];
    }

    /**
     * Product belongs to a store.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Product belongs to a user (owner).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cart items for this product.
     */
    public function cart_items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the order items for this product.
     */
    public function order_items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the wishlist items for this product.
     */
    public function wishlist_items(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    /**
     * Get the product images for this product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Product belongs to a discount plan.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(DiscountPlan::class, 'plan_id');
    }

    /**
     * Product belongs to many discount plans (many-to-many).
     */
    public function discount_plans(): BelongsToMany
    {
        return $this->belongsToMany(DiscountPlan::class, 'discount_plan_products', 'product_id', 'plan_id')
            ->withTimestamps();
    }

    /**
     * Alias for discount_plans() to support camelCase access.
     */
    public function discountPlans(): BelongsToMany
    {
        return $this->discount_plans();
    }

    /**
     * Get the final price (discounted_price if available, otherwise regular price).
     */
    public function getFinalPrice(): int
    {
        return $this->discounted_price ?? $this->price;
    }

    /**
     * Check if product is on discount.
     */
    public function isOnDiscount(): bool
    {
        return ! is_null($this->discounted_price) && $this->discounted_price < $this->price;
    }

    /**
     * Check if product is low stock for a given store.
     */
    public function isLowStock(?Store $store = null): bool
    {
        if (! $store) {
            $store = $this->store;
        }

        $threshold = $store->low_stock_threshold ?? 10;

        return $this->stock <= $threshold;
    }

    /**
     * Scope to exclude products that are already in a specific discount plan.
     *
     * @param  Builder  $query
     * @param  int  $plan_id
     * @return Builder
     */
    public function scopeWhereNotInPlan(Builder $query, int $plan_id): Builder
    {
        return $query->whereDoesntHave('discount_plans', function ($q) use ($plan_id) {
            $q->where('discount_plans.id', $plan_id);
        });
    }
}
