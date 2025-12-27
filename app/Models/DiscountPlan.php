<?php

namespace App\Models;

use App\Enums\DiscountPlanStatus;
use App\Enums\DiscountType;
use App\Services\DiscountService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DiscountPlan extends Model
{
    /** @use HasFactory<\Database\Factories\DiscountPlanFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'store_id',
        'name',
        'discount_type',
        'discount_value',
        'start_date',
        'end_date',
        'status',
        'created_by_user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_type' => DiscountType::class,
            'status' => DiscountPlanStatus::class,
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    /**
     * Discount plan belongs to a store.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Discount plan belongs to a user (creator).
     */
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Discount plan has many products (many-to-many).
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'discount_plan_products', 'plan_id', 'product_id')
            ->withTimestamps();
    }

    /**
     * Check if the plan is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === DiscountPlanStatus::Active
            && $this->start_date <= now()
            && $this->end_date >= now();
    }

    /**
     * Check if the plan is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->status === DiscountPlanStatus::Scheduled;
    }

    /**
     * Check if the plan is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === DiscountPlanStatus::Expired || $this->end_date < now();
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Remove discounts from products when a plan is deleted
        static::deleting(function (DiscountPlan $plan) {
            $discount_service = app(DiscountService::class);
            $discount_service->removePlanDiscounts($plan);
        });
    }
}
