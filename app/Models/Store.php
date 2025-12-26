<?php

namespace App\Models;

use App\Enums\StoreStatus;
use App\Enums\StoreType;
use App\Services\StoreStatusService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Store extends Model
{
    /** @use HasFactory<\Database\Factories\StoreFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'bio',
        'image',
        'type',
        'status',
        'low_stock_threshold',
        'business_hours',
        'phone',
        'email',
        'address',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'return_policy',
        'shipping_policy',
        'privacy_policy',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Store $store): void {
            if (empty($store->slug) && ! empty($store->name)) {
                $store->slug = static::generate_unique_slug($store->name);
            }
        });

        static::created(function (Store $store): void {
            // Auto-initialize delivery prices for all cities when a non-digital store is created
            if ($store->type !== StoreType::Digital) {
                $store->initializeDeliveryPrices();
            }
        });

        static::updating(function (Store $store): void {
            if ($store->isDirty('name')) {
                $store->slug = static::generate_unique_slug($store->name, $store->id);
            }

            // When store status changes to inactive, deactivate all products
            if ($store->isDirty('status') && $store->status === StoreStatus::Inactive) {
                $original_status = $store->getOriginal('status');
                // Only deactivate products if the store was previously active (not already inactive)
                if ($original_status !== StoreStatus::Inactive->value) {
                    $store_status_service = app(StoreStatusService::class);
                    $store_status_service->deactivateStoreProducts($store);
                }
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
            'type' => StoreType::class,
            'status' => StoreStatus::class,
        ];
    }

    /**
     * Get the users that manage this store.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Get the products for this store.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the orders for this store.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the cities this store delivers to.
     */
    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class, 'city_store_delivery')
            ->withPivot('price')
            ->withTimestamps();
    }

    /**
     * Get the discount plans for this store.
     */
    public function discount_plans(): HasMany
    {
        return $this->hasMany(DiscountPlan::class);
    }

    /**
     * Get products with low stock for this store.
     */
    public function getLowStockProducts()
    {
        $threshold = $this->low_stock_threshold ?? 10;

        return $this->products()
            ->where('stock', '<=', $threshold)
            ->get();
    }

    /**
     * Helper: check if store is digital.
     */
    public function isDigital(): bool
    {
        return $this->type === StoreType::Digital;
    }

    /**
     * Helper: check if store is physical.
     */
    public function isPhysical(): bool
    {
        return $this->type === StoreType::Physical;
    }

    /**
     * Helper: check if store is active.
     */
    public function isActive(): bool
    {
        return $this->status === StoreStatus::Active;
    }

    /**
     * Initialize delivery prices for all cities.
     */
    public function initializeDeliveryPrices(): void
    {
        $all_city_ids = City::query()->pluck('id')->toArray();

        if (empty($all_city_ids)) {
            return;
        }

        // Check which cities already have delivery prices for this store
        $existing_city_ids = CityStoreDelivery::query()
            ->where('store_id', $this->id)
            ->pluck('city_id')
            ->toArray();

        // Get cities that need to be added
        $cities_to_add = array_diff($all_city_ids, $existing_city_ids);

        if (empty($cities_to_add)) {
            return;
        }

        // Insert delivery prices for all cities (default price: 0)
        DB::transaction(function () use ($cities_to_add) {
            $records = [];
            $now = now();

            foreach ($cities_to_add as $city_id) {
                $records[] = [
                    'store_id' => $this->id,
                    'city_id' => $city_id,
                    'price' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            CityStoreDelivery::query()->insert($records);
        });
    }
}
