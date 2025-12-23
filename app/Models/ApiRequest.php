<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiRequest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'method',
        'path',
        'full_url',
        'status_code',
        'request_started_at',
        'request_ended_at',
        'duration_ms',
        'ip_address',
        'user_agent',
        'user_id',
        'request_size',
        'response_size',
        'request_headers',
        'exception',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'duration_ms' => 'integer',
            'request_size' => 'integer',
            'response_size' => 'integer',
            'request_headers' => 'array',
            'request_started_at' => 'datetime',
            'request_ended_at' => 'datetime',
        ];
    }

    /**
     * Get the user that made this request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if request was successful (2xx status).
     */
    public function isSuccessful(): bool
    {
        return $this->status_code >= 200 && $this->status_code < 300;
    }

    /**
     * Check if request was a client error (4xx status).
     */
    public function isClientError(): bool
    {
        return $this->status_code >= 400 && $this->status_code < 500;
    }

    /**
     * Check if request was a server error (5xx status).
     */
    public function isServerError(): bool
    {
        return $this->status_code >= 500;
    }

    /**
     * Check if request had an error (4xx or 5xx status).
     */
    public function isError(): bool
    {
        return $this->status_code >= 400;
    }
}
