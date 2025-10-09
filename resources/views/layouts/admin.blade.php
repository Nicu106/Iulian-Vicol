<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Admin • MOTORCLASS')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/pricing.css') }}">
    @stack('styles')
  </head>
  <body class="bg-light admin-layout">
    <!-- Mobile-First Header -->
    <header class="admin-header navbar navbar-dark bg-dark fixed-top shadow-sm">
      <div class="container-fluid">
        <!-- Mobile Menu Toggle -->
        <button class="navbar-toggler d-md-none border-0 p-0 me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-expanded="false" aria-label="Toggle navigation" id="mobileMenuToggle">
          <i class="bi bi-list fs-4 text-white"></i>
        </button>
        
        <a class="navbar-brand d-flex align-items-center" href="{{ route('admin.home') }}">
          <i class="bi bi-speedometer2 me-2"></i>
          <span class="d-none d-sm-inline">Panel Admin</span>
          <span class="d-sm-none">Admin</span>
        </a>
        
        <div class="d-flex align-items-center gap-1">
          <a href="{{ route('home') }}" class="btn btn-sm btn-outline-light d-none d-sm-inline-flex">
            <i class="bi bi-eye me-1"></i>Ver sitio
          </a>
          <a href="{{ route('home') }}" class="btn btn-sm btn-outline-light d-sm-none">
            <i class="bi bi-eye"></i>
          </a>
          <form action="{{ route('logout') }}" method="POST" class="mb-0">
            @csrf
            <button class="btn btn-sm btn-outline-light d-none d-sm-inline-flex" type="submit">
              <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
            </button>
            <button class="btn btn-sm btn-outline-light d-sm-none" type="submit" title="Cerrar sesión">
              <i class="bi bi-box-arrow-right"></i>
            </button>
          </form>
        </div>
      </div>
    </header>

    <!-- Mobile Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
      <div class="offcanvas-header bg-dark text-white">
        <h5 class="offcanvas-title" id="adminSidebarLabel">
          <i class="bi bi-speedometer2 me-2"></i>Panel Admin
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body p-0">
        <nav class="admin-mobile-nav">
          <!-- Dashboard Overview -->
          <div class="nav-section">
            <h6 class="nav-section-title">Panel Principal</h6>
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.home') ? 'active' : '' }}" href="{{ route('admin.home') }}" data-bs-dismiss="offcanvas">
                  <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
              </li>
            </ul>
          </div>

          <!-- Vehicle Management -->
          <div class="nav-section">
            <h6 class="nav-section-title">Gestión de Vehículos</h6>
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.vehicles.index') ? 'active' : '' }}" href="{{ route('admin.vehicles.index') }}" data-bs-dismiss="offcanvas">
                  <i class="bi bi-list-ul me-2"></i>Lista de vehículos
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.vehicles.create') ? 'active' : '' }}" href="{{ route('admin.vehicles.create') }}" data-bs-dismiss="offcanvas">
                  <i class="bi bi-plus-circle me-2"></i>Añadir vehículo
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.sell-cars.index') ? 'active' : '' }}" href="{{ route('admin.sell-cars.index') }}" data-bs-dismiss="offcanvas">
                  <i class="bi bi-car-front me-2"></i>Coches para vender
                </a>
              </li>
            </ul>
          </div>

          <!-- Customer Communications -->
          <div class="nav-section">
            <h6 class="nav-section-title">Comunicaciones</h6>
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.inquiries.index') ? 'active' : '' }}" href="{{ route('admin.inquiries.index') }}" data-bs-dismiss="offcanvas">
                  <i class="bi bi-chat-dots me-2"></i>Solicitudes de prueba
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.contacts.index') ? 'active' : '' }}" href="{{ route('admin.contacts.index') }}" data-bs-dismiss="offcanvas">
                  <i class="bi bi-envelope-open me-2"></i>Mensajes de contacto
                </a>
              </li>
            </ul>
          </div>

          <!-- Page Content -->
          <div class="nav-section">
            <h6 class="nav-section-title">Contenido de página</h6>
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}" href="{{ route('admin.testimonials.index') }}" data-bs-dismiss="offcanvas">
                  <i class="bi bi-people me-2"></i>Opiniones de clientes
                </a>
              </li>
            </ul>
          </div>
        </nav>
      </div>
    </div>

    <div class="admin-shell container-fluid">
      <!-- Mobile Toolbar: Back + Title -->
      <div class="admin-mobile-toolbar d-md-none">
        <div class="d-flex align-items-center justify-content-between">
          <button type="button" class="btn btn-light btn-sm admin-back-btn" id="adminBackBtn" aria-label="Înapoi" data-fallback-url="{{ route('admin.home') }}">
            <i class="bi bi-arrow-left"></i>
          </button>
          <div class="admin-mobile-title text-truncate" id="adminMobileTitle">Admin</div>
          <!-- Spacer to balance layout -->
          <div style="width:36px;height:28px;"></div>
        </div>
      </div>
      <div class="row">
        <!-- Desktop Sidebar -->
        <aside class="admin-sidebar col-md-3 col-lg-2 d-none d-md-block bg-white border-end">
          <nav class="py-3">
            <!-- Dashboard Overview -->
            <div class="px-3 mb-3">
              <h6 class="text-muted text-uppercase small fw-bold mb-2">Panel Principal</h6>
              <ul class="nav flex-column small">
                <li class="nav-item">
                  <a class="nav-link {{ request()->routeIs('admin.home') ? 'active' : '' }}" href="{{ route('admin.home') }}">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                  </a>
                </li>
              </ul>
            </div>

            <!-- Vehicle Management -->
            <div class="px-3 mb-3">
              <h6 class="text-muted text-uppercase small fw-bold mb-2">Gestión de Vehículos</h6>
              <ul class="nav flex-column small">
                <li class="nav-item">
                  <a class="nav-link {{ request()->routeIs('admin.vehicles.index') ? 'active' : '' }}" href="{{ route('admin.vehicles.index') }}">
                    <i class="bi bi-list-ul me-2"></i>Lista de vehículos
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ request()->routeIs('admin.vehicles.create') ? 'active' : '' }}" href="{{ route('admin.vehicles.create') }}">
                    <i class="bi bi-plus-circle me-2"></i>Añadir vehículo
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ request()->routeIs('admin.sell-cars.index') ? 'active' : '' }}" href="{{ route('admin.sell-cars.index') }}">
                    <i class="bi bi-car-front me-2"></i>Coches para vender
                  </a>
                </li>
              </ul>
            </div>

            <!-- Customer Communications -->
            <div class="px-3 mb-3">
              <h6 class="text-muted text-uppercase small fw-bold mb-2">Comunicaciones</h6>
              <ul class="nav flex-column small">
                <li class="nav-item">
                  <a class="nav-link {{ request()->routeIs('admin.inquiries.index') ? 'active' : '' }}" href="{{ route('admin.inquiries.index') }}">
                    <i class="bi bi-chat-dots me-2"></i>Solicitudes de prueba
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ request()->routeIs('admin.contacts.index') ? 'active' : '' }}" href="{{ route('admin.contacts.index') }}">
                    <i class="bi bi-envelope-open me-2"></i>Mensajes de contacto
                  </a>
                </li>
              </ul>
            </div>

            <!-- Page Content -->
            <div class="px-3 mb-3">
              <h6 class="text-muted text-uppercase small fw-bold mb-2">Contenido de página</h6>
              <ul class="nav flex-column small">
                <li class="nav-item">
                  <a class="nav-link {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}" href="{{ route('admin.testimonials.index') }}">
                    <i class="bi bi-people me-2"></i>Opiniones de clientes
                  </a>
                </li>
              </ul>
            </div>
          </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-content col-12 col-md-9 col-lg-10 px-2 px-md-3">
          @yield('content')
        </main>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
      var sidebar = document.getElementById('adminSidebar');
      var toggler = document.getElementById('mobileMenuToggle');
      var backBtn = document.getElementById('adminBackBtn');
      var mobileTitle = document.getElementById('adminMobileTitle');
      if (!sidebar || !toggler) return;

      if (window.bootstrap && bootstrap.Offcanvas) {
        // Ensure an Offcanvas instance exists; rely on data-bs API for toggling
        bootstrap.Offcanvas.getOrCreateInstance(sidebar);
      } else {
        // Very small fallback if Bootstrap JS failed to load
        toggler.addEventListener('click', function (e) {
          e.preventDefault();
          var isOpen = sidebar.classList.toggle('show');
          sidebar.style.visibility = isOpen ? 'visible' : '';
          document.body.classList.toggle('offcanvas-open-custom', isOpen);
        });
        var dismissBtns = sidebar.querySelectorAll('[data-bs-dismiss="offcanvas"]');
        dismissBtns.forEach(function (btn) {
          btn.addEventListener('click', function () {
            sidebar.classList.remove('show');
            document.body.classList.remove('offcanvas-open-custom');
          });
        });
      }

      // Reliable navigation from offcanvas links on mobile
      var navLinks = sidebar.querySelectorAll('.nav-link');
      navLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
          var href = this.getAttribute('href');
          if (!href || href === '#') return;

          if (window.bootstrap && bootstrap.Offcanvas) {
            e.preventDefault();
            var instance = bootstrap.Offcanvas.getOrCreateInstance(sidebar);
            instance.hide();
            setTimeout(function () { window.location.assign(href); }, 150);
          } else {
            e.preventDefault();
            sidebar.classList.remove('show');
            document.body.classList.remove('offcanvas-open-custom');
            setTimeout(function () { window.location.assign(href); }, 50);
          }
        });
      });

      // Mobile Back Button
      if (backBtn) {
        backBtn.addEventListener('click', function () {
          if (window.history.length > 1) {
            window.history.back();
          } else {
            var fallback = backBtn.getAttribute('data-fallback-url');
            if (fallback) window.location.assign(fallback);
          }
        });
      }

      // Mobile Title from current page title
      if (mobileTitle) {
        var pageTitle = document.title.replace(/^[^•]*•\s*/, '').trim();
        if (!pageTitle) pageTitle = 'Admin';
        mobileTitle.textContent = pageTitle;
      }
    });
    </script>
    
    @stack('scripts')
  </body>
 </html>


