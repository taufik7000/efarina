<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check() || !$request->user()->hasRole($roles)) {
            // Arahkan ke halaman panel yang sesuai atau tolak akses
            // Misalnya, arahkan kembali ke halaman login
            return redirect('/login');

            // Atau jika Anda ingin menampilkan halaman "tidak diizinkan"
            // abort(403, 'UNAUTHORIZED');
        }

        return $next($request);
    }
}