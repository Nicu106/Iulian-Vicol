{{-- The showroom's own document: the site's head (fonts, tokens, the system's
     base and buttons) without the site's header and footer. A proposal is a
     section of a page; framed in the showroom it shows that section and
     nothing around it. noindex, nofollow, no canonical. --}}
@php
  $v = fn ($p) => asset($p) . '?v=' . (@filemtime(public_path($p)) ?: 1);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title>@yield('title')</title>
<meta name="theme-color" content="@yield('theme', '#111C2E')">
<link rel="icon" href="/favicon.ico" sizes="32x32">
<link rel="preload" href="/fonts/dm-sans/dm-sans-latin-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/brandbook.css') }}">
<link rel="stylesheet" href="{{ $v('css/muestras-opiniones.css') }}">
@stack('head')
{{-- "js" only while the scripts are healthy: an error anywhere takes it off
     again, and every proposal falls back to its readable, static form. --}}
<script>
  (function (d) {
    d.classList.add('js');
    /* ?embed=1 — inside the showroom, in a frame as tall as its content: 100vh
       in here would be the whole proposal and every height derived from it
       would feed back into the frame. The screen's height comes from the page
       around the frame instead, and so does how far down the frame the
       visible part starts.
       ?embed=2 — a pinned sequence, in a screen-tall frame that scrolls on its
       own: here the frame IS the screen, nothing is borrowed. */
    try {
      var m = /[?&]embed=(\d)/.exec(location.search);
      if (m && m[1] === '2') d.classList.add('is-framed');
      if (m && m[1] === '1' && window.frameElement && window.parent.location.origin === location.origin) {
        d.classList.add('is-embed');
        var P = window.parent, F = window.frameElement;
        var view = function () {
          d.style.setProperty('--vh', (P.innerHeight / 100) + 'px');
          d.style.setProperty('--vis-top', Math.max(0, -F.getBoundingClientRect().top) + 'px');
        };
        view();
        P.addEventListener('scroll', view, { passive: true });
        P.addEventListener('resize', view);
        window.msView = function () {
          var r = F.getBoundingClientRect();
          return { top: Math.max(0, -r.top), h: P.innerHeight, frameTop: r.top };
        };
      }
    } catch (e) {}
    window.addEventListener('error', function (e) {
      if (!e.filename || /muestras-opiniones/.test(e.filename) || e.filename === location.href) d.classList.remove('js');
    });
  })(document.documentElement);
</script>
</head>
<body class="bb ms-body @yield('body')">
@yield('content')
@stack('after')
<script src="{{ $v('js/muestras-opiniones.js') }}" defer onerror="document.documentElement.classList.remove('js')"></script>
</body>
</html>
