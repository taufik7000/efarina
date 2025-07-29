<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('🚀 MIDDLEWARE EXECUTING', [
            'url' => $request->fullUrl(),
            'path' => $request->getPathInfo(),
        ]);

        // Skip jika sudah ada route yang match
        if ($this->hasMatchingRoute($request)) {
            Log::info('⏭️ SKIPPING - Route already exists', [
                'path' => $request->getPathInfo()
            ]);
            return $next($request);
        }

        // Skip untuk admin panel dll
        if ($this->shouldSkipRedirect($request)) {
            Log::info('⏭️ SKIPPING - Admin/API path', [
                'path' => $request->getPathInfo()
            ]);
            return $next($request);
        }

        $requestPath = $request->getPathInfo();
        $cleanPath = ltrim($requestPath, '/');
        
        Log::info('🔍 SEARCHING DATABASE', [
            'clean_path' => $cleanPath,
        ]);

        $redirect = Redirect::where('is_active', true)
            ->where('old_url', $cleanPath)
            ->first();

        if ($redirect) {
            Log::info('🎯 EXECUTING REDIRECT', [
                'from' => $cleanPath,
                'to' => $redirect->new_url,
                'status_code' => $redirect->status_code,
            ]);

            $redirect->increment('hit_count');
            $redirect->update(['last_accessed_at' => now()]);

            return redirect($redirect->new_url, $redirect->status_code);
        }

        Log::info('❌ NO REDIRECT FOUND', [
            'searched_for' => $cleanPath,
        ]);

        return $next($request);
    }

    private function hasMatchingRoute(Request $request): bool
    {
        try {
            // Check if there's already a defined route for this path
            $routes = Route::getRoutes();
            $method = $request->method();
            $path = $request->getPathInfo();
            
            foreach ($routes as $route) {
                if (in_array($method, $route->methods()) && $route->matches($request)) {
                    // Skip jika route adalah fallback atau catch-all
                    $uri = $route->uri();
                    if (str_contains($uri, '{any}') || 
                        str_contains($uri, 'fallback') ||
                        $route->isFallback) {
                        return false; // Tetap proses redirect
                    }
                    return true; // Ada route spesifik
                }
            }
        } catch (\Exception $e) {
            Log::warning('Error checking route match', ['error' => $e->getMessage()]);
        }
        
        return false;
    }

    private function shouldSkipRedirect(Request $request): bool
    {
        $path = $request->getPathInfo();
        
        $skipPaths = [
            '/admin', '/api', '/storage', '/livewire',
            '/hrd', '/team', '/bisnis', '/redaksi', '/direktur',
            '/_debugbar', '/telescope', '/horizon'
        ];
        
        foreach ($skipPaths as $skipPath) {
            if (str_starts_with($path, $skipPath)) {
                return true;
            }
        }

        // Skip file extensions
        if (str_contains($path, '.')) {
            $extension = '.' . pathinfo($path, PATHINFO_EXTENSION);
            $skipExtensions = ['.js', '.css', '.jpg', '.png', '.gif', '.svg', '.ico', '.pdf', '.zip'];
            if (in_array($extension, $skipExtensions)) {
                return true;
            }
        }

        // Skip AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return true;
        }

        return false;
    }
}