<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Admin • MOTORCLASS')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/pricing.css') }}">
    @stack('styles')
  </head>
  <body class="bg-light">
    <header class="admin-header navbar navbar-dark bg-dark fixed-top shadow-sm">
      <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('admin.home') }}"><i class="bi bi-speedometer2 me-2"></i>Panel Admin</a>
        <div class="d-flex align-items-center gap-2">
          <a href="{{ route('home') }}" class="btn btn-sm btn-outline-light">Ver sitio</a>
          <form action="{{ route('logout') }}" method="POST" class="mb-0">@csrf<button class="btn btn-sm btn-outline-light" type="submit">Cerrar sesión</button></form>
        </div>
      </div>
    </header>

    <div class="admin-shell container-fluid">
      <div class="row">
        <aside class="admin-sidebar col-12 col-md-3 col-lg-2 d-md-block bg-white border-end">
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
        <main class="admin-content col-md-9 ms-sm-auto col-lg-10 px-3">
          @yield('content')
        </main>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    @stack('scripts')
  </body>
 </html>


