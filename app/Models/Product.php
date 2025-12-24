<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
}
