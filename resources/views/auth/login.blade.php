<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Efarina TV</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="font-sans antialiased bg-slate-100">

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-6xl mx-auto flex rounded-2xl shadow-2xl overflow-hidden bg-white">
        
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-slate-900 to-slate-800 relative">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.02"%3E%3Cpath d="M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
            
            <div class="relative z-10 flex flex-col justify-between p-12 text-white">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-xl flex items-center justify-center">
                        <i class="fas fa-tv text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">Efarina TV</h1>
                        <p class="text-slate-300 text-sm">Media & Broadcasting</p>
                    </div>
                </div>

                <div class="quotes-container min-h-[220px]">
                    <div class="quote-item active">
                        <blockquote class="text-xl font-light leading-relaxed">
                            <p class="mb-5">"Jurnalisme adalah sastra yang terburu-buru, tetapi dampaknya bisa bertahan selamanya."</p>
                            <footer class="text-slate-400 text-sm">
                                — Matthew Arnold, Penulis & Kritikus
                            </footer>
                        </blockquote>
                    </div>
                    <div class="quote-item">
                        <blockquote class="text-xl font-light leading-relaxed">
                            <p class="mb-5">"Berita yang baik membutuhkan waktu. Berita yang buruk menyebar dengan sendirinya."</p>
                            <footer class="text-slate-400 text-sm">
                                — Warren Buffett, Investor
                            </footer>
                        </blockquote>
                    </div>
                    <div class="quote-item">
                        <blockquote class="text-xl font-light leading-relaxed">
                            <p class="mb-5">"Media adalah pesan. Cara kita menyampaikan berita sama pentingnya dengan berita itu sendiri."</p>
                            <footer class="text-slate-400 text-sm">
                                — Marshall McLuhan, Teoris Media
                            </footer>
                        </blockquote>
                    </div>
                    <div class="quote-item">
                        <blockquote class="text-xl font-light leading-relaxed">
                            <p class="mb-5">"Tugas jurnalis adalah memisahkan gandum dari sekam, kebenaran dari kebohongan."</p>
                            <footer class="text-slate-400 text-sm">
                                — Walter Cronkite, Anchor Berita
                            </footer>
                        </blockquote>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12">
            <div class="w-full max-w-md">
                
                <div class="text-center lg:text-left mb-10">
                    <h2 class="text-3xl font-bold text-slate-800">Masuk ke Dashboard</h2>
                    <p class="text-slate-500 mt-2">Selamat datang kembali! Silakan masuk.</p>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-6 rounded-md" role="alert">
                        <div class="flex">
                            <div class="py-1"><i class="fas fa-exclamation-circle mr-3"></i></div>
                            <div>
                                <p class="font-bold">Terjadi kesalahan</p>
                                <p class="text-sm">{{ $errors->first() }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                            Alamat Email
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-envelope text-slate-400"></i>
                            </span>
                            <input 
                                id="email" 
                                name="email" 
                                type="email" 
                                value="{{ old('email') }}"
                                required 
                                autofocus
                                autocomplete="email"
                                placeholder="nama@email.com"
                                class="block w-full pl-10 pr-4 py-3 border border-slate-300 rounded-lg bg-slate-50 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-lock text-slate-400"></i>
                            </span>
                            <input 
                                id="password" 
                                name="password" 
                                type="password" 
                                required
                                autocomplete="current-password"
                                placeholder="Masukkan password Anda"
                                class="block w-full pl-10 pr-4 py-3 border border-slate-300 rounded-lg bg-slate-50 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            >
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input 
                                id="remember_me" 
                                name="remember" 
                                type="checkbox"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded"
                            >
                            <label for="remember_me" class="ml-2 block text-sm text-slate-800">
                                Ingat saya
                            </label>
                        </div>
                        <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                            Lupa password?
                        </a>
                    </div>

                    <div>
                        <button 
                            type="submit" 
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-indigo-500/50"
                        >
                            Sign In
                        </button>
                    </div>
                </form>
                
                <div class="mt-8 text-center">
                    <p class="text-sm text-slate-500">
                        Kembali ke  <a href="/" class="font-medium text-indigo-600 hover:underline">Beranda</a>
                    </p>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
.quotes-container {
    position: relative;
}
.quote-item {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
}
.quote-item.active {
    opacity: 1;
    transform: translateY(0);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quotes = document.querySelectorAll('.quote-item');
    if (quotes.length > 0) {
        let currentQuote = 0;
        
        function showNextQuote() {
            quotes[currentQuote].classList.remove('active');
            currentQuote = (currentQuote + 1) % quotes.length;
            quotes[currentQuote].classList.add('active');
        }
        
        setInterval(showNextQuote, 5000); // Change quote every 5 seconds
    }
});
</script>

</body>
</html>