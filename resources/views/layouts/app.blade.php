<!DOCTYPE html>
<html lang="ro">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'APTECH Auto')</title>
    <meta name="description" content="@yield('description', 'Ecommerce auto - APTECH')" />

    <!-- Bootstrap only (no Vite) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
  </head>
  <body class="d-flex flex-column min-vh-100">
    <header class="border-bottom bg-dark fixed-top">
      <nav class="navbar navbar-expand-lg navbar-dark container">
        <a class="navbar-brand fw-bold" href="{{ url('/') }}"><span class="text-light">AP</span><span class="text-primary">TECH</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item"><a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">Acasă</a></li>
            <li class="nav-item"><a href="{{ route('catalog') }}" class="nav-link {{ request()->is('catalog') ? 'active' : '' }}">Catalog</a></li>
            <li class="nav-item"><a href="{{ route('about') }}" class="nav-link {{ request()->is('despre') ? 'active' : '' }}">Despre</a></li>
            <li class="nav-item"><a href="{{ route('contact') }}" class="nav-link {{ request()->is('contact') ? 'active' : '' }}">Contact</a></li>
          </ul>
          <a href="{{ route('catalog') }}" class="btn btn-primary ms-lg-3">Vezi Catalogul</a>
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
            <h5 class="fw-bold"><span class="text-light">AP</span><span class="text-primary">TECH</span></h5>
            <p class="text-secondary">Dealer auto de încredere. Vehicule verificate, garanție și servicii complete.</p>
          </div>
          <div class="col-md-2">
            <h6 class="fw-semibold">Link-uri</h6>
            <ul class="list-unstyled small">
              <li><a class="link-light text-decoration-none" href="{{ url('/') }}">Acasă</a></li>
              <li><a class="link-light text-decoration-none" href="{{ route('catalog') }}">Catalog</a></li>
              <li><a class="link-light text-decoration-none" href="{{ route('about') }}">Despre</a></li>
              <li><a class="link-light text-decoration-none" href="{{ route('contact') }}">Contact</a></li>
            </ul>
          </div>
          <div class="col-md-3">
            <h6 class="fw-semibold">Contact</h6>
            <ul class="list-unstyled small">
              <li>Telefon: <a href="tel:+40123456789" class="link-light text-decoration-none">+40 123 456 789</a></li>
              <li>Email: <a href="mailto:contact@aptech.ro" class="link-light text-decoration-none">contact@aptech.ro</a></li>
              <li>Adresă: București, România</li>
            </ul>
          </div>
          <div class="col-md-3">
            <h6 class="fw-semibold">Program</h6>
            <p class="small mb-0">Luni–Vineri: 09:00–18:00<br/>Sâmbătă: 10:00–16:00</p>
          </div>
        </div>
        <hr class="border-secondary my-4" />
        <div class="d-flex justify-content-between small text-secondary">
          <div>© {{ date('Y') }} APTECH. Toate drepturile rezervate.</div>
          <div class="d-flex gap-3"><a class="link-light text-decoration-none" href="#">Termeni</a><a class="link-light text-decoration-none" href="#">Confidențialitate</a></div>
        </div>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @stack('scripts')
  </body>
</html>
