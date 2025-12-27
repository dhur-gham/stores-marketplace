<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'city_id',
        'notes',
        'telegram_chat_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Customer belongs to a city.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the orders for this customer.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the cart items for this customer.
     */
    public function cart_items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the wishlist items for this customer.
     */
    public function wishlist_items(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    /**
     * Get the saved addresses for this customer.
     */
    public function saved_addresses(): HasMany
    {
        return $this->hasMany(SavedAddress::class);
    }

    /**
     * Get the wishlist share for this customer.
     */
    public function wishlist_share(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WishlistShare::class);
    }

    /**
     * Check if customer has activated Telegram notifications.
     */
    public function hasTelegramActivated(): bool
    {
        return ! is_null($this->telegram_chat_id);
    }

    /**
     * Get the Telegram deep link for activation.
     */
    public function getTelegramDeepLink(): string
    {
        $bot_username = config('services.telegram.bot_username', 'jzubot');
        $customer_id = $this->id;

        return "https://t.me/{$bot_username}?start=cust-{$customer_id}";
    }
}
