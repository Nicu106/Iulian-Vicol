{{--
  The admin shell.

  What it replaces: a fixed dark navbar, a Bootstrap offcanvas, a desktop rail,
  and the SAME four-section navigation written out twice — once for each — plus
  Bootstrap's JS bundle to open the drawer and a hand-rolled fallback for when
  that failed to load. 220 lines of markup and script to show six links.

  Here the links are a row in the bar. Below 900 they take their own line and
  that line scrolls sideways; the public header already proved that pattern
  (wrapping is what made it 148px tall at 320 with four links). No drawer, no
  toggle, no JavaScript, nothing to fail to load.

  The order is frequency, measured: a car goes up about once a week (41 cars in
  eleven months), the messages arrive daily, the reviews come in bursts.
  "Solicitudes de prueba" is not here — the table has held zero rows since it
  was created, and the test-drive request was taken off the car page on the
  client's own instruction. Its route still works and the Panel links to it the
  moment a row appears.
--}}
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex, nofollow">
<title>@yield('title', 'Panel — IV MOTORCLASS')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&display=swap">
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/ad.css') }}">
@stack('css')
</head>
<body class="ad">

@php
  $inbox = \App\Support\Inbox::counts();
  $nav = [
    ['admin.home',              'Panel',     null],
    ['admin.vehicles.index',    'Coches',    null],
    ['admin.contacts.index',    'Mensajes',  $inbox['real'] ?: null],
    ['admin.testimonials.index','Opiniones', null],
    ['admin.sell-cars.index',   'Ventas',    null],
  ];
@endphp

<header class="ad-top">
  <div class="ad-wrap ad-top__in">
    <a class="ad-mark" href="{{ route('admin.home') }}"><b>IV MOTORCLASS</b> <span>Panel</span></a>

    <div class="ad-navwrap">
      <nav aria-label="Secciones">
        <ul class="ad-nav">
          @foreach($nav as [$route, $label, $count])
            <li>
              <a class="ad-nav__i {{ request()->routeIs(str_replace('.index', '.*', $route)) ? 'is-on' : '' }}"
                 href="{{ route($route) }}"
                 @if(request()->routeIs(str_replace('.index', '.*', $route))) aria-current="page" @endif>
                {{ $label }}@if($count)<span class="ad-nav__n">{{ $count }}</span>@endif
              </a>
            </li>
          @endforeach
        </ul>
      </nav>
    </div>

    <div class="ad-top__end">
      <a class="ad-out" href="{{ route('inicio') }}">Ver la web</a>
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button class="ad-out" type="submit">Salir</button>
      </form>
    </div>
  </div>
</header>

<main class="ad-main">
  <div class="ad-wrap">
    @if(session('status'))
      <p class="ad-flash">{{ session('status') }}</p>
    @endif
    @if(session('error'))
      <p class="ad-flash ad-flash--bad">{{ session('error') }}</p>
    @endif

    @yield('content')
  </div>
</main>

@stack('js')
</body>
</html>
