<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'store_id',
        'city_id',
        'address',
        'total',
        'delivery_price',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'delivery_price' => 'integer',
            'status' => OrderStatus::class,
        ];
    }

    /**
     * Order belongs to a customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Order belongs to a store.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Order belongs to a city.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the order items for this order.
     */
    public function order_items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the status history for this order.
     */
    public function status_history(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    /**
     * Record a status change in history.
     */
    public function recordStatusChange(OrderStatus $status, ?User $user = null): void
    {
        $this->status_history()->create([
            'status' => $status,
            'changed_by_user_id' => $user?->id,
        ]);
    }
}
