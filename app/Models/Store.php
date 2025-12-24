<?php

namespace App\Models;

use App\Enums\StoreType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

        static::updating(function (Store $store): void {
            if ($store->isDirty('name')) {
                $store->slug = static::generate_unique_slug($store->name, $store->id);
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
}
