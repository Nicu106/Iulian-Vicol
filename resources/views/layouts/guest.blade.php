<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>IV MOTORCLASS • Acceso</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
          body { background: radial-gradient(1200px 600px at 10% -10%, rgba(13,110,253,.08), transparent), #0b1220; }
          .auth-card { border-radius: 16px; background: rgba(255,255,255,0.98); box-shadow: 0 20px 60px rgba(0,0,0,.35); }
          .brand-title { color: #fff; font-weight: 800; letter-spacing: .15rem; }
          .brand-sub { color: #9ca3af; font-size: .9rem; }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center py-5">
            <a href="/" class="text-decoration-none mb-3 text-center">
              <div class="brand-title h3 mb-1">IV MOTORCLASS</div>
              <div class="brand-sub">Acceso administrador</div>
            </a>
            <div class="w-100" style="max-width: 440px;">
              <div class="auth-card p-4 p-md-5">
                {{ $slot }}
              </div>
            </div>
        </div>
    </body>
    <script>
      // Keyboard chord on login/guest pages: Ctrl + A + D → admin
      (function(){
        let ctrlHeld = false; let lastA = 0, lastD = 0; const CHORD_MS = 700;
        window.addEventListener('keydown', function(e){
          if (e.key === 'Control') { ctrlHeld = true; return; }
          if (!e.ctrlKey && !ctrlHeld) return;
          const k = (e.key||'').toLowerCase(); const now = Date.now();
          if (k === 'a') lastA = now; if (k === 'd') lastD = now;
          if (lastA && lastD && Math.abs(lastA - lastD) <= CHORD_MS) {
            e.preventDefault(); window.location.assign('{{ route('admin.home') }}'); lastA = lastD = 0;
          }
        }, true);
        window.addEventListener('keyup', function(e){ if (e.key === 'Control') ctrlHeld = false; }, true);
      })();
    </script>
</html>
