<?php

namespace App\Http\Middleware;

use App\Models\ApiRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class ApiMetricsMiddleware
{
    /**
     * Sensitive headers that should not be logged.
     *
     * @var array<string>
     */
    protected array $sensitive_headers = [
        'authorization',
        'cookie',
        'x-csrf-token',
        'x-xsrf-token',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $started_at = Carbon::now();

        $response = $next($request);

        $ended_at = Carbon::now();

        $this->logRequest($request, $response, $started_at, $ended_at);

        return $response;
    }

    /**
     * Log the API request metrics.
     */
    protected function logRequest(Request $request, Response $response, Carbon $started_at, Carbon $ended_at): void
    {
        try {
            $duration_ms = (int) round($started_at->diffInMicroseconds($ended_at) / 1000);

            // Get user_id and validate it exists in database
            $user_id = null;
            $user = $request->user();
            if ($user && $user->id) {
                // Verify user exists in database to avoid foreign key constraint violations
                if (\App\Models\User::query()->where('id', $user->id)->exists()) {
                    $user_id = $user->id;
                }
            }

            ApiRequest::create([
                'method' => $request->method(),
                'path' => $request->path(),
                'full_url' => $request->fullUrl(),
                'status_code' => $response->getStatusCode(),
                'request_started_at' => $started_at,
                'request_ended_at' => $ended_at,
                'duration_ms' => $duration_ms,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => $user_id,
                'request_size' => strlen($request->getContent()),
                'response_size' => strlen($response->getContent()),
                'request_headers' => $this->filterHeaders($request->headers->all()),
                'exception' => $this->getException($response),
            ]);
        } catch (\Throwable $e) {
            // Silently fail - we don't want metrics to break the API
            report($e);
        }
    }

    /**
     * Filter out sensitive headers and normalize values to strings.
     *
     * @param  array<string, array<string>>  $headers
     * @return array<string, string>
     */
    protected function filterHeaders(array $headers): array
    {
        return collect($headers)
            ->filter(fn (array $value, string $key) => ! in_array(strtolower($key), $this->sensitive_headers))
            ->map(fn (array $values): string => implode(', ', $values))
            ->toArray();
    }

    /**
     * Get exception message if response indicates an error.
     */
    protected function getException(Response $response): ?string
    {
        if ($response->getStatusCode() >= 400) {
            $content = $response->getContent();
            $decoded = json_decode($content, true);

            if (isset($decoded['message'])) {
                return $decoded['message'];
            }

            if (isset($decoded['error'])) {
                return $decoded['error'];
            }

            return substr($content, 0, 500);
        }

        return null;
    }
}
