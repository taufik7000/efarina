<!-- Scripts -->
<script>
    // Mobile menu toggle
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenu.classList.toggle('hidden');
    });
    
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-scroll');
        } else {
            navbar.classList.remove('navbar-scroll');
        }
    });
    
    // Auto-hide mobile menu on resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            document.getElementById('mobile-menu').classList.add('hidden');
        }
    });
    
    // View counter for news detail
    @if(isset($news) && request()->routeIs('news.show'))
    document.addEventListener('DOMContentLoaded', function() {
        // Increment view count after 5 seconds
        setTimeout(function() {
            fetch('{{ route("api.news.view", ["news" => $news->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).catch(error => console.log('View count error:', error));
        }, 5000);
    });
    @endif
</script>