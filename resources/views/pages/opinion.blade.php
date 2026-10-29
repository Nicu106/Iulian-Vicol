@extends('layouts.site')

@section('title', 'Tu opinión — IV MOTORCLASS')
@section('robots', 'noindex, nofollow')
@section('current', '')

{{-- The token is in this address: it goes to no other site in a Referer. --}}
@push('meta')
<meta name="referrer" content="same-origin">
@endpush

@push('css')
<link rel="stylesheet" href="{{ asset('css/contact.css') }}">
<link rel="stylesheet" href="{{ asset('css/sell.css') }}">
<link rel="stylesheet" href="{{ asset('css/opinion.css') }}">
@endpush

@section('content')
{{-- /opinion/{token}: the customer writes their own opinion through the link
     the owner sent (OpinionController — read its docblock). The fields, the
     photo zone and the "sent" state are /vende's and /recomienda's, so every
     page that asks a visitor to write is one voice. --}}
@php $wa = 'https://wa.me/34614753187?text=' . rawurlencode('Hola, es sobre mi opinión para la web.'); @endphp
<main class="sl op">

@if($state === 'form')
  <section class="sl-open ct-grid" aria-labelledby="op-h">
    <div class="sl-open__say">
      <h1 class="ct-open__h op-h" id="op-h">{{ $name ? $name . ', cuéntame' : 'Cuéntame' }} qué&nbsp;tal</h1>
      <p class="ct-say sl-open__lead">Lo que escribas sale en la web, con tu nombre y tu foto,
        junto a las opiniones de otros clientes. Lo leo yo antes de publicarlo.</p>
    </div>
  </section>

  <div class="sl-body cat-wrap">
    <form class="op-form" method="post" action="{{ route('opinion.store', $link->token) }}"
          enctype="multipart/form-data" novalidate data-stat-form="opinion">
      @csrf

      <label class="mc-field">
        <span class="mc-field__label">Tu nombre, como quieres que salga</span>
        <input class="mc-input {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name"
               value="{{ old('name', $name) }}" required maxlength="80" autocomplete="name" autocapitalize="words" enterkeyhint="next">
        @error('name')<span class="mc-err">{{ $message }}</span>@enderror
      </label>

      <label class="mc-field">
        <span class="mc-field__label">Tu opinión</span>
        <textarea class="mc-textarea op-text {{ $errors->has('quote') ? 'is-invalid' : '' }}" name="quote" rows="6"
                  required minlength="20" maxlength="1000" autocapitalize="sentences">{{ old('quote') }}</textarea>
        @error('quote')<span class="mc-err">{{ $message }}</span>@enderror
      </label>

      {{-- One photograph, shown whole as it will be on the site. The explicit
           types make an iPhone send a JPEG rather than its HEIC. --}}
      <div class="mc-field">
        <span class="mc-field__label">Una foto <span class="mc-field__opt">— si quieres, con el coche</span></span>
        <div class="sl-drop op-drop" id="op-drop">
          <label class="sl-drop__label">
            <input class="sl-drop__in" type="file" name="photo" id="op-file" accept="image/jpeg,image/png,image/webp">
            <span class="sl-drop__big" id="op-pick">Elegir una foto</span>
            <span class="sl-drop__small" id="op-small">Sale entera, sin recortar</span>
          </label>
          <figure class="op-prev" id="op-prev" hidden><img id="op-prev-i" alt="La foto que has elegido"></figure>
        </div>
        @error('photo')<p class="mc-err">{{ $message }}</p>@enderror
      </div>

      <label class="mc-check op-ok">
        <input type="checkbox" name="consent" value="1" required @checked(old('consent'))>
        <span class="mc-check__t">Acepto que mi nombre, mi opinión y mi foto se publiquen en
          ivmotorclass.com. Puedo pedir que se quiten cuando quiera
          (<a href="/privacidad" target="_blank" rel="noopener">privacidad</a>).</span>
      </label>
      @error('consent')<p class="mc-err">{{ $message }}</p>@enderror

      <div class="sl-send op-send">
        <button class="mc-btn sl-send__btn" type="submit">Enviar mi opinión</button>
      </div>
    </form>
  </div>

@else
  {{-- A state of the page, not a bar over the form: it survives a refresh. --}}
  <section class="sl-done ct-grid" aria-labelledby="op-h">
    <div class="sl-done__in">
      @if($state === 'gracias')
        <h1 class="ct-open__h" id="op-h">Gracias{{ $name ? ', ' . $name : '' }}.</h1>
        <p class="ct-say">La leo y la publico en unos días, junto a las demás.</p>
        <p class="sl-done__note">Si quieres cambiar algo, escríbeme por WhatsApp.</p>
      @elseif($state === 'recibida')
        <h1 class="ct-open__h" id="op-h">Tu opinión ya me&nbsp;llegó.</h1>
        <p class="ct-say">Gracias. Si quieres cambiar algo, escríbeme por WhatsApp.</p>
      @else
        <h1 class="ct-open__h" id="op-h">Este enlace ya está&nbsp;cerrado.</h1>
        <p class="ct-say">Si quieres dejar tu opinión, escríbeme por WhatsApp y te mando otro.</p>
      @endif
      <p class="sl-done__go">
        <a class="mc-btn mc-btn--cta" href="{{ $wa }}">Escribir por WhatsApp</a>
        <a class="mc-link sl-done__again" href="/catalogo">Ver los coches</a>
      </p>
    </div>
  </section>
@endif

</main>
@endsection

@push('js')
@if($state === 'form')
<script>
(function () {
  var input = document.getElementById('op-file'), drop = document.getElementById('op-drop');
  var prev = document.getElementById('op-prev'), img = document.getElementById('op-prev-i');
  var pick = document.getElementById('op-pick'), small = document.getElementById('op-small');
  var shown = null;
  input.addEventListener('change', function () {
    var f = input.files && input.files[0];
    if (shown) { URL.revokeObjectURL(shown); shown = null; }
    if (!f) { prev.hidden = true; drop.classList.remove('has-files'); pick.textContent = 'Elegir una foto'; return; }
    shown = URL.createObjectURL(f);
    img.src = shown; prev.hidden = false;
    drop.classList.add('has-files');
    pick.textContent = 'Cambiar la foto';
    small.textContent = f.size > {{ $maxKb }} * 1024 ? 'Pesa demasiado: como mucho 12 MB.' : 'Así saldrá: entera.';
  });
  // one press: the button does not send twice while a photo is going up
  document.querySelector('.op-form').addEventListener('submit', function (e) {
    var b = e.target.querySelector('[type=submit]');
    if (b.disabled) { e.preventDefault(); return; }
    setTimeout(function () { b.disabled = true; b.textContent = 'Enviando…'; }, 0);
  });
})();
</script>
@endif
@endpush
