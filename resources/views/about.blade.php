@extends('layouts.app')

@section('title', 'Despre Noi - Auto Premium')
@section('description', 'Descoperă povestea Auto Premium, misiunea noastră și valorile care ne definesc ca dealer auto de încredere.')

@section('content')
    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <div class="about-hero-content">
                <h1 class="about-hero-title">
                    Despre Auto Premium
                </h1>
                <p class="about-hero-subtitle">
                    Dealer auto de încredere cu peste 15 ani de experiență în industria auto românească
                </p>
            </div>
        </div>
    </section>

    <!-- Story Section -->
    <section class="story-section">
        <div class="container">
            <div class="story-content">
                <div class="story-text fade-in-up">
                    <h2 class="story-title">
                        Povestea Noastră
                    </h2>
                    <div class="space-y-6">
                        <p class="story-paragraph">
                            Auto Premium a fost înființat în 2008 cu o viziune clară: să oferim clienților noștri 
                            cea mai bună experiență în achiziționarea unui vehicul. În ultimii 15 ani, am crescut 
                            de la un mic dealer local la unul dintre cei mai respectați dealer auto din România.
                        </p>
                        <p class="story-paragraph">
                            Misiunea noastră este să simplificăm procesul de achiziționare a unui vehicul, 
                            oferind transparență totală, prețuri competitive și servicii de calitate superioară.
                        </p>
                        <p class="story-paragraph">
                            Fiecare vehicul din catalogul nostru este verificat cu atenție de echipa noastră 
                            de tehnicieni specializați, garantând că vei primi doar vehicule în stare excelentă.
                        </p>
                    </div>
                </div>
                <div class="story-stats scale-in">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number">15+</div>
                            <div class="stat-label">Ani de Experiență</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Vehicule în Stoc</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">1000+</div>
                            <div class="stat-label">Clienți Mulțumiți</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">Suport Tehnic</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Timeline Section -->
    <section class="section bg-gray-50">
        <div class="container">
            <div class="text-center mb-16 fade-in-up">
                <h2 class="section-title">
                    Evoluția Noastră
                </h2>
                <p class="section-subtitle">
                    Povestea succesului nostru prin anii
                </p>
            </div>

            <div class="timeline">
                <div class="timeline-item slide-in-left">
                    <div class="timeline-content">
                        <div class="timeline-dot"></div>
                        <div class="timeline-year">2008</div>
                        <h3 class="timeline-title">Înființarea</h3>
                        <p class="timeline-description">
                            Auto Premium își începe activitatea ca mic dealer local cu focus pe calitate și încredere.
                        </p>
                    </div>
                </div>

                <div class="timeline-item slide-in-right">
                    <div class="timeline-content">
                        <div class="timeline-dot"></div>
                        <div class="timeline-year">2013</div>
                        <h3 class="timeline-title">Prima Expansiune</h3>
                        <p class="timeline-description">
                            Deschiderea primului showroom modern și extinderea catalogului cu vehicule premium.
                        </p>
                    </div>
                </div>

                <div class="timeline-item slide-in-left">
                    <div class="timeline-content">
                        <div class="timeline-dot"></div>
                        <div class="timeline-year">2018</div>
                        <h3 class="timeline-title">Lansarea Online</h3>
                        <p class="timeline-description">
                            Implementarea platformei online pentru o experiență de cumpărare modernă și transparentă.
                        </p>
                    </div>
                </div>

                <div class="timeline-item slide-in-right">
                    <div class="timeline-content">
                        <div class="timeline-dot"></div>
                        <div class="timeline-year">2023</div>
                        <h3 class="timeline-title">Lider în Industrie</h3>
                        <p class="timeline-description">
                            Devenim unul dintre cei mai respectați dealer auto din România cu peste 1000 de clienți mulțumiți.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="values-section">
        <div class="container">
            <div class="values-header fade-in-up">
                <h2 class="values-title">
                    Valorile Noastre
                </h2>
                <p class="values-subtitle">
                    Principiile care ne definesc și ne ghidează în fiecare zi
                </p>
            </div>

            <div class="values-grid">
                <div class="value-card scale-in">
                    <div class="value-icon">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="value-title">Transparență</h3>
                    <p class="value-description">
                        Credem în transparența totală în toate aspectele afacerii noastre. 
                        Fiecare vehicul vine cu istoricul complet și verificarea tehnică detaliată.
                    </p>
                </div>

                <div class="value-card scale-in">
                    <div class="value-icon">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="value-title">Calitate</h3>
                    <p class="value-description">
                        Fiecare vehicul din catalogul nostru este verificat cu atenție de tehnicieni 
                        specializați pentru a garanta calitatea superioară.
                    </p>
                </div>

                <div class="value-card scale-in">
                    <div class="value-icon">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <h3 class="value-title">Încredere</h3>
                    <p class="value-description">
                        Construim relații de încredere cu clienții noștri prin servicii de calitate 
                        și suport tehnic continuu după achiziție.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <div class="team-header fade-in-up">
                <h2 class="team-title">
                    Echipa Noastră
                </h2>
                <p class="team-subtitle">
                    Profesioniști dedicați cu experiență vastă în industria auto
                </p>
            </div>

            <div class="team-grid">
                <div class="team-member scale-in">
                    <div class="member-avatar">
                        <svg class="w-16 h-16 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="member-name">Alexandru Popescu</h3>
                    <p class="member-position">Director General</p>
                    <p class="member-experience">15+ ani experiență în industria auto</p>
                </div>

                <div class="team-member scale-in">
                    <div class="member-avatar">
                        <svg class="w-16 h-16 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="member-name">Maria Ionescu</h3>
                    <p class="member-position">Manager Vânzări</p>
                    <p class="member-experience">Expert în relații cu clienții</p>
                </div>

                <div class="team-member scale-in">
                    <div class="member-avatar">
                        <svg class="w-16 h-16 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                    </div>
                    <h3 class="member-name">Ion Dumitrescu</h3>
                    <p class="member-position">Șef Atelier</p>
                    <p class="member-experience">Tehnician certificat cu 12+ ani experiență</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="about-cta">
        <div class="container">
            <div class="cta-content fade-in-up">
                <h2 class="cta-title">
                    Gata să-ți Găsești Mașina Perfectă?
                </h2>
                <p class="cta-subtitle">
                    Contactează-ne acum pentru o consultație personalizată 
                    și descoperă ofertele noastre speciale.
                </p>
                <div class="cta-buttons">
                    <a href="{{ route('vehicles.index') }}" class="cta-primary">
                        Vezi Catalogul
                    </a>
                    <a href="{{ route('contact') }}" class="cta-secondary">
                        Contactează-ne
                    </a>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Intersection Observer for animations
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

        // Observe all animated elements
        document.addEventListener('DOMContentLoaded', () => {
            const animatedElements = document.querySelectorAll('.fade-in-up, .slide-in-left, .slide-in-right, .scale-in');
            animatedElements.forEach(el => observer.observe(el));
        });
    </script>
@endsection 