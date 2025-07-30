<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Auto Premium - Technical Automotive')</title>
    <meta name="description" content="@yield('description', 'Descoperă perfecțiunea tehnică în mișcare. Auto Premium - dealer auto cu vehicule de performanță.')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-primary">
    <!-- Scroll Progress Bar -->
    <div class="scroll-progress" id="scroll-progress"></div>
    
    <!-- Navigation -->
    <nav class="nav-main" id="main-nav">
        <!-- Global Loading Line -->
        <div class="nav-loading-line"></div>
        
        <div class="nav-container">
            <div class="nav-content">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="nav-logo nav-glow">
                    AP<span class="font-light text-blue-600">TECH</span>
                </a>
                
                <!-- Desktop Navigation -->
                <div class="nav-links">
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        Acasă
                    </a>
                    <a href="{{ route('vehicles.index') }}" class="nav-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                        Catalog
                    </a>
                    <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">
                        Despre
                    </a>
                    <a href="{{ route('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">
                        Contact
                    </a>
                </div>
                
                <!-- CTA Button -->
                <div class="hidden md:block">
                    <a href="{{ route('vehicles.index') }}" class="nav-cta nav-glow">
                        Vezi Catalogul
                    </a>
                </div>
                
                <!-- Mobile Menu Button -->
                <button class="mobile-menu-btn" id="mobile-menu-button">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Mobile Navigation -->
            <div class="mobile-menu" id="mobile-menu">
                <div class="mobile-menu-content">
                    <a href="{{ route('home') }}" class="mobile-menu-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        Acasă
                    </a>
                    <a href="{{ route('vehicles.index') }}" class="mobile-menu-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                        Catalog
                    </a>
                    <a href="{{ route('about') }}" class="mobile-menu-link {{ request()->routeIs('about') ? 'active' : '' }}">
                        Despre
                    </a>
                    <a href="{{ route('contact') }}" class="mobile-menu-link {{ request()->routeIs('contact') ? 'active' : '' }}">
                        Contact
                    </a>
                    <a href="{{ route('vehicles.index') }}" class="nav-cta block text-center mt-4">
                        Vezi Catalogul
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main>
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p class="text-center text-gray-500 py-8">
                © {{ date('Y') }} Auto Premium. Toate drepturile rezervate.
            </p>
        </div>
    </footer>
    
    <!-- Navigation JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nav = document.getElementById('main-nav');
            const mobileMenuBtn = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const scrollProgress = document.getElementById('scroll-progress');
            const loadingLine = document.querySelector('.nav-loading-line');
            
            // Mobile Menu Toggle
            mobileMenuBtn.addEventListener('click', function() {
                mobileMenu.classList.toggle('active');
                mobileMenuBtn.classList.toggle('active');
            });
            
            // Close mobile menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!nav.contains(event.target)) {
                    mobileMenu.classList.remove('active');
                    mobileMenuBtn.classList.remove('active');
                }
            });
            
            // Scroll Progress Bar
            window.addEventListener('scroll', function() {
                const scrollTop = window.pageYOffset;
                const docHeight = document.body.offsetHeight - window.innerHeight;
                const scrollPercent = scrollTop / docHeight;
                
                scrollProgress.style.transform = `scaleX(${scrollPercent})`;
            });
            
            // Navbar scroll effect
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    nav.classList.add('scrolled');
                } else {
                    nav.classList.remove('scrolled');
                }
            });
            
            // Active page highlighting
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link, .mobile-menu-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
            
            // Enhanced loading line animation
            let loadingTimeout;
            nav.addEventListener('mouseenter', function() {
                clearTimeout(loadingTimeout);
                loadingLine.style.transform = 'scaleX(1)';
            });
            
            nav.addEventListener('mouseleave', function() {
                loadingTimeout = setTimeout(() => {
                    loadingLine.style.transform = 'scaleX(0)';
                }, 500);
            });
            
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Intersection Observer for wow animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, observerOptions);
            
            // Observe all wow animation elements
            document.querySelectorAll('.wow-observer, .wow-fade-in, .wow-slide-up, .wow-scale, .wow-rotate, .fade-in-up, .slide-in-left, .slide-in-right, .scale-in').forEach(el => {
                observer.observe(el);
            });
        });
    </script>
</body>
</html> 