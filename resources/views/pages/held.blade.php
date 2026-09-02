<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title>IV MOTORCLASS — Brandbook</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@88..100,400..700&display=swap">
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<style>
  *,*::before,*::after{ box-sizing:border-box; border-radius:0; }
  body{ margin:0; min-height:100svh; display:flex; align-items:center;
        font-family:var(--f-ui); background:var(--mc-navy); color:var(--mc-on-navy-2); }
  .h{ max-width:var(--mc-container); margin:0 auto; padding:var(--s-7) var(--mc-gutter-m); }
  @media (min-width:768px){ .h{ padding-inline:var(--mc-gutter-d); } }
  .l{ font-size:var(--t-label); letter-spacing:.01em; color:var(--mc-on-navy-2); }
  h1{ margin:var(--s-3) 0 var(--s-5); font-size:var(--t-display); line-height:1.04;
      font-stretch:92%; letter-spacing:-.035em; font-weight:600; color:var(--mc-on-navy); }
  p{ margin:0 0 var(--s-5); max-width:52ch; font-size:var(--t-body); line-height:1.55; }
  a.btn{ display:inline-flex; align-items:center; justify-content:center;
         min-height:var(--mc-tap-pref); padding:var(--s-3) var(--s-5);
         font-size:var(--t-ui); font-weight:500; text-decoration:none;
         background:var(--mc-surface); color:var(--mc-navy);
         border:1px solid var(--mc-surface);
         transition:background var(--m-quick) var(--e-out), color var(--m-quick) var(--e-out);
         touch-action:manipulation; -webkit-tap-highlight-color:transparent; }
  a.btn:hover{ background:var(--mc-blue-light); border-color:var(--mc-blue-light); }
  a.btn:focus-visible{ outline:2px solid var(--mc-on-navy); outline-offset:2px; }
  .r{ margin-top:var(--s-7); padding-top:var(--s-5);
      border-top:1px solid var(--mc-navy-line); font-size:var(--t-small); }
</style>
</head>
<body>
  <div class="h">
    <span class="l">IV MOTORCLASS · entorno de diseño</span>
    <h1>Aquí sólo está el brandbook</h1>
    <p>Este servidor es la copia de diseño, no la web pública. Ahora mismo sirve
       únicamente el brandbook; el resto de páginas todavía se están construyendo.</p>
    <p><a class="btn" href="/brandbook">Abrir el brandbook</a></p>
    <p class="r">¿Buscabas la web?
       <a href="https://ivmotorclass.com" style="color:var(--mc-blue-light)">ivmotorclass.com</a></p>
  </div>
</body>
</html>
