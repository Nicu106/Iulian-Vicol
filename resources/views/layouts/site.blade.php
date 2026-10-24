{{-- ============================================================================
     The site. One document, every page.

     Before this each page was a standalone <!DOCTYPE html> repeating the same
     twenty lines: charset, viewport, robots, two preconnects, the DM Sans link,
     and the four stylesheets every page loads — then @include('partials.head')
     and @include('partials.foot') at the ends. Five copies of one head. Adding a
     stylesheet or a meta tag meant five edits, and the /coche page had already
     drifted: it carried `interactive-widget=resizes-content` in its viewport and
     /catalogo did not.

     A page now brings its content and nothing else. What it may add:

       @section('title')      the <title>, WHOLE. Not a fragment with the company
                              name appended: /inicio leads with it — "IV MOTORCLASS
                              — Coches alemanes premium en Málaga" — and the others
                              trail it. A layout that appends cannot express both,
                              and quietly rewrote the home page's title when it
                              tried.
       @section('current')    which nav item is marked. Required.
       @section('body')       extra <body> classes — /coche's sold theme uses it.
       @push('css')           page-only stylesheets, after the shared four.
       @push('head')          preloads and anything else in <head>.
       @section('content')    the page.
       @section('after')      what sits BELOW the footer: the phone dock, the
                              full-screen photo viewer. Rare, and explicit.
       @push('js')            page scripts, after the DOM they act on.
     ========================================================================= --}}
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
{{-- interactive-widget: the phone keyboard resizes the viewport instead of
     scrolling the page under it, which is what a page with forms wants. It was
     on three pages of five before this. --}}
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
<meta name="robots" content="@yield('robots', 'index, follow')">
<title>@yield('title')</title>
@php
  // What a shared link shows (WhatsApp, Facebook, Google). A page may set
  // @section('description') and @section('og_image'); the rest is derived.
  $metaDesc  = trim($__env->yieldContent('description')) ?: 'Coches alemanes premium en Málaga: Volkswagen, Audi, BMW, Mercedes-Benz y Porsche. Seleccionados, revisados y con garantía. Escríbeme por WhatsApp.';
  $metaImage = trim($__env->yieldContent('og_image')) ?: url('/img/og-default.jpg');
  $metaUrl   = url()->current();
@endphp
<meta name="description" content="{{ $metaDesc }}">
@unless(trim($__env->yieldContent('robots')))<link rel="canonical" href="{{ $metaUrl }}">@endunless
<meta property="og:type" content="website">
<meta property="og:site_name" content="IV MOTORCLASS">
<meta property="og:locale" content="es_ES">
<meta property="og:title" content="@yield('title')">
<meta property="og:description" content="{{ $metaDesc }}">
<meta property="og:url" content="{{ $metaUrl }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#111C2E">
<link rel="icon" href="/favicon.ico" sizes="32x32">
<link rel="icon" href="/icons/icon-192.png" type="image/png" sizes="192x192">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
<link rel="manifest" href="/manifest.json">

<link rel="preload" href="/fonts/dm-sans/dm-sans-latin-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="{{ asset('css/fonts.css') }}">

{{-- The four every page loads, in the order the cascade needs: tokens, then the
     system, then the page furniture, then the footer. --}}
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/brandbook.css') }}">
<link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
<link rel="stylesheet" href="{{ asset('css/foot.css') }}">
@stack('css')
@stack('head')
</head>
<body class="bb cat @yield('body')">

@include('partials.head', ['current' => trim($__env->yieldContent('current'))])

@yield('content')

@include('partials.foot')

@yield('after')

{{-- The panel's statistics (App\Support\Stats, POST /s — read its docblock).
     Anonymous and counted, never followed: no cookie, no storage, nothing typed.
     One beacon when the page opens; one when it is hidden or left, with the
     seconds it was on screen, how far down it was read and how many photographs
     were seen; one per press on WhatsApp, the phone, e-mail, the map, or any
     element marked data-stat="name". A form marked data-stat-form="name" says
     when it is started and when it is sent, never what is in it. --}}
<script>
(function () {
  var path = location.pathname, beacon = navigator.sendBeacon && navigator.sendBeacon.bind(navigator);
  function send(d) {
    if (!beacon) return;
    d.p = path;
    try { beacon('/s', new URLSearchParams(d)); } catch (e) {}
  }
  window.mcStat = function (t, name) { send(name ? { t: 'ev', e: name } : { t: t }); };
  send({ t: 'view', q: location.search, r: document.referrer });

  // The bonnet placard's QR opens /coche/…?c=qr: counted above, then taken off
  // the address so a link shared from here is not another "scan".
  if (/[?&]c=qr(&|$)/.test(location.search) && history.replaceState) {
    try {
      var u = new URL(location.href); u.searchParams.delete('c');
      history.replaceState(history.state, '', u.pathname + u.search + u.hash);
    } catch (e) {}
  }

  // Time on screen, depth, photographs. Sent as differences, so a page hidden
  // and shown again many times still adds up once.
  var on = document.visibilityState === 'visible' ? Date.now() : 0, ms = 0, sent = 0;
  var y = 0, ySent = 0, seen = {}, nSeen = 0, phSent = 0, first = true, tick = false;
  function depth() {
    tick = false;
    var h = document.documentElement.scrollHeight, v = innerHeight + (scrollY || pageYOffset || 0);
    var pct = h > 0 ? Math.round(100 * v / h) : 100;
    if (pct >= 97) pct = 100;
    if (pct > y) y = pct;
  }
  addEventListener('scroll', function () { if (!tick) { tick = true; requestAnimationFrame(depth); } }, { passive: true });
  addEventListener('load', depth);
  window.mcStatPhoto = function (k) { if (!seen[k]) { seen[k] = 1; nSeen++; } };
  function bucket(v) { return Math.min(100, Math.floor(v / 25) * 25); }
  function flush() {
    if (on) { ms += Date.now() - on; on = 0; }
    var total = Math.min(1800, Math.round(ms / 1000)), s = total - sent, b = bucket(y);
    if (!first && s <= 0 && b <= ySent && nSeen <= phSent) return;
    send({ t: 'eng', s: Math.max(0, s), y: b, y0: ySent, ph: nSeen - phSent, f: first ? '1' : '0' });
    sent = total; ySent = b; phSent = nSeen; first = false;
  }
  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'hidden') flush(); else on = Date.now();
  });
  addEventListener('pagehide', flush);

  // Presses. Capture, so a link that leaves the page is counted before it goes.
  // A control that is already open (aria-expanded="true") is being closed: not counted.
  document.addEventListener('click', function (e) {
    var el = e.target.closest && e.target.closest('a[href], [data-stat]');
    if (!el) return;
    var name = el.getAttribute('data-stat');
    if (name) { if (el.getAttribute('aria-expanded') !== 'true') window.mcStat('ev', name); return; }
    var href = el.getAttribute('href') || '', host = '';
    try { host = new URL(el.href).hostname; } catch (x) {}
    if (/(^|\.)wa\.me$|(^|\.)whatsapp\.com$/.test(host)) window.mcStat('wa');
    else if (/^tel:/i.test(href)) window.mcStat('tel');
    else if (/^mailto:/i.test(href)) window.mcStat('mail');
    else if (/(^|\.)google\.[a-z.]+$/.test(host) && /\/maps/.test(el.href) || /^maps\.google\./.test(host)) window.mcStat('ev', 'mapa');
  }, true);

  // A full-screen viewer and the like: counted when it opens.
  document.querySelectorAll('[data-stat-open]').forEach(function (el) {
    new MutationObserver(function () {
      if (!el.hidden) window.mcStat('ev', el.getAttribute('data-stat-open'));
    }).observe(el, { attributes: true, attributeFilter: ['hidden'] });
  });

  // Forms: started (the first time anything in them gets the focus), and sent.
  document.querySelectorAll('form[data-stat-form]').forEach(function (f) {
    var name = f.getAttribute('data-stat-form'), started = false;
    f.addEventListener('focusin', function () { if (!started) { started = true; window.mcStat('ev', name + '-empieza'); } });
    // data-stat-sent="js": the page says itself when it really sent (/contacto).
    if (f.getAttribute('data-stat-sent') !== 'js') f.addEventListener('submit', function () { window.mcStat('ev', name + '-envia'); });
  });
})();
</script>

{{-- A recommendation code rides into WhatsApp.
     /r/{code} leaves the code in the mc_ref cookie (App\Support\Referral).
     Buyers here write on WhatsApp rather than fill in forms, so while the
     cookie is there every WhatsApp link on the site carries the code in its
     prefilled text and the owner sees it in the conversation. Links marked
     data-no-ref are left alone: the person sharing their OWN link. Pages that
     open WhatsApp from script read window.mcRefNote. --}}
<script>
(function () {
  var m = document.cookie.match(/(?:^|;\s*)mc_ref=([A-Z0-9]{4,16})(?:;|$)/);
  window.mcRefNote = m ? '\n\n(Código de recomendación: ' + m[1] + ')' : '';
  // Reports a press that leaves the site (WhatsApp, e-mail) for the recommendation
  // journey. A no-op for anyone who did not arrive through a link.
  window.mcRefTrack = function (t) {
    if (!m || !navigator.sendBeacon) return;
    try { navigator.sendBeacon('/r/e', new URLSearchParams({ t: t, p: location.pathname })); } catch (e) {}
  };
  if (!m) return;
  var code = m[1];
  function isWa(a) {
    try { var h = new URL(a.href).hostname; return /(^|\.)wa\.me$/.test(h) || /(^|\.)whatsapp\.com$/.test(h); }
    catch (e) { return false; }
  }
  function tag(a) {
    if (a.hasAttribute('data-no-ref') || !isWa(a)) return;
    try {
      var u = new URL(a.href);
      var t = u.searchParams.get('text') || '';
      if (t.indexOf(code) !== -1) return;
      u.searchParams.set('text', (t || 'Hola') + window.mcRefNote);
      a.href = u.toString();
    } catch (e) {}
  }
  document.querySelectorAll('a[href*="wa.me"], a[href*="whatsapp.com"]').forEach(tag);
  // Links whose href is set by script after load are tagged at the moment they are pressed.
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href]');
    if (!a || a.hasAttribute('data-no-ref')) return;
    if (isWa(a)) { tag(a); window.mcRefTrack('whatsapp'); }
    else if (/^mailto:/i.test(a.getAttribute('href') || '')) { window.mcRefTrack('email'); }
  }, true);
})();
</script>
{{-- The maps draw themselves (footer band, /contacto). Deferred; does nothing
     until a map is near the screen. --}}
<script src="{{ asset('js/map-draw.js') }}" defer></script>
@stack('js')
</body>
</html>
