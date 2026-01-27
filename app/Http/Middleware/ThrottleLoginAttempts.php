<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLoginAttempts
{
    /**
     * The rate limiter instance.
     *
     * @var \Illuminate\Cache\RateLimiter
     */
    protected $limiter;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Cache\RateLimiter  $limiter
     * @return void
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $rateLimitConfig = \App\Services\SystemSettingService::getRateLimitingConfig();
        $maxAttempts = $rateLimitConfig['login']['max_attempts'] ?? config('rate-limiting.login.max_attempts', 5);
        $decayMinutes = $rateLimitConfig['login']['decay_minutes'] ?? config('rate-limiting.login.decay_minutes', 15);

        // Create a unique key based on phone number (user-specific throttling)
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = $this->limiter->availableIn($key);
            $minutes = ceil($retryAfter / 60);

            Log::warning('Login rate limit exceeded', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'phone' => $request->input('phone'),
                'retry_after' => $retryAfter,
            ]);

            return back()->withErrors([
                'phone' => "Too many login attempts. Please try again in {$minutes} minute(s).",
            ])->withInput();
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Add rate limit headers
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $maxAttempts - $this->limiter->attempts($key)),
        ]);

        return $response;
    }

    /**
     * Resolve request signature based on phone number (user-specific) instead of IP.
     * This ensures that only the specific user making too many attempts is locked out,
     * not all users from the same IP address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature(Request $request): string
    {
        // Use phone number for user-specific throttling
        // If phone is not provided, fall back to IP (for edge cases)
        $phone = $request->input('phone');
        
        if ($phone) {
            // Normalize phone number to ensure consistent key
            $normalizedPhone = function_exists('normalize_phone_number') 
                ? normalize_phone_number($phone) 
                : preg_replace('/[^0-9+]/', '', $phone);
            
            return 'login|phone|' . $normalizedPhone;
        }
        
        // Fallback to IP if phone is not available (shouldn't happen for login, but safety first)
        return 'login|ip|' . $request->ip();
    }
}

