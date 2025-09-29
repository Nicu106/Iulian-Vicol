<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'MOTORCLASS')</title>
    <meta name="description" content="@yield('description', 'MOTORCLASS - Vehículos premium')" />

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
            <li class="nav-item"><a href="{{ route('catalog') }}" class="nav-link {{ request()->is('catalog') ? 'active' : '' }}">Catálogo</a></li>
            <li class="nav-item"><a href="{{ route('about') }}" class="nav-link {{ request()->is('despre') ? 'active' : '' }}">Sobre</a></li>
            <li class="nav-item"><a href="{{ route('contact') }}" class="nav-link {{ request()->is('contact') ? 'active' : '' }}">Contacto</a></li>
          </ul>
          <div class="d-flex align-items-center gap-2 ms-lg-3">
            <a href="{{ route('sell-car') }}" class="btn btn-success btn-sm">
              <i class="bi bi-car-front me-1"></i>Vende tu Coche
            </a>
            <a href="{{ route('catalog', ['status' => 'sold']) }}" class="btn btn-sold-highlight d-flex align-items-center">
              <i class="bi bi-lightning-charge-fill me-2"></i>Vendidos
              <span class="pulse-dot"></span>
            </a>
            <div class="dropdown">
              <button class="btn btn-outline-primary position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="savedVehiclesBtn">
                <i class="bi bi-bookmark-heart"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="savedVehiclesCount">0</span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end" id="savedVehiclesDropdown">
                <li><h6 class="dropdown-header">Coches Guardados</h6></li>
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

    <footer class="bg-dark text-light mt-auto pt-5 pb-4">
      <div class="container">
        <div class="row g-4">
          <div class="col-md-4">
            <h5 class="fw-bold d-flex align-items-center">
              <span class="brand-text">MOTORCLASS</span>
              @if (file_exists(public_path('images/logo-motorclass.png')))
                <img src="{{ asset('images/logo-motorclass.png') }}" alt="MOTORCLASS" class="brand-logo ms-2 d-none d-md-inline">
              @endif
            </h5>
            <p class="text-secondary">Concesionario de confianza. Vehículos verificados, garantía y servicios completos.</p>
          </div>
          <div class="col-md-2">
            <h6 class="fw-semibold">Enlaces</h6>
            <ul class="list-unstyled small">
              <li><a class="link-light text-decoration-none" href="{{ url('/') }}">Inicio</a></li>
              <li><a class="link-light text-decoration-none" href="{{ route('catalog') }}">Catálogo</a></li>
              <li><a class="link-light text-decoration-none" href="{{ route('about') }}">Sobre</a></li>
              <li><a class="link-light text-decoration-none" href="{{ route('contact') }}">Contacto</a></li>
            </ul>
          </div>
          <div class="col-md-3">
            <h6 class="fw-semibold">Contacto</h6>
            <ul class="list-unstyled small">
              <li>Teléfono: <a href="tel:614753187" class="link-light text-decoration-none">614 753 187</a></li>
              <li>WhatsApp: <a href="https://wa.me/34614753187" class="link-light text-decoration-none" target="_blank">Enviar mensaje</a></li>
              <li>Email: <a href="mailto:jvmotorclass@gmail.com" class="link-light text-decoration-none">jvmotorclass@gmail.com</a></li>
              <li>Dirección: Centro Comercial El Alisal<br/>Santander, España</li>
            </ul>
          </div>
          <div class="col-md-3">
            <h6 class="fw-semibold">Horario</h6>
            <p class="small mb-0">Abierto cada día<br/><span class="text-success">Consultar disponibilidad</span></p>
          </div>
        </div>
        <hr class="border-secondary my-4" />
        <div class="d-flex justify-content-between small text-secondary">
          <div>© {{ date('Y') }} MOTORCLASS. Todos los derechos reservados.</div>
          <div class="d-flex gap-3"><a class="link-light text-decoration-none" href="#">Términos</a><a class="link-light text-decoration-none" href="#">Privacidad</a></div>
        </div>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="{{ asset('js/saved-vehicles.js') }}"></script>
    @stack('scripts')
  </body>
</html>
