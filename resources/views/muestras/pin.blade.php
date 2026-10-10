<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>IV MOTORCLASS</title>
<link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
<style>
  html,body{margin:0;height:100%;background:#05080F;color:#fff;font-family:"DM Sans",system-ui,sans-serif}
  main{min-height:100%;display:grid;place-items:center;padding:24px;box-sizing:border-box}
  form{display:grid;gap:20px;justify-items:center;text-align:center}
  .m{font-weight:700;letter-spacing:.02em;font-size:15px;color:#A9B5C6}
  .pin{display:flex;gap:12px}
  .pin input{width:56px;height:68px;border:1px solid rgba(255,255,255,.22);border-radius:12px;background:rgba(255,255,255,.04);
    color:#fff;font:700 28px/1 "DM Sans",system-ui,sans-serif;text-align:center;caret-color:#fff;outline:none}
  .pin input:focus{border-color:#fff;background:rgba(255,255,255,.08)}
  .err{color:#FFB4B4;font-size:15px;min-height:20px;margin:0}
  .wrong .pin{animation:sh .38s cubic-bezier(.36,.07,.19,.97)}
  @keyframes sh{20%,60%{transform:translateX(-6px)}40%,80%{transform:translateX(6px)}}
  @media (prefers-reduced-motion:reduce){.wrong .pin{animation:none}}
  button{position:absolute;left:-9999px}
</style>
</head>
<body>
<main>
  <form method="post" action="/muestras/pin" class="{{ $wrong ? 'wrong' : '' }}" autocomplete="off">
    @csrf
    <input type="hidden" name="back" value="{{ $back }}">
    <input type="hidden" name="pin" id="pin">
    <div class="m">IV MOTORCLASS</div>
    <div class="pin" role="group" aria-label="4-digit code">
      @for($i = 0; $i < 4; $i++)
        <input inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-label="Digit {{ $i + 1 }}" @if($i === 0) autofocus @endif>
      @endfor
    </div>
    <p class="err" aria-live="polite">{{ $wrong ? 'Wrong code' : '' }}</p>
    <button type="submit">Enter</button>
  </form>
</main>
<script>
(function () {
  var f = document.querySelector('form'), box = [].slice.call(document.querySelectorAll('.pin input')), pin = document.getElementById('pin');
  function go() { var v = box.map(function (b) { return b.value; }).join(''); if (/^\d{4}$/.test(v)) { pin.value = v; f.submit(); } }
  box.forEach(function (b, i) {
    b.addEventListener('input', function () {
      var d = b.value.replace(/\D/g, '');
      if (d.length > 1) { d.split('').slice(0, 4 - i).forEach(function (c, k) { box[i + k].value = c; }); }
      else b.value = d;
      var next = box.find(function (x) { return !x.value; });
      (next || box[3]).focus(); go();
    });
    b.addEventListener('keydown', function (e) { if (e.key === 'Backspace' && !b.value && i > 0) { box[i - 1].value = ''; box[i - 1].focus(); } });
  });
})();
</script>
</body>
</html>
