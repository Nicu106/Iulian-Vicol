<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
<meta name="robots" content="noindex, nofollow">
<title>Contacto — IV MOTORCLASS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&display=swap">
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/brandbook.css') }}">
<link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
<link rel="stylesheet" href="{{ asset('css/foot.css') }}">
<link rel="stylesheet" href="{{ asset('css/contact.css') }}">
</head>
<body class="bb cat">

@include('partials.head', ['current' => 'contacto'])

<main class="cat-wrap ct">

  <header class="ct-head">
    <span class="ct-head__eyebrow">Málaga · {{ $sold }} coches entregados</span>
    <h1 class="ct-h1">Escríbeme.<br>Contesto yo.</h1>
    <p class="ct-lead">No hay centralita ni formulario que espere a que alguien lo mire.
      El teléfono es el mío y WhatsApp lo leo en minutos.</p>
  </header>

  {{-- ---- the fast way, first ---------------------------------------------
       Ordered by what people do: 70% of car buyers would rather message than call
       or fill in a form, and 55% expect an answer inside three hours. So the
       message comes first, the call second, and the form last. --}}
  <section class="ct-ways" aria-label="Cómo contactar">
    <a class="ct-way ct-way--wa" href="https://wa.me/{{ $phoneRaw }}">
      <span class="ct-way__k">WhatsApp</span>
      <span class="ct-way__v">{{ $phone }}</span>
      <span class="ct-way__note">Lo leo en minutos. Puedes mandarme fotos y audios.</span>
    </a>
    <a class="ct-way" href="tel:+{{ $phoneRaw }}">
      <span class="ct-way__k">Llamar</span>
      <span class="ct-way__v">{{ $phone }}</span>
      <span class="ct-way__note">Si estoy con un cliente no lo cojo — insiste o escríbeme.</span>
    </a>
    <a class="ct-way" href="mailto:{{ $email }}">
      <span class="ct-way__k">Email</span>
      <span class="ct-way__v">{{ $email }}</span>
      <span class="ct-way__note">Para documentación y facturas.</span>
    </a>
  </section>

  {{-- ---- what the dropdown used to be ------------------------------------
       The old page had a select with six subjects and a message box. A subject
       chosen from a list only helps whoever sorts the inbox; these write the first
       line for you and open the conversation already started. --}}
  <section class="ct-sec" aria-labelledby="ct-why">
    <h2 class="ct-h2" id="ct-why">¿Sobre qué?</h2>
    <p class="ct-sec__p">Elige y te abro la conversación escrita.</p>
    <ul class="ct-reasons">
      @foreach($reasons as $label => $text)
        <li><a class="ct-reason" href="https://wa.me/{{ $phoneRaw }}?text={{ urlencode($text) }}">{{ $label }}</a></li>
      @endforeach
    </ul>
  </section>

  {{-- ---- when ------------------------------------------------------------- --}}
  <section class="ct-sec ct-when" aria-labelledby="ct-when-h">
    <div>
      <h2 class="ct-h2" id="ct-when-h">Cuándo</h2>
      <p class="ct-when__big">Todos los días, <b>a convenir</b></p>
      <p class="ct-sec__p">No tengo horario de oficina. Dime cuándo te viene bien —
        temprano, tarde o fin de semana — y quedamos.</p>
    </div>
    <div>
      <h2 class="ct-h2">Dónde</h2>
      <p class="ct-when__big">Málaga</p>
      <p class="ct-sec__p">Te digo el punto exacto cuando quedemos: depende de dónde
        tenga el coche que quieres ver.</p>
    </div>
  </section>

  {{-- ---- the slow way, last ----------------------------------------------- --}}
  <section class="ct-sec ct-form-sec" aria-labelledby="ct-form-h">
    <h2 class="ct-h2" id="ct-form-h">O déjalo escrito aquí</h2>
    <p class="ct-sec__p">Tres campos. Al enviar, eliges si te abro WhatsApp o el correo
      — en los dos casos el mensaje va ya redactado y lo puedes leer antes de mandarlo.</p>

    <form class="ct-form" id="ct-form">
      <label class="ct-f">
        <span>Tu nombre</span>
        <input class="mc-input" type="text" id="f-name" name="name" autocomplete="name" required>
      </label>
      <label class="ct-f">
        <span>Tu teléfono <em>(opcional)</em></span>
        <input class="mc-input" type="tel" id="f-tel" name="phone" autocomplete="tel" inputmode="tel">
      </label>
      <label class="ct-f ct-f--wide">
        <span>Qué necesitas</span>
        <textarea class="mc-input" id="f-msg" name="message" rows="4" required
                  placeholder="Un coche concreto, una prueba, financiación…"></textarea>
      </label>
      <div class="ct-form__go">
        <button class="mc-btn mc-btn--cta" type="submit" value="wa" name="via">Enviar por WhatsApp</button>
        <button class="mc-btn mc-btn--ghost" type="submit" value="mail" name="via">Enviar por email</button>
      </div>
      <p class="ct-form__note">No guardo nada en esta web: el mensaje se escribe en tu
        WhatsApp o en tu correo y lo envías tú. Así no hay datos míos que proteger ni
        casilla que marcar.</p>
    </form>
  </section>

  {{-- ---- instead of "¿por qué elegirnos?" ---------------------------------
       The old page answered that with four claims — verified vehicles, extended
       warranty, flexible finance, and 24/7 support. Counts he can stand behind
       answer it better, and the last of those four is not carried over: see the
       brandbook's sign-off table. --}}
  <section class="ct-sec ct-facts" aria-label="En números">
    <dl>
      <div><dt>Coches entregados</dt><dd>{{ $sold }}</dd></div>
      <div><dt>Personas fotografiadas con el suyo</dt><dd>{{ $people }}</dd></div>
      <div><dt>Disponibles ahora</dt><dd>{{ $available }}</dd></div>
      <div><dt>Marcas</dt><dd>5</dd></div>
    </dl>
    <p class="ct-sec__p">Las cinco alemanas, y sólo esas.
      <a class="mc-link" href="/catalogo">Ver el catálogo →</a></p>
  </section>
</main>

@include('partials.foot')

<div class="cat-dock" role="complementary" aria-label="Contacto">
  <div class="mc-bar">
    <span class="cat-dock__t">¿Hablamos?<b>Contesto yo</b></span>
    <span class="mc-bar__act">
      <a class="mc-btn mc-btn--cta" href="https://wa.me/{{ $phoneRaw }}">WhatsApp</a>
      <a class="mc-btn mc-btn--ghost" href="tel:+{{ $phoneRaw }}" aria-label="Llamar">Tel</a>
    </span>
  </div>
</div>

<script>
(function () {
  document.documentElement.className += ' js';
  var form = document.getElementById('ct-form');
  if (!form) return;
  var PHONE = @json($phoneRaw), MAIL = @json($email);

  // Which button was pressed decides where it goes. Submit is not cancelled until
  // there is somewhere to send it, so the browser's own required-field checks run
  // first and the page behaves like a form, because it is one.
  var via = 'wa';
  form.querySelectorAll('button[type=submit]').forEach(function (b) {
    b.addEventListener('click', function () { via = b.value; });
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var name = form.querySelector('#f-name').value.trim();
    var tel  = form.querySelector('#f-tel').value.trim();
    var msg  = form.querySelector('#f-msg').value.trim();
    var body = 'Hola' + (name ? ', soy ' + name : '') + '. ' + msg + (tel ? '\n\nMi teléfono: ' + tel : '');
    if (via === 'mail') {
      window.location.href = 'mailto:' + MAIL
        + '?subject=' + encodeURIComponent('Consulta de ' + (name || 'la web'))
        + '&body=' + encodeURIComponent(body);
    } else {
      window.open('https://wa.me/' + PHONE + '?text=' + encodeURIComponent(body), '_blank', 'noopener');
    }
  });
})();
</script>
</body>
</html>
