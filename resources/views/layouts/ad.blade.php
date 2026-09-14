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
  /* Sections, and the rule for how many fit on a phone, live in
     App\Support\AdminNav. Add one there; nothing here changes. */
  $items = \App\Support\AdminNav::items();
  [$bar, $over] = \App\Support\AdminNav::split($items);
  $overOn = collect($over)->contains('on', true) || \App\Support\AdminNav::current() === 'admin.more';
  $overCount = collect($over)->sum(fn ($i) => (int) $i['count']);
@endphp

<div class="ad-shell">

  {{-- One navigation. A rail on the left from 1000, the same element as a
       bottom bar below it. The old layout printed this whole menu twice — once
       inside a Bootstrap offcanvas, once in a desktop column — with a JS
       bundle and a hand-rolled fallback to open the drawer. --}}
  <header class="ad-rail">
    <div class="ad-rail__head">
      <a class="ad-mark" href="{{ route('admin.home') }}"><b>IV MOTORCLASS</b> <span>Panel</span></a>
      <div class="ad-rail__end">
        <a class="ad-out" href="{{ route('inicio') }}">Ver la web</a>
        <form action="{{ route('logout') }}" method="POST">
          @csrf
          <button class="ad-out" type="submit">Salir</button>
        </form>
      </div>
    </div>

    <nav class="ad-rail__nav" aria-label="Secciones">
      <ul class="ad-nav">
        @php $g = null; @endphp
        @foreach($items as $idx => $i)
          @if($i['group'] && $i['group'] !== $g)
            <li class="ad-nav__g">{{ $i['group'] }}</li>
          @endif
          @php $g = $i['group']; @endphp
          <li class="{{ $idx >= count($bar) ? 'is-over' : '' }}">
            <a class="ad-nav__i {{ $i['on'] ? 'is-on' : '' }}" href="{{ $i['href'] }}"
               @if($i['on']) aria-current="page" @endif>
              <span class="ad-nav__l"><span class="ad-nav__rail">{{ $i['label'] }}</span><span class="ad-nav__tab">{{ $i['tab'] }}</span>@if($i['count'])<span class="ad-nav__n">{{ $i['count'] }}</span>@endif</span>
            </a>
          </li>
        @endforeach

        {{-- Only on a phone, and only past five sections. A link to a real
             page, so it works everywhere; where the popover API exists the
             script below opens the same list as a sheet instead. --}}
        @if($over)
          <li class="ad-nav__more">
            <a class="ad-nav__i {{ $overOn ? 'is-on' : '' }}" href="{{ route('admin.more') }}"
               data-sheet="ad-more" aria-haspopup="dialog" aria-controls="ad-more" aria-expanded="false"
               @if(\App\Support\AdminNav::current() === 'admin.more') aria-current="page" @endif>
              <span class="ad-nav__l"><span>Más</span>@if($overCount)<span class="ad-nav__n">{{ $overCount }}</span>@endif</span>
            </a>
          </li>
        @endif
      </ul>
    </nav>
  </header>

  @if($over)
    <div class="ad-sheet" id="ad-more" popover aria-label="Más secciones">
      <div class="ad-sheet__head">
        <p class="ad-sheet__t">Más secciones</p>
        <button class="ad-btn ad-btn--q ad-btn--s" type="button" popovertarget="ad-more" popovertargetaction="hide">Cerrar</button>
      </div>
      @include('admin.partials.nav-list', ['items' => $over])
    </div>
  @endif

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

</div>

@if($over)
<script>
/* "Más" is a link to /admin/mas. Where the browser has the popover API it opens
   the same list as a sheet instead of loading a page; without it (JavaScript
   off, Safari before 17) the link simply works. */
(function () {
  var sheet = document.getElementById('ad-more');
  if (!sheet || typeof sheet.showPopover !== 'function') return;
  var tabs = document.querySelectorAll('[data-sheet="ad-more"]');
  tabs.forEach(function (a) {
    a.addEventListener('click', function (e) {
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      e.preventDefault();
      sheet.showPopover();
    });
  });
  sheet.addEventListener('toggle', function (e) {
    var open = e.newState === 'open';
    tabs.forEach(function (a) { a.setAttribute('aria-expanded', open ? 'true' : 'false'); });
    if (open) { var first = sheet.querySelector('.ad-navlist__i'); if (first) first.focus({ preventScroll: true }); }
  });
})();
</script>
@endif
@stack('js')
</body>
</html>
