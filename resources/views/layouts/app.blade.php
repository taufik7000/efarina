<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Berita')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Styles -->
    @stack('styles')
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .text-blue-600 {
            color: #2563eb;
        }
        
        .bg-blue-600 {
            background-color: #2563eb;
        }
        
        .bg-blue-50 {
            background-color: #eff6ff;
        }
        
        .bg-blue-100 {
            background-color: #dbeafe;
        }
        
        .text-blue-800 {
            color: #1e40af;
        }
        
        .hover\:bg-blue-50:hover {
            background-color: #eff6ff;
        }
        
        .hover\:text-blue-600:hover {
            color: #2563eb;
        }
        
        .hover\:text-blue-800:hover {
            color: #1e40af;
        }
        
        .focus\:ring-blue-500:focus {
            --tw-ring-color: #3b82f6;
        }
        
        .navbar-scroll {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.9);
        }
    </style>
</head>
<body class="bg-gray-50">
    @include('partials.header')
    
    <!-- Main Content -->
    <main>
        @yield('content')
    </main>
    
    @include('partials.footer')
    
    @include('partials.scripts')
    
    @stack('scripts')
</body>
</html>