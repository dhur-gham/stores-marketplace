<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Determine if the user can access the Filament panel.
     */
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return true;
    }

    /**
     * Get the stores this user manages.
     */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class)->withTimestamps();
    }

    /**
     * Get the products created by this user.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the discount plans created by this user.
     */
    public function discount_plans(): HasMany
    {
        return $this->hasMany(DiscountPlan::class, 'created_by_user_id');
    }

    /**
     * Check if user has activated Telegram notifications.
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
        $user_id = $this->id;

        return "https://t.me/{$bot_username}?start=user-{$user_id}";
    }
}
