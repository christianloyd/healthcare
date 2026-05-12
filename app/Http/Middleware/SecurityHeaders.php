<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security Headers Middleware
 *
 * Adds security headers to all HTTP responses to protect against common web vulnerabilities.
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking attacks by preventing the page from being embedded in frames
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection in older browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Control how much referrer information is sent
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Restrict browser features and APIs
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Force HTTPS connections (only in production)
        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Content Security Policy - Prevent XSS and injection attacks
        // In development: Allow Vite dev server (localhost:5173)
        // In production: Strict local-only policy
        
        $isDevelopment = config('app.env') !== 'production';
        
        if ($isDevelopment) {
            // Development: Allow Vite dev server (on multiple ports if needed) and external fonts
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:5173 http://localhost:5174 ws://localhost:5173 ws://localhost:5174",
                "style-src 'self' 'unsafe-inline' http://localhost:5173 http://localhost:5174 https://fonts.googleapis.com",
                "font-src 'self' data: http://localhost:5173 http://localhost:5174 https://fonts.gstatic.com",
                "img-src 'self' data: https: http://localhost:5173 http://localhost:5174",
                "connect-src 'self' http://localhost:5173 http://localhost:5174 ws://localhost:5173 ws://localhost:5174",
                "frame-ancestors 'none'",
                "base-uri 'self'",
                "form-action 'self'",
            ]);
        } else {
            // Production: Strict local-only policy
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
                "style-src 'self' 'unsafe-inline'",
                "font-src 'self' data:",
                "img-src 'self' data: https:",
                "connect-src 'self'",
                "frame-ancestors 'none'",
                "base-uri 'self'",
                "form-action 'self'",
            ]);
        }
        
        $response->headers->set('Content-Security-Policy', $csp);

        // Remove server identification headers to prevent information disclosure
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
