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
<meta property="og:type" content="@yield('og_type', 'website')">
<meta property="og:site_name" content="IV MOTORCLASS">
<meta property="og:locale" content="es_ES">
{{-- og_title: what a shared link (WhatsApp, Facebook) shows, when a page wants it
     shorter than the title search engines read. Defaults to the title. --}}
<meta property="og:title" content="{{ trim($__env->yieldContent('og_title')) ?: trim($__env->yieldContent('title')) }}">
<meta property="og:description" content="{{ $metaDesc }}">
<meta property="og:url" content="{{ $metaUrl }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
@stack('meta')
{{-- What search engines and answer engines read: the dealer, the site, this page
     and whatever the page adds (a car, the list of cars, its questions) — one
     JSON-LD graph, App\Support\Schema (docblock first). Not on a page kept out
     of the index. --}}
@unless(trim($__env->yieldContent('robots')))
<script type="application/ld+json">{!! \App\Support\Schema::render(trim($__env->yieldContent('title')), $metaDesc, $metaUrl, $metaImage) !!}</script>
@endunless
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
     One beacon when the page is seen (not while Chrome prerenders it; again when
     Back brings it out of the back-forward cache); one when it is hidden or left,
     with the seconds it was on screen, how far down it was read and the
     photographs — which ones were on screen, for how long, which were opened full
     screen; one per press on WhatsApp, the phone, e-mail, the map, or any element
     marked data-stat="name" (data-stat-k="bmw" says what about). A form marked
     data-stat-form="name" says when it is started and when it is sent, never what
     is in it. data-stat-seen="name" is said once when half of it has been on screen
     for a second; data-stat-scroll="name" once when it is first scrolled sideways.
     Silent in automated browsers (navigator.webdriver). --}}
<script>
(function () {
  if (navigator.webdriver) { window.mcStat = window.mcStatPic = window.mcStatPicView = function () {}; return; }
  var path = location.pathname, beacon = navigator.sendBeacon && navigator.sendBeacon.bind(navigator);
  function send(d) {
    if (!beacon) return;
    d.p = path;
    try { beacon('/s', new URLSearchParams(d)); } catch (e) {}
  }
  window.mcStat = function (t, name, k) {
    var d = name ? { t: 'ev', e: name } : { t: t };
    if (k) d.k = k;
    send(d);
  };

  // Seen: now, or when a prerendered page is actually shown.
  var search = location.search;
  function viewed() { send({ t: 'view', q: search, r: document.referrer }); }
  if (document.prerendering) document.addEventListener('prerenderingchange', viewed, { once: true });
  else viewed();

  // The bonnet placard's QR opens /coche/…?c=qr: counted above, then taken off
  // the address so a link shared from here is not another "scan".
  if (/[?&]c=qr(&|$)/.test(search) && history.replaceState) {
    try {
      var u = new URL(location.href); u.searchParams.delete('c');
      history.replaceState(history.state, '', u.pathname + u.search + u.hash);
      search = u.search;
    } catch (e) {}
  }

  // Time on screen, depth, photographs. Sent as differences, so a page hidden
  // and shown again many times still adds up once.
  var on, ms, sent, y, ySent, first, tick = false;
  var pic, picBig, viewer, picOn, dwell, dwellSent, seenK, seenSent, bigK, bigSent, galVis = false;
  function reset() {
    on = document.visibilityState === 'visible' ? Date.now() : 0; ms = 0; sent = 0;
    y = 0; ySent = 0; first = true;
    pic = null; picBig = false; viewer = false; picOn = 0;
    dwell = {}; dwellSent = {}; seenK = {}; seenSent = {}; bigK = {}; bigSent = {};
  }
  reset();
  function depth() {
    tick = false;
    var h = document.documentElement.scrollHeight, v = innerHeight + (scrollY || pageYOffset || 0);
    var pct = h > 0 ? Math.round(100 * v / h) : 100;
    if (pct >= 97) pct = 100;
    if (pct > y) y = pct;
  }
  addEventListener('scroll', function () { if (!tick) { tick = true; requestAnimationFrame(depth); } }, { passive: true });
  addEventListener('load', depth);

  // A photograph counts while it is the one on show — on the page's gallery
  // while half of the gallery is on screen, or in the full-screen viewer — and
  // the page is visible. Seen = 0.7 s or more; a swipe flying past is not.
  function picRunning() { return document.visibilityState === 'visible' && pic && (viewer ? picBig : galVis); }
  function picStop() {
    if (!picOn) return;
    dwell[pic] = (dwell[pic] || 0) + (Date.now() - picOn); picOn = 0;
    if (dwell[pic] >= 700) seenK[pic] = 1;
  }
  function picStart() { if (!picOn && picRunning()) picOn = Date.now(); }
  window.mcStatPic = function (k, big) {
    if (!/^[a-f0-9]{8}$/.test(k || '')) return;
    if (k === pic && !!big === picBig) return;
    picStop(); pic = k; picBig = !!big; if (big) bigK[k] = 1; picStart();
  };
  window.mcStatPicView = function (open) { picStop(); viewer = !!open; picStart(); };
  function watch(sel, fn) {
    if (!window.IntersectionObserver) return;
    document.querySelectorAll(sel).forEach(function (el) {
      new IntersectionObserver(function (es) { es.forEach(function (e) { fn(el, e.isIntersecting && e.intersectionRatio >= 0.5); }); },
        { threshold: [0, 0.5, 1] }).observe(el);
    });
  }
  watch('[data-stat-gallery]', function (el, vis) { picStop(); galVis = vis; picStart(); });

  function list(o, done) {
    var out = [];
    for (var k in o) if (o[k] && !done[k]) { out.push(k); done[k] = 1; }
    return out.slice(0, 80).join(',');
  }
  function flush() {
    if (on) { ms += Date.now() - on; on = 0; }
    picStop();
    var total = Math.min(1800, Math.round(ms / 1000)), s = total - sent, b = Math.min(100, Math.floor(y / 25) * 25);
    var pt = [];
    for (var k in dwell) {
      var d = Math.round((dwell[k] - (dwellSent[k] || 0)) / 1000);
      if (d > 0) { pt.push(k + ':' + d); dwellSent[k] = (dwellSent[k] || 0) + d * 1000; }
    }
    var pv = list(seenK, seenSent), pz = list(bigK, bigSent);
    if (!first && s <= 0 && b <= ySent && !pv && !pz && !pt.length) return;
    var data = { t: 'eng', s: Math.max(0, s), y: b, y0: ySent, f: first ? '1' : '0' };
    if (pv) data.pv = pv;
    if (pz) data.pz = pz;
    if (pt.length) data.pt = pt.slice(0, 80).join(',');
    send(data);
    sent = total; ySent = b; first = false;
  }
  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'hidden') flush(); else { on = Date.now(); picStart(); }
  });
  addEventListener('pagehide', flush);
  // Back to a page kept whole by the browser: it is seen again, as a new page view.
  addEventListener('pageshow', function (e) {
    if (!e.persisted) return;
    reset(); depth(); viewed();
  });

  // Presses. Capture, so a link that leaves the page is counted before it goes.
  // A control that is already open (aria-expanded="true") is being closed: not counted.
  document.addEventListener('click', function (e) {
    var el = e.target.closest && e.target.closest('a[href], [data-stat]');
    if (!el) return;
    var name = el.getAttribute('data-stat'), k = el.getAttribute('data-stat-k') || '';
    if (name && el.getAttribute('aria-expanded') !== 'true') window.mcStat('ev', name, k);
    if (el.tagName !== 'A') return;
    var href = el.getAttribute('href') || '', host = '';
    try { host = new URL(el.href).hostname; } catch (x) {}
    if (/(^|\.)wa\.me$|(^|\.)whatsapp\.com$/.test(host)) window.mcStat('wa', '', k);
    else if (/^tel:/i.test(href)) window.mcStat('tel', '', k);
    else if (/^mailto:/i.test(href)) window.mcStat('mail', '', k);
    else if (!name && (/(^|\.)google\.[a-z.]+$/.test(host) && /\/maps/.test(el.href) || /^maps\.google\./.test(host))) window.mcStat('ev', 'mapa');
  }, true);

  // A full-screen viewer and the like: counted when it opens.
  document.querySelectorAll('[data-stat-open]').forEach(function (el) {
    new MutationObserver(function () {
      if (!el.hidden) window.mcStat('ev', el.getAttribute('data-stat-open'));
    }).observe(el, { attributes: true, attributeFilter: ['hidden'] });
  });

  // Seen (half on screen for a second) and scrolled sideways: once per page view each.
  var shown = {};
  watch('[data-stat-seen]', function (el, vis) {
    var id = el.getAttribute('data-stat-seen') + '|' + (el.getAttribute('data-stat-k') || '');
    if (shown[id]) return;
    clearTimeout(el.mcStatT);
    if (vis) el.mcStatT = setTimeout(function () {
      if (shown[id] || document.visibilityState !== 'visible') return;
      shown[id] = 1; window.mcStat('ev', el.getAttribute('data-stat-seen'), el.getAttribute('data-stat-k') || '');
    }, 1000);
  });
  document.querySelectorAll('[data-stat-scroll]').forEach(function (el) {
    var x0 = el.scrollLeft;
    el.addEventListener('scroll', function () {
      if (el.mcStatS || Math.abs(el.scrollLeft - x0) < 40) return;
      el.mcStatS = 1; window.mcStat('ev', el.getAttribute('data-stat-scroll'), el.getAttribute('data-stat-k') || '');
    }, { passive: true });
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
