<!DOCTYPE html>
<html lang="ro">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Admin • APTECH')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
  </head>
  <body class="bg-light">
    <header class="admin-header navbar navbar-dark bg-dark fixed-top shadow-sm">
      <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('admin.vehicles.index') }}"><i class="bi bi-speedometer2 me-2"></i>Admin</a>
        <div class="d-flex align-items-center gap-2">
          <a href="{{ route('home') }}" class="btn btn-sm btn-outline-light">Vizitează site</a>
          <form action="{{ route('logout') }}" method="POST" class="mb-0">@csrf<button class="btn btn-sm btn-outline-light" type="submit">Logout</button></form>
        </div>
      </div>
    </header>

    <div class="admin-shell container-fluid">
      <div class="row">
        <aside class="admin-sidebar col-12 col-md-3 col-lg-2 d-md-block bg-white border-end">
          <nav class="py-3">
            <ul class="nav flex-column small">
              <li class="nav-item"><a class="nav-link" href="{{ route('admin.vehicles.index') }}"><i class="bi bi-list-ul me-2"></i>Vehicule</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ route('admin.vehicles.create') }}"><i class="bi bi-plus-circle me-2"></i>Adaugă vehicul</a></li>
            </ul>
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


