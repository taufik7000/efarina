<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Berita')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/icon-real.png">
    
    <!-- Vite Assets -->
    
    <!-- Additional Styles -->
    @stack('styles')
</head>
<body class="bg-gray-50 font-sans">
    <!-- Main Content -->
    @include('partials.header')
    <main>
        @yield('content')
    </main>
    @include('partials.footer')
    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>