// vite.config.js

import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default ({ mode }) => {
    // Memuat variabel dari file .env berdasarkan mode (development/production)
    const env = loadEnv(mode, process.cwd());

    return defineConfig({
        plugins: [
            laravel({
                // Daftarkan semua file input CSS dan JS utama Anda di sini.
                // Sangat penting untuk mendaftarkan tema custom Filament Anda.
                input: [
                    'resources/css/app.css', 
                    'resources/js/app.js',
                    'resources/css/filament/team/theme.css',
                    'resources/css/filament/direktur/theme.css',
                    'resources/css/filament/redaksi/theme.css',
                    'resources/css/filament/keuangan/theme.css',
                    'resources/css/filament/hrd/theme.css',
                ],
                
                // INI ADALAH BAGIAN KUNCINYA:
                // Secara otomatis mengganti URL 'localhost' dengan URL server
                // yang sedang diakses (misalnya, alamat IP lokal Anda).
                // Ini memperbaiki masalah CSS yang rusak di dalam dasbor Filament.
                transformOnServe: (code, devServerUrl) => {
                    return code.replace(/http:\/\/localhost:5173/g, devServerUrl);
                },
                
                // Refresh: true adalah cara sederhana untuk mengaktifkan hot-reload
                refresh: true,
            }),
        ],
        server: {
            // Menjalankan server di semua antarmuka jaringan (0.0.0.0)
            // agar bisa diakses dari perangkat lain seperti ponsel Anda.
            host: '0.0.0.0', 
            
            // Konfigurasi untuk Hot Module Replacement (HMR)
            // agar tahu host mana yang harus dihubungi untuk pembaruan instan.
            hmr: {
                host: env.VITE_HMR_HOST || 'localhost',
            },
        },
    });
}