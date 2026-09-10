{{--
  The way in.

  It was Bootstrap from a CDN, Figtree from fonts.bunny.net — a different family
  from the entire rest of the site — a radial-gradient body, a 16px card radius
  and a 60px soft shadow. The first screen the owner sees, in a typeface the
  site does not use, wearing three of the defaults the design contract forbids.

  Now it is the same language as everything else: mc-tokens, DM Sans, the navy,
  square corners. No Bootstrap, no Vite, no second font.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex, nofollow">
<title>IV MOTORCLASS — Acceso</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&display=swap">
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/ad.css') }}">
</head>
<body class="ad">

<main class="ad-gate">
  <div class="ad-gate__in">
    <a class="ad-gate__mark" href="{{ route('inicio') }}">
      <b>IV MOTORCLASS</b>
      <span>Acceso administrador</span>
    </a>

    <div class="ad-gate__card">
      {{ $slot }}
    </div>
  </div>
</main>

<script>
  // Keyboard chord on the gate: Ctrl + A + D goes straight to the panel.
  (function () {
    let ctrlHeld = false, lastA = 0, lastD = 0; const CHORD_MS = 700;
    window.addEventListener('keydown', function (e) {
      if (e.key === 'Control') { ctrlHeld = true; return; }
      if (!e.ctrlKey && !ctrlHeld) return;
      const k = (e.key || '').toLowerCase(), now = Date.now();
      if (k === 'a') lastA = now;
      if (k === 'd') lastD = now;
      if (lastA && lastD && Math.abs(lastA - lastD) <= CHORD_MS) {
        e.preventDefault(); window.location.assign('{{ route('admin.home') }}'); lastA = lastD = 0;
      }
    }, true);
    window.addEventListener('keyup', function (e) { if (e.key === 'Control') ctrlHeld = false; }, true);
  })();
</script>
</body>
</html>
