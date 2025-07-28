<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Models\Project;
use App\Models\LeaveRequest;
use App\Models\Transaksi;
use App\Observers\ProjectObserver;
use App\Observers\TransaksiObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrasi observer yang sudah ada
        Project::observe(ProjectObserver::class);
        Transaksi::observe(TransaksiObserver::class);
        if ($this->app->isLocal()) {
            Model::unguard();
        }

        // Memaksa penggunaan URL HTTPS di lingkungan produksi.
        // Ini penting jika aplikasi Anda berada di belakang reverse proxy atau load balancer,
        // untuk memastikan asset dan link yang dihasilkan oleh Laravel menggunakan skema yang benar.
        // Kode ini diambil dari file asli Anda dan merupakan praktik yang sudah benar.
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' || $this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Register event listeners untuk Leave Request
     */
}