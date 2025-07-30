@extends('layouts.app')

@section('title', 'Auto Premium - Technical Automotive')
@section('description', 'Descoperă perfecțiunea tehnică în mișcare. Auto Premium - dealer auto cu vehicule de performanță.')

@section('content')
    <!-- Preloader -->
    <div id="preloader" class="preloader">
        <div class="preloader-logo">AP<span class="font-light text-gray-400">TECH</span></div>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <!-- Floating Elements -->
        <div class="hero-floating">
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
        </div>
        
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    Performanță în mișcare.
                </h1>
                <h2 class="hero-secondary-title">
                    Tehnologie avansată, design inovativ.
                </h2>
                <p class="hero-subtitle">
                    O colecție de vehicule tehnice, unde fiecare detaliu contează.
                </p>
                <a href="{{ route('vehicles.index') }}" class="hero-button wow-magnetic">
                    Vezi Catalogul
                </a>
            </div>
        </div>
    </section>

    <!-- Top Recommended Models Section -->
    <section id="featured-vehicles" class="section">
        <div class="container">
            <div class="text-center wow-observer">
                <h2 class="section-title">
                    Top Modele Recomandate
                </h2>
                <p class="section-subtitle">
                    Cele mai populare vehicule din colecția noastră
                </p>
            </div>

            @if($featuredVehicles->count() > 0)
                <div class="featured-cards-grid wow-stagger">
                    <div class="featured-cards-track">
                        <!-- First set of cards -->
                        @foreach($featuredVehicles->take(3) as $vehicle)
                            <div class="featured-card wow-3d">
                                <div class="featured-card-image">
                                    <div class="featured-card-icon">🚗</div>
                                    <div class="featured-card-overlay">
                                        <div class="featured-card-badge">{{ $vehicle->fuel_type }}</div>
                                    </div>
                                </div>
                                <div class="featured-card-content">
                                    <div class="featured-card-header">
                                        <h3 class="featured-card-title">
                                            {{ $vehicle->brand }} {{ $vehicle->model }}
                                        </h3>
                                        <div class="featured-card-year">{{ $vehicle->year }}</div>
                                    </div>
                                    
                                    <div class="featured-card-details">
                                        <div class="featured-card-spec">
                                            <span class="featured-card-label">Kilometri</span>
                                            <span class="featured-card-value">{{ $vehicle->formatted_mileage }} km</span>
                                        </div>
                                        <div class="featured-card-spec">
                                            <span class="featured-card-label">Combustibil</span>
                                            <span class="featured-card-value">{{ $vehicle->fuel_type }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="featured-card-footer">
                                        <div class="featured-card-price">{{ $vehicle->formatted_price }}</div>
                                        <a href="{{ route('vehicles.show', $vehicle->slug) }}" class="featured-card-button wow-magnetic">
                                            Vezi Detalii
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        <!-- Duplicate set for seamless loop -->
                        @foreach($featuredVehicles->take(3) as $vehicle)
                            <div class="featured-card wow-3d">
                                <div class="featured-card-image">
                                    <div class="featured-card-icon">🚗</div>
                                    <div class="featured-card-overlay">
                                        <div class="featured-card-badge">{{ $vehicle->fuel_type }}</div>
                                    </div>
                                </div>
                                <div class="featured-card-content">
                                    <div class="featured-card-header">
                                        <h3 class="featured-card-title">
                                            {{ $vehicle->brand }} {{ $vehicle->model }}
                                        </h3>
                                        <div class="featured-card-year">{{ $vehicle->year }}</div>
                                    </div>
                                    
                                    <div class="featured-card-details">
                                        <div class="featured-card-spec">
                                            <span class="featured-card-label">Kilometri</span>
                                            <span class="featured-card-value">{{ $vehicle->formatted_mileage }} km</span>
                                        </div>
                                        <div class="featured-card-spec">
                                            <span class="featured-card-label">Combustibil</span>
                                            <span class="featured-card-value">{{ $vehicle->fuel_type }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="featured-card-footer">
                                        <div class="featured-card-price">{{ $vehicle->formatted_price }}</div>
                                        <a href="{{ route('vehicles.show', $vehicle->slug) }}" class="featured-card-button wow-magnetic">
                                            Vezi Detalii
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="empty-state">
                        <div class="empty-state-icon">🚗</div>
                        <h3 class="empty-state-title">Nu sunt vehicule disponibile momentan.</h3>
                        <p class="empty-state-description">Vino înapoi în curând pentru vehicule noi!</p>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- Brand Cascade Section -->
    <section class="brand-cascade">
        <div class="container">
            <div class="text-center wow-observer mb-12">
                <h3 class="section-title text-2xl md:text-3xl">
                    Mărci Premium
                </h3>
                <p class="section-subtitle text-base">
                    Lucrăm cu cele mai prestigioase mărci auto
                </p>
            </div>
            
            <div class="brand-track">
                <!-- First set of brands -->
                <div class="brand-logo">
                    <span class="text-xl font-bold text-gray-800">BMW</span>
                </div>
                <div class="brand-logo">
                    <span class="text-xl font-bold text-gray-800">Mercedes</span>
                </div>
                <div class="brand-logo">
                    <span class="text-xl font-bold text-gray-800">Audi</span>
                </div>
                <div class="brand-logo">
                    <span class="text-xl font-bold text-gray-800">Porsche</span>
                </div>
                <div class="brand-logo">
                    <span class="text-xl font-bold text-gray-800">Ferrari</span>
                </div>
                <div class="brand-logo">
                    <span class="text-xl font-bold text-gray-800">Lamborghini</span>
                </div>
                <!-- Duplicate for seamless loop -->
                <div class="brand-logo">
                    <span class="text-xl font-bold text-gray-800">BMW</span>
                </div>
                <div class="brand-logo">
                    <span class="text-xl font-bold text-gray-800">Mercedes</span>
                </div>
                <div class="brand-logo">
                    <span class="text-xl font-bold text-gray-800">Audi</span>
                </div>
                <div class="brand-logo">
                    <span class="text-xl font-bold text-gray-800">Porsche</span>
                </div>
                <div class="brand-logo">
                    <span class="text-xl font-bold text-gray-800">Ferrari</span>
                </div>
                <div class="brand-logo">
                    <span class="text-xl font-bold text-gray-800">Lamborghini</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Vehicle Finder Section -->
    <section class="vehicle-finder">
        <div class="container">
            <div class="text-center wow-observer">
                <h2 class="finder-title">
                    Găsește Vehiculul Perfect
                </h2>
                <p class="finder-subtitle">
                    Filtrează după preferințele tale
                </p>
            </div>
            
            <div class="finder-cards-grid wow-stagger">
                <div class="finder-cards-track">
                    <!-- First set of finder cards -->
                    <div class="finder-card" data-type="sport">
                        <div class="finder-card-icon">🏎️</div>
                        <div class="finder-card-content">
                            <h3 class="finder-card-title">Sport</h3>
                            <p class="finder-card-description">Performanță maximă și adrenalina la maxim</p>
                            <div class="finder-card-features">
                                <span class="finder-feature">Viteză</span>
                                <span class="finder-feature">Accelerație</span>
                                <span class="finder-feature">Design</span>
                            </div>
                        </div>
                        <div class="finder-card-arrow">→</div>
                    </div>
                    
                    <div class="finder-card" data-type="luxury">
                        <div class="finder-card-icon">👑</div>
                        <div class="finder-card-content">
                            <h3 class="finder-card-title">Luxury</h3>
                            <p class="finder-card-description">Comfort premium și tehnologie avansată</p>
                            <div class="finder-card-features">
                                <span class="finder-feature">Comfort</span>
                                <span class="finder-feature">Tehnologie</span>
                                <span class="finder-feature">Elegance</span>
                            </div>
                        </div>
                        <div class="finder-card-arrow">→</div>
                    </div>
                    
                    <div class="finder-card" data-type="suv">
                        <div class="finder-card-icon">🚙</div>
                        <div class="finder-card-content">
                            <h3 class="finder-card-title">SUV</h3>
                            <p class="finder-card-description">Versatilitate și spațiu pentru orice nevoie</p>
                            <div class="finder-card-features">
                                <span class="finder-feature">Spațiu</span>
                                <span class="finder-feature">Versatilitate</span>
                                <span class="finder-feature">Siguranță</span>
                            </div>
                        </div>
                        <div class="finder-card-arrow">→</div>
                    </div>
                    
                    <!-- Duplicate set for seamless loop -->
                    <div class="finder-card" data-type="sport">
                        <div class="finder-card-icon">🏎️</div>
                        <div class="finder-card-content">
                            <h3 class="finder-card-title">Sport</h3>
                            <p class="finder-card-description">Performanță maximă și adrenalina la maxim</p>
                            <div class="finder-card-features">
                                <span class="finder-feature">Viteză</span>
                                <span class="finder-feature">Accelerație</span>
                                <span class="finder-feature">Design</span>
                            </div>
                        </div>
                        <div class="finder-card-arrow">→</div>
                    </div>
                    
                    <div class="finder-card" data-type="luxury">
                        <div class="finder-card-icon">👑</div>
                        <div class="finder-card-content">
                            <h3 class="finder-card-title">Luxury</h3>
                            <p class="finder-card-description">Comfort premium și tehnologie avansată</p>
                            <div class="finder-card-features">
                                <span class="finder-feature">Comfort</span>
                                <span class="finder-feature">Tehnologie</span>
                                <span class="finder-feature">Elegance</span>
                            </div>
                        </div>
                        <div class="finder-card-arrow">→</div>
                    </div>
                    
                    <div class="finder-card" data-type="suv">
                        <div class="finder-card-icon">🚙</div>
                        <div class="finder-card-content">
                            <h3 class="finder-card-title">SUV</h3>
                            <p class="finder-card-description">Versatilitate și spațiu pentru orice nevoie</p>
                            <div class="finder-card-features">
                                <span class="finder-feature">Spațiu</span>
                                <span class="finder-feature">Versatilitate</span>
                                <span class="finder-feature">Siguranță</span>
                            </div>
                        </div>
                        <div class="finder-card-arrow">→</div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-12">
                <a href="{{ route('vehicles.index') }}" class="finder-cta-button">
                    Caută Vehicule
                </a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="section">
        <div class="container">
            <div class="text-center wow-observer mb-12">
                <h2 class="section-title">
                    Serviciile Noastre
                </h2>
                <p class="section-subtitle">
                    Oferim soluții complete pentru nevoile tale auto
                </p>
            </div>
            
            <div class="grid-system wow-stagger">
                <div class="service-item">
                    <div class="service-icon">🔧</div>
                    <h3 class="service-title">Service Tehnic</h3>
                    <p class="service-description">Mentenanță și reparații de înaltă calitate</p>
                </div>
                <div class="service-item">
                    <div class="service-icon">📋</div>
                    <h3 class="service-title">Consultanță</h3>
                    <p class="service-description">Sfaturi specializate pentru alegerea potrivită</p>
                </div>
                <div class="service-item">
                    <div class="service-icon">🚗</div>
                    <h3 class="service-title">Test Drive</h3>
                    <p class="service-description">Încearcă vehiculul înainte de cumpărare</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="text-center wow-observer">
                <h2 class="section-title text-white">
                    Gata să Începi?
                </h2>
                <p class="section-subtitle text-blue-100">
                    Descoperă colecția noastră de vehicule premium
                </p>
                <div class="cta-buttons">
                    <a href="{{ route('vehicles.index') }}" class="hero-button hero-button-secondary">
                        Vezi Catalogul
                    </a>
                    <a href="{{ route('contact') }}" class="hero-button">
                        Contactează-ne
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- JavaScript for animations -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');
            
            // Debug featured vehicles
            const featuredCards = document.querySelectorAll('.featured-card');
            console.log('Featured cards found:', featuredCards.length);
            
            // Debug finder cards
            const finderCards = document.querySelectorAll('.finder-card');
            console.log('Finder cards found:', finderCards.length);
            
            // Preloader
            const preloader = document.getElementById('preloader');
            if (preloader) {
                setTimeout(() => {
                    preloader.classList.add('hidden');
                }, 1000);
            }
            
            // Hero content animation
            const heroContent = document.querySelector('.hero-content');
            if (heroContent) {
                setTimeout(() => {
                    heroContent.classList.add('animated');
                }, 500);
            }
            
            // Vehicle finder functionality
            finderCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Remove active class from all cards
                    finderCards.forEach(c => c.classList.remove('selected'));
                    // Add active class to clicked card
                    this.classList.add('selected');
                });
            });
            
            // Magnetic effect for buttons
            const magneticButtons = document.querySelectorAll('.wow-magnetic');
            magneticButtons.forEach(button => {
                button.addEventListener('mousemove', function(e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left - rect.width / 2;
                    const y = e.clientY - rect.top - rect.height / 2;
                    
                    this.style.transform = `translate(${x * 0.1}px, ${y * 0.1}px)`;
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translate(0, 0)';
                });
            });
            
            // 3D effect for cards
            const cards = document.querySelectorAll('.wow-3d');
            cards.forEach(card => {
                card.addEventListener('mousemove', function(e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    
                    const rotateX = (y - centerY) / 10;
                    const rotateY = (centerX - x) / 10;
                    
                    this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0)';
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
                        console.log('Element became visible:', entry.target);
                    }
                });
            }, observerOptions);
            
            // Observe all wow animation elements
            document.querySelectorAll('.wow-observer, .wow-fade-in, .wow-slide-up, .wow-scale, .wow-rotate, .fade-in-up, .slide-in-left, .slide-in-right, .scale-in, .wow-stagger').forEach(el => {
                observer.observe(el);
                console.log('Observing element:', el);
            });
        });
    </script>
@endsection 