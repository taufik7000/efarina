<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip redirect untuk admin panel, API, dan asset files
        if ($this->shouldSkipRedirect($request)) {
            return $next($request);
        }

        $requestPath = $request->getPathInfo();
        $cleanPath = ltrim($requestPath, '/');
        
        // Cache redirect untuk performance (cache selama 1 jam)
        $cacheKey = 'redirect:' . md5($cleanPath);
        
        $redirect = Cache::remember($cacheKey, 3600, function () use ($cleanPath) {
            return Redirect::active()
                ->where('old_url', $cleanPath)
                ->first();
        });

        if ($redirect) {
            // Update hit count secara async
            dispatch(function () use ($redirect) {
                $redirect->incrementHitCount();
            })->afterResponse();

            $newUrl = $redirect->new_url;
            
            // Pastikan URL absolut jika internal
            if (!str_starts_with($newUrl, 'http')) {
                $newUrl = url($newUrl);
            }

            return redirect($newUrl, $redirect->status_code);
        }

        return $next($request);
    }

    private function shouldSkipRedirect(Request $request): bool
    {
        $path = $request->getPathInfo();
        
        // Daftar path yang tidak perlu di-redirect
        $skipPaths = [
            '/admin',           // Filament admin
            '/api',             // API routes
            '/storage',         // Storage files
            '/livewire',        // Livewire assets
            '/_debugbar',       // Debug bar
            '/telescope',       // Laravel Telescope
            '/horizon',         // Laravel Horizon
            '/hrd',             // Panel HRD
            '/team',            // Panel Team
            '/bisnis',          // Panel Bisnis
            '/redaksi',         // Panel Redaksi
            '/direktur',        // Panel Direktur
            '/keuangan',        // Panel Keuangan
        ];

        foreach ($skipPaths as $skipPath) {
            if (str_starts_with($path, $skipPath)) {
                return true;
            }
        }

        // Skip untuk file dengan ekstensi tertentu
        $skipExtensions = ['.js', '.css', '.jpg', '.jpeg', '.png', '.gif', '.svg', '.ico', '.pdf', '.zip', '.woff', '.woff2', '.ttf'];
        foreach ($skipExtensions as $extension) {
            if (str_ends_with($path, $extension)) {
                return true;
            }
        }

        // Skip untuk AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return true;
        }

        return false;
    }
}