{{-- 413, from PublicFormLimits, before PHP has parsed a byte of the upload. --}}
<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Demasiado grande — IV MOTORCLASS</title>
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/brandbook.css') }}">
<style>body{background:var(--mc-bg);font-family:var(--f-ui);margin:0}
.b{max-width:36rem;margin:0 auto;padding:var(--s-10) var(--mc-gutter-m)}
h1{margin:0 0 var(--s-4);font-size:var(--t-h2);letter-spacing:-.02em;color:var(--mc-ink)}
p{margin:0 0 var(--s-5);font-size:var(--t-prose);line-height:1.6;color:var(--mc-ink-2)}</style>
</head>
<body><div class="b">
<h1>Son demasiadas fotos de golpe</h1>
<p>El envío pasa de {{ $mb }} MB. Manda menos fotos, o las mismas en dos veces —
   con seis se ve un coche perfectamente.</p>
<a class="mc-btn" href="/vende">Volver</a>
</div></body></html>
