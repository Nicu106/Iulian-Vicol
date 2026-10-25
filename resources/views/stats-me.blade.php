{{-- "No contar este dispositivo" (StatsMeController). The same gate as the
     sign-in page: one card, a sentence, one button. --}}
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title>IV MOTORCLASS — Estadísticas</title>
<link rel="preload" href="/fonts/dm-sans/dm-sans-latin-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/panel.css') }}">
</head>
<body class="ad">
<main class="ad-gate">
  <div class="ad-gate__in">
    <a class="ad-gate__mark" href="{{ route('home') }}">
      <b>IV MOTORCLASS</b>
      <span>Estadísticas</span>
    </a>
    <div class="ad-gate__card ad-form" style="gap:var(--s-4)">
      @if($on)
        <p class="ad-me__t">Este dispositivo ya no cuenta</p>
        <p class="ad-me__p">Lo que mires desde este navegador no se suma a las estadísticas de la web, aunque no
          entres en el panel. Abre el mismo enlace en cada móvil, ordenador y navegador que uséis.</p>
        <a class="ad-btn ad-btn--q" href="{{ $undo }}" style="width:100%">Volver a contarlo</a>
      @else
        <p class="ad-me__t">Este dispositivo vuelve a contar</p>
        <p class="ad-me__p">Sus visitas se suman otra vez a las estadísticas.</p>
        <a class="ad-btn ad-btn--q" href="{{ $again }}" style="width:100%">No contarlo</a>
      @endif
      <a class="ad-btn" href="{{ route('home') }}" style="width:100%">Ir a la web</a>
    </div>
  </div>
</main>
</body>
</html>
