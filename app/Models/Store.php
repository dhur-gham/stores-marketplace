<?php

namespace App\Models;

use App\Enums\StoreType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
