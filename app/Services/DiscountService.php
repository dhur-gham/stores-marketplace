<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DiscountPlanStatus;
use App\Enums\DiscountType;
use App\Helpers\TimezoneHelper;
use App\Models\DiscountPlan;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiscountService
{
    /**
     * Calculate discounted price for a product based on discount plan.
     *
     * @param  Product  $product  The product
     * @param  DiscountPlan  $plan  The discount plan
     * @return int The discounted price in IQD
     */
    public function calculateDiscountedPrice(Product $product, DiscountPlan $plan): int
    {
        if ($plan->discount_type === DiscountType::Percentage) {
            $discount_amount = ($product->price * $plan->discount_value) / 100;

            return (int) max(0, $product->price - $discount_amount);
        } else {
            // Fixed discount
            return (int) max(0, $product->price - $plan->discount_value);
        }
    }

    /**
     * Activate a discount plan and update all associated products.
     */
    public function activatePlan(DiscountPlan $plan): void
    {
        DB::transaction(function () use ($plan) {
            $plan->status = DiscountPlanStatus::Active;
            $plan->save();

            // Get all products in this plan
            $products = $plan->products;

            foreach ($products as $product) {
                $discounted_price = $this->calculateDiscountedPrice($product, $plan);
                $product->plan_id = $plan->id;
                $product->discounted_price = $discounted_price;
                $product->save();
            }

            Log::info("Discount plan {$plan->id} activated, updated {$products->count()} products");
        });
    }

    /**
     * Expire a discount plan and reset all associated products.
     */
    public function expirePlan(DiscountPlan $plan): void
    {
        DB::transaction(function () use ($plan) {
            $plan->status = DiscountPlanStatus::Expired;
            $plan->save();

            // Get all products with this plan
            $products = Product::where('plan_id', $plan->id)->get();

            foreach ($products as $product) {
                $product->plan_id = null;
                $product->discounted_price = null;
                $product->save();
            }

            Log::info("Discount plan {$plan->id} expired, reset {$products->count()} products");
        });
    }

    /**
     * Process all discount plans - activate scheduled plans and expire active plans.
     * This is called by the scheduled command.
     */
    public function processPlans(): void
    {
        $now = now();

        // Activate scheduled plans that should start
        $plans_to_activate = DiscountPlan::where('status', DiscountPlanStatus::Scheduled)
            ->where('start_date', '<=', $now)
            ->get();

        foreach ($plans_to_activate as $plan) {
            try {
                $this->activatePlan($plan);
            } catch (\Exception $e) {
                Log::error("Failed to activate discount plan {$plan->id}: {$e->getMessage()}");
            }
        }

        // Expire active plans that should end
        $plans_to_expire = DiscountPlan::where('status', DiscountPlanStatus::Active)
            ->where('end_date', '<', $now)
            ->get();

        foreach ($plans_to_expire as $plan) {
            try {
                $this->expirePlan($plan);
            } catch (\Exception $e) {
                Log::error("Failed to expire discount plan {$plan->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Update products when a discount plan is modified.
     * Recalculates discounts for all products in the plan if the plan is active.
     */
    public function updatePlanProducts(DiscountPlan $plan): void
    {
        DB::transaction(function () use ($plan) {
            // Only update products if the plan is active
            if ($plan->status !== DiscountPlanStatus::Active) {
                return;
            }

            // Get all products in this plan
            $products = $plan->products;

            foreach ($products as $product) {
                $discounted_price = $this->calculateDiscountedPrice($product, $plan);
                $product->plan_id = $plan->id;
                $product->discounted_price = $discounted_price;
                $product->save();
            }

            Log::info("Discount plan {$plan->id} updated, recalculated discounts for {$products->count()} products");
        });
    }

    /**
     * Apply discount to products when they are added to an active plan.
     */
    public function applyDiscountToProducts(DiscountPlan $plan, array $product_ids): void
    {
        DB::transaction(function () use ($plan, $product_ids) {
            // Only apply discount if the plan is active
            if ($plan->status !== DiscountPlanStatus::Active) {
                return;
            }

            $products = Product::whereIn('id', $product_ids)->get();

            foreach ($products as $product) {
                $discounted_price = $this->calculateDiscountedPrice($product, $plan);
                $product->plan_id = $plan->id;
                $product->discounted_price = $discounted_price;
                $product->save();
            }

            Log::info("Applied discount from plan {$plan->id} to {$products->count()} products");
        });
    }

    /**
     * Convert Baghdad datetime to UTC for storage.
     */
    public function convertBaghdadToUtc(string $datetime): \Carbon\Carbon
    {
        return TimezoneHelper::baghdadToUtc($datetime);
    }

    /**
     * Convert UTC datetime to Baghdad for display.
     */
    public function convertUtcToBaghdad(\Carbon\Carbon $datetime): \Carbon\Carbon
    {
        return TimezoneHelper::utcToBaghdad($datetime);
    }
}
