<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'IV MOTORCLASS')</title>
    <meta name="description" content="@yield('description', 'IV MOTORCLASS — coches de ocasión seleccionados en Málaga. Historial verificado, garantía y financiación.')" />
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <meta property="og:site_name" content="IV MOTORCLASS">
    <meta property="og:title" content="@yield('og:title', View::yieldContent('title', 'IV MOTORCLASS'))">
    <meta property="og:description" content="@yield('og:description', View::yieldContent('description', 'IV MOTORCLASS — coches de ocasión seleccionados en Málaga.'))">
    <meta property="og:type" content="@yield('og:type', 'website')">
    <meta property="og:url" content="@yield('og:url', url()->current())">
    @hasSection('og:image')
      <meta property="og:image" content="@yield('og:image')">
      <meta name="twitter:card" content="summary_large_image">
    @endif
    <meta name="twitter:title" content="@yield('twitter:title', View::yieldContent('title', 'IV MOTORCLASS'))">

    {{-- Onest: la tipografía del sistema de diseño --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    {{-- v2.css va DESPUÉS de Bootstrap: es quien manda --}}
    <script>
      // Marca el documento antes de pintar: sólo entonces el CSS oculta las secciones
      // que va a revelar el observador. Si este script no corre, todo queda visible.
      document.documentElement.classList.add('js');
      // Red de seguridad: si en 3s algo no se ha revelado, se muestra igualmente.
      setTimeout(function(){
        document.querySelectorAll('[data-anim="reveal"]:not(.is-visible)').forEach(function(el){
          el.classList.add('is-visible');
        });
      }, 3000);
    </script>
    <link rel="stylesheet" href="{{ asset('css/v2.css') }}">
    @stack('styles')
  </head>
  <body class="d-flex flex-column min-vh-100">

    {{-- ---------- barra superior ---------- --}}
    <div class="v2-topbar d-none d-lg-block">
      <div class="container d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-4">
          <span><i class="bi bi-geo-alt me-1"></i>Málaga, España</span>
          <span><i class="bi bi-clock me-1"></i>Lun–Sáb · Consultar horario</span>
        </div>
        <div class="d-flex align-items-center gap-4">
          <a href="mailto:jvmotorclass@gmail.com"><i class="bi bi-envelope me-1"></i>jvmotorclass@gmail.com</a>
          <a href="https://wa.me/34614753187" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a>
        </div>
      </div>
    </div>

    {{-- ---------- cabecera ---------- --}}
    <header class="v2-header">
      <nav class="navbar navbar-expand-lg container">
        <a class="navbar-brand v2-brand" href="{{ url('/') }}">
          <span class="v2-brand__mark">IV</span>
          <span class="v2-brand__name">MOTORCLASS</span>
        </a>

        <div class="d-flex align-items-center gap-2 order-lg-3">
          {{-- coches guardados --}}
          <div class="dropdown">
            <button class="v2-icon-btn position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="savedVehiclesBtn" aria-label="Coches guardados">
              <i class="bi bi-bookmark-heart"></i>
              <span class="v2-icon-btn__count" id="savedVehiclesCount">0</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end v2-dropdown" id="savedVehiclesDropdown">
              <li><h6 class="dropdown-header">Coches guardados</h6></li>
              <li><hr class="dropdown-divider"></li>
              <li id="savedVehiclesList">
                <div class="px-3 py-2 text-muted small">No tienes coches guardados</div>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-center" href="{{ route('saved-vehicles') }}" id="viewAllSaved">Ver todos</a></li>
            </ul>
          </div>

          <a href="tel:+34614753187" class="v2-btn v2-btn--primary d-none d-sm-inline-flex">
            <i class="bi bi-telephone-fill"></i>
            <span class="d-none d-md-inline">614 753 187</span>
            <span class="d-md-none">Llamar</span>
          </a>

          @auth
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
              @csrf
              <button type="submit" class="v2-icon-btn" title="Salir" aria-label="Salir"><i class="bi bi-box-arrow-right"></i></button>
            </form>
          @endauth

          <button class="navbar-toggler border-0 ms-1" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Menú">
            <i class="bi bi-list fs-3"></i>
          </button>
        </div>

        <div class="collapse navbar-collapse order-lg-2" id="mainNav">
          <ul class="navbar-nav v2-nav mx-lg-auto mb-2 mb-lg-0">
            <li class="nav-item"><a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">Inicio</a></li>
            <li class="nav-item"><a href="{{ route('catalog') }}" class="nav-link {{ (request()->is('catalog') && request('status') !== 'sold') ? 'active' : '' }}">Coches</a></li>
            <li class="nav-item"><a href="{{ route('catalog', ['status' => 'sold']) }}" class="nav-link {{ (request()->is('catalog') && request('status') === 'sold') ? 'active' : '' }}">Vendidos</a></li>
            <li class="nav-item"><a href="{{ route('sell-car') }}" class="nav-link {{ request()->is('sell-car') ? 'active' : '' }}">Vende tu coche</a></li>
            <li class="nav-item"><a href="{{ route('about') }}" class="nav-link {{ request()->is('despre') ? 'active' : '' }}">Nosotros</a></li>
            <li class="nav-item"><a href="{{ route('contact') }}" class="nav-link {{ request()->is('contact') ? 'active' : '' }}">Contacto</a></li>
          </ul>
        </div>
      </nav>
    </header>

    <main class="flex-grow-1">
      @yield('content')
    </main>

    {{-- ---------- pie ---------- --}}
    <footer class="v2-footer mt-auto">
      <div class="container">
        <div class="row g-5 py-5">
          <div class="col-lg-4">
            <div class="v2-brand v2-brand--light mb-3">
              <span class="v2-brand__mark">IV</span>
              <span class="v2-brand__name">MOTORCLASS</span>
            </div>
            <p class="v2-footer__text">Coches de ocasión seleccionados uno a uno. Historial verificado,
            preparación completa y acompañamiento antes y después de la compra.</p>
            <div class="d-flex gap-2 mt-4">
              <a href="https://wa.me/34614753187" class="v2-social" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
              <a href="mailto:jvmotorclass@gmail.com" class="v2-social" aria-label="Email"><i class="bi bi-envelope"></i></a>
              <a href="tel:+34614753187" class="v2-social" aria-label="Teléfono"><i class="bi bi-telephone"></i></a>
            </div>
          </div>

          <div class="col-6 col-lg-2">
            <h6 class="v2-footer__title">Comprar</h6>
            <ul class="v2-footer__links">
              <li><a href="{{ route('catalog') }}">Todos los coches</a></li>
              <li><a href="{{ route('catalog', ['body_type' => 'Sedan']) }}">Berlinas</a></li>
              <li><a href="{{ route('catalog', ['body_type' => 'Hatchback']) }}">Compactos</a></li>
              <li><a href="{{ route('catalog', ['status' => 'sold']) }}">Vendidos</a></li>
            </ul>
          </div>

          <div class="col-6 col-lg-2">
            <h6 class="v2-footer__title">Empresa</h6>
            <ul class="v2-footer__links">
              <li><a href="{{ route('about') }}">Sobre nosotros</a></li>
              <li><a href="{{ route('sell-car') }}">Vende tu coche</a></li>
              <li><a href="{{ route('contact') }}">Contacto</a></li>
            </ul>
          </div>

          <div class="col-lg-4">
            <h6 class="v2-footer__title">Contacto</h6>
            <ul class="v2-footer__contact">
              <li><i class="bi bi-telephone"></i><a href="tel:+34614753187">+34 614 753 187</a></li>
              <li><i class="bi bi-whatsapp"></i><a href="https://wa.me/34614753187" target="_blank" rel="noopener">Escríbenos por WhatsApp</a></li>
              <li><i class="bi bi-envelope"></i><a href="mailto:jvmotorclass@gmail.com">jvmotorclass@gmail.com</a></li>
              <li><i class="bi bi-geo-alt"></i><span>Málaga, España</span></li>
            </ul>
            <a href="{{ route('contact') }}" class="v2-btn v2-btn--light mt-3">
              <i class="bi bi-calendar-check"></i> Reservar una visita
            </a>
          </div>
        </div>

      </div>

      {{-- Firma de cierre: es decorativa y el nombre ya aparece dos veces arriba,
           por eso aria-hidden — un lector de pantalla no debe repetirlo una tercera vez. --}}
      <p class="v2-signature" aria-hidden="true">IV MOTORCLASS</p>

      <div class="container">
        <div class="v2-footer__bottom">
          <span>© {{ date('Y') }} IV MOTORCLASS. Todos los derechos reservados.</span>
          <span class="v2-footer__legal">Los precios y la disponibilidad pueden variar. Consulta las condiciones de garantía y financiación antes de la compra.</span>
        </div>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="{{ asset('js/saved-vehicles.js') }}"></script>
    <script>
      // atajo de teclado Ctrl + A + D -> panel de administración
      (function(){
        let ctrlHeld=false,lastA=0,lastD=0;const CHORD_MS=700;
        window.addEventListener('keydown',function(e){
          if(e.key==='Control'){ctrlHeld=true;return;}
          if(!e.ctrlKey&&!ctrlHeld)return;
          const k=(e.key||'').toLowerCase(),now=Date.now();
          if(k==='a')lastA=now; if(k==='d')lastD=now;
          if(lastA&&lastD&&Math.abs(lastA-lastD)<=CHORD_MS){e.preventDefault();window.location.assign('{{ route('admin.home') }}');lastA=lastD=0;}
        },true);
        window.addEventListener('keyup',function(e){if(e.key==='Control')ctrlHeld=false;},true);
      })();
    </script>
    @stack('scripts')
  </body>
</html>
