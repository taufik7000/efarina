<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Menampilkan form login.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLoginForm()
    {
        // Jika pengguna sudah login, arahkan ke dashboard sesuai role
        if (Auth::check()) {
            return $this->redirectToDashboard(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Menangani percobaan login.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Redirect ke dashboard sesuai role
            return $this->redirectToDashboard($user);
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Menangani logout pengguna.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Redirect ke dashboard berdasarkan role pengguna (STRICT ACCESS).
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectToDashboard($user)
    {
        // STRICT: Setiap role hanya ke dashboard mereka sendiri
        if ($user->hasRole('direktur')) {
            return redirect()->to('/direktur');
        } 
        elseif ($user->hasRole('keuangan')) {
            return redirect()->to('/keuangan');
        } 
        elseif ($user->hasRole('hrd')) {
            return redirect()->to('/hrd');
        } 
        elseif ($user->hasRole('marketing')) {
            return redirect()->to('/marketing');
        } 
        elseif ($user->hasRole('redaksi')) {
            return redirect()->to('/redaksi');
        } 
        elseif ($user->hasRole('team')) {
            return redirect()->to('/team');
        }
        elseif ($user->hasRole(['admin', 'super-admin'])) {
            return redirect()->to('/admin');
        }

        // Jika tidak ada role yang cocok
        return redirect()->to('/login')->withErrors(['error' => 'Role tidak valid']);
    }

    /**
     * Alternative method jika menggunakan field 'role' biasa (bukan Spatie)
     * Uncomment jika TIDAK menggunakan package spatie/laravel-permission
     */
    /*
    private function redirectToDashboardWithField($user)
    {
        $dashboardUrl = match ($user->role) {
            'direktur' => '/direktur',
            'hrd' => '/hrd',
            'keuangan' => '/keuangan',
            'marketing' => '/marketing',
            'redaksi' => '/redaksi',
            default => '/admin' // fallback ke admin utama
        };

        return redirect()->to($dashboardUrl);
    }
    */
}