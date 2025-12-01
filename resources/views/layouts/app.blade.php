<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'MOTORCLASS')</title>
    <meta name="description" content="@yield('description', 'MOTORCLASS - Vehículos premium')" />
    <!-- Open Graph / Twitter placeholders -->
    <meta property="og:title" content="@yield('og:title', View::yieldContent('title', 'MOTORCLASS'))">
    <meta property="og:description" content="@yield('og:description', View::yieldContent('description', 'MOTORCLASS - Vehículos premium'))">
    <meta property="og:type" content="@yield('og:type', 'website')">
    <meta property="og:url" content="@yield('og:url', url()->current())">
    <meta property="og:image" content="@yield('og:image', asset('images/logo-motorclass.png'))">
    <meta name="twitter:card" content="@yield('twitter:card', 'summary_large_image')">
    <meta name="twitter:title" content="@yield('twitter:title', View::yieldContent('title', 'MOTORCLASS'))">
    <meta name="twitter:description" content="@yield('twitter:description', View::yieldContent('description', 'MOTORCLASS - Vehículos premium'))">
    <meta name="twitter:image" content="@yield('twitter:image', asset('images/logo-motorclass.png'))">

    <!-- Bootstrap only (no Vite) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
  </head>
  <body class="d-flex flex-column min-vh-100">
    <header class="border-bottom bg-dark fixed-top">
      <nav class="navbar navbar-expand-lg navbar-dark container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
          <span class="brand-text">IV MOTORCLASS</span>
          @if (file_exists(public_path('images/logo-motorclass.png')))
            <img src="{{ asset('images/logo-motorclass.png') }}" alt="MOTORCLASS" class="brand-logo d-none d-md-inline">
          @endif
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item"><a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">Inicio</a></li>
            <li class="nav-item"><a href="{{ route('catalog') }}" class="nav-link {{ (request()->is('catalog') && request('status') !== 'sold') ? 'active' : '' }}">Catálogo</a></li>
            <li class="nav-item"><a href="{{ route('about') }}" class="nav-link {{ request()->is('despre') ? 'active' : '' }}">Sobre</a></li>
            <li class="nav-item"><a href="{{ route('contact') }}" class="nav-link {{ request()->is('contact') ? 'active' : '' }}">Contacto</a></li>
          </ul>
          <div class="d-flex align-items-center gap-2 ms-lg-3">
            <a href="{{ route('sell-car') }}" class="btn btn-success btn-sm">
              <i class="bi bi-car-front me-1"></i>Vende tu Coche
            </a>
            <a href="{{ route('catalog', ['status' => 'sold']) }}" class="btn btn-sold-highlight d-flex align-items-center {{ (request()->is('catalog') && request('status') === 'sold') ? 'active' : '' }}" @if(request()->is('catalog') && request('status') === 'sold') aria-current="page" @endif>
              <i class="bi bi-lightning-charge-fill me-2"></i>Vendidos
              <span class="pulse-dot"></span>
            </a>
            @auth
              <form action="{{ route('logout') }}" method="POST" class="d-none d-lg-inline">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">
                  <i class="bi bi-box-arrow-right me-1"></i>Salir
                </button>
              </form>
              <form action="{{ route('logout') }}" method="POST" class="d-lg-none">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm" title="Salir">
                  <i class="bi bi-box-arrow-right"></i>
                </button>
              </form>
            @endauth
            <div class="dropdown">
              <button class="btn btn-outline-primary position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="savedVehiclesBtn">
                <i class="bi bi-bookmark-heart"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="savedVehiclesCount">0</span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end" id="savedVehiclesDropdown">
              <li><h6 class="dropdown-header">Coches guardados</h6></li>
                <li><hr class="dropdown-divider"></li>
                <li id="savedVehiclesList">
                  <div class="px-3 py-2 text-muted small">No tienes coches guardados</div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-center" href="#" id="viewAllSaved">Ver todos los coches guardados</a></li>
              </ul>
            </div>
          </div>
        </div>
      </nav>
    </header>

    <main class="flex-grow-1">
      @yield('content')
    </main>

    <footer class="footer-premium mt-auto">
      <!-- Main Footer -->
      <div class="footer-main">
        <div class="container py-5">
          <div class="row g-4 g-lg-5">
            <div class="col-lg-4 col-md-6">
              <div class="footer-brand mb-4">
                <span class="footer-brand-text">IV MOTORCLASS</span>
              </div>
              <p class="text-light text-opacity-75 mb-4">Tu concesionario de confianza en Santander. Vehículos premium verificados, garantía extendida y servicios completos para una experiencia de compra excepcional.</p>
              <div class="d-flex gap-3">
                <a href="https://wa.me/34614753187" class="footer-social" target="_blank" title="WhatsApp">
                  <i class="bi bi-whatsapp"></i>
                </a>
                <a href="mailto:jvmotorclass@gmail.com" class="footer-social" title="Email">
                  <i class="bi bi-envelope"></i>
                </a>
                <a href="tel:614753187" class="footer-social" title="Teléfono">
                  <i class="bi bi-telephone"></i>
                </a>
              </div>
            </div>
            <div class="col-lg-2 col-md-6 col-6">
              <h6 class="footer-title">Navegación</h6>
              <ul class="footer-links">
                <li><a href="{{ url('/') }}">Inicio</a></li>
                <li><a href="{{ route('catalog') }}">Catálogo</a></li>
                <li><a href="{{ route('catalog', ['status' => 'sold']) }}">Vendidos</a></li>
                <li><a href="{{ route('about') }}">Sobre nosotros</a></li>
                <li><a href="{{ route('contact') }}">Contacto</a></li>
              </ul>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
              <h6 class="footer-title">Contacto</h6>
              <ul class="footer-contact">
                <li>
                  <i class="bi bi-telephone"></i>
                  <a href="tel:614753187">614 753 187</a>
                </li>
                <li>
                  <i class="bi bi-whatsapp"></i>
                  <a href="https://wa.me/34614753187" target="_blank">WhatsApp</a>
                </li>
                <li>
                  <i class="bi bi-envelope"></i>
                  <a href="mailto:jvmotorclass@gmail.com">jvmotorclass@gmail.com</a>
                </li>
                <li>
                  <i class="bi bi-geo-alt"></i>
                  <span>Centro Comercial El Alisal<br/>Santander, España</span>
                </li>
              </ul>
            </div>
            <div class="col-lg-3 col-md-6">
              <h6 class="footer-title">Horario</h6>
              <div class="footer-schedule">
                <div class="schedule-item">
                  <span class="schedule-day">Lunes - Viernes</span>
                  <span class="schedule-time">Consultar</span>
                </div>
                <div class="schedule-item">
                  <span class="schedule-day">Sábado</span>
                  <span class="schedule-time">Consultar</span>
                </div>
                <div class="schedule-item">
                  <span class="schedule-day">Domingo</span>
                  <span class="schedule-time">Consultar</span>
                </div>
              </div>
              <a href="{{ route('contact') }}" class="btn btn-outline-light btn-sm mt-3">
                <i class="bi bi-calendar-check me-2"></i>Reservar cita
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Big Brand Banner -->
      <div class="footer-brand-banner">
        <div class="container">
          <div class="brand-banner-text">IV-MOTORCLASS</div>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="footer-bottom">
        <div class="container">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div class="text-light text-opacity-50 small">© {{ date('Y') }} IV MOTORCLASS. Todos los derechos reservados.</div>
            <div class="d-flex gap-4 small">
              <a href="#" class="text-light text-opacity-50 text-decoration-none hover-light">Términos y condiciones</a>
              <a href="#" class="text-light text-opacity-50 text-decoration-none hover-light">Política de privacidad</a>
            </div>
          </div>
        </div>
      </div>
    </footer>

    <style>
      .footer-premium {
        background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
      }
      .footer-main {
        border-bottom: 1px solid rgba(255,255,255,0.1);
      }
      .footer-brand-text {
        font-size: 1.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, #fff 0%, #a0c4ff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: 1px;
      }
      .footer-title {
        color: #fff;
        font-weight: 600;
        margin-bottom: 1.25rem;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 1px;
      }
      .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
      }
      .footer-links li {
        margin-bottom: 0.75rem;
      }
      .footer-links a {
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.95rem;
      }
      .footer-links a:hover {
        color: #fff;
        padding-left: 5px;
      }
      .footer-contact {
        list-style: none;
        padding: 0;
        margin: 0;
      }
      .footer-contact li {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1rem;
        color: rgba(255,255,255,0.7);
        font-size: 0.95rem;
      }
      .footer-contact li i {
        color: #0d6efd;
        margin-top: 2px;
      }
      .footer-contact a {
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        transition: color 0.3s ease;
      }
      .footer-contact a:hover {
        color: #fff;
      }
      .footer-social {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255,255,255,0.1);
        color: #fff;
        text-decoration: none;
        transition: all 0.3s ease;
      }
      .footer-social:hover {
        background: #0d6efd;
        color: #fff;
        transform: translateY(-3px);
      }
      .footer-schedule {
        background: rgba(255,255,255,0.05);
        border-radius: 10px;
        padding: 1rem;
      }
      .schedule-item {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        font-size: 0.9rem;
      }
      .schedule-item:last-child {
        border-bottom: none;
      }
      .schedule-day {
        color: rgba(255,255,255,0.7);
      }
      .schedule-time {
        color: #fff;
        font-weight: 500;
      }
      .footer-brand-banner {
        padding: 2rem 0;
        overflow: hidden;
        background: linear-gradient(180deg, rgba(13,110,253,0.1) 0%, transparent 100%);
      }
      .brand-banner-text {
        font-size: clamp(3rem, 12vw, 8rem);
        font-weight: 900;
        text-align: center;
        color: transparent;
        -webkit-text-stroke: 2px rgba(255,255,255,0.15);
        letter-spacing: 0.05em;
        line-height: 1;
        user-select: none;
      }
      .footer-bottom {
        padding: 1.25rem 0;
        background: rgba(0,0,0,0.2);
      }
      .hover-light:hover {
        color: #fff !important;
      }
      @media (max-width: 767.98px) {
        .brand-banner-text {
          -webkit-text-stroke: 1px rgba(255,255,255,0.15);
        }
      }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="{{ asset('js/saved-vehicles.js') }}"></script>
    <script>
      // Keyboard chord: Ctrl + A + D → go to admin
      (function(){
        let ctrlHeld = false;
        let lastA = 0, lastD = 0;
        const CHORD_MS = 700;
        window.addEventListener('keydown', function(e){
          if (e.key === 'Control') { ctrlHeld = true; return; }
          if (!e.ctrlKey && !ctrlHeld) return;
          const key = (e.key || '').toLowerCase();
          const now = Date.now();
          if (key === 'a') lastA = now;
          if (key === 'd') lastD = now;
          if (lastA && lastD && Math.abs(lastA - lastD) <= CHORD_MS) {
            e.preventDefault();
            window.location.assign('{{ route('admin.home') }}');
            lastA = lastD = 0;
          }
        }, true);
        window.addEventListener('keyup', function(e){ if (e.key === 'Control') ctrlHeld = false; }, true);
      })();
    </script>
    @stack('scripts')
  </body>
</html>
