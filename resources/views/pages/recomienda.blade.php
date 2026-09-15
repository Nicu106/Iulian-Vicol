@extends('layouts.site')

@section('title', 'Recomienda a un amigo — IV MOTORCLASS')
@section('current', 'recomienda')

@push('css')
<link rel="stylesheet" href="{{ asset('css/contact.css') }}">
<link rel="stylesheet" href="{{ asset('css/sell.css') }}">
<link rel="stylesheet" href="{{ asset('css/refer.css') }}">
@endpush

@section('content')
{{-- Recommendations without accounts. Two questions — the person's own name
     and phone — and back comes a personal link to send by WhatsApp. The
     friend's data is never asked for. Rules: App\Support\Referral. --}}
<main class="sl rf">

  <section class="sl-open ct-grid" aria-labelledby="rf-h">
    <div class="sl-open__say">
      <p class="ct-open__kick">Recomienda</p>
      <h1 class="ct-open__h rf-h" id="rf-h">¿Conoces a alguien que busca&nbsp;coche?</h1>
      <p class="ct-say sl-open__lead">Te doy un enlace tuyo. Mándaselo por WhatsApp y, si
        compra un coche, sabré que vino de tu parte.@if($reward) {{ $reward }}.@endif</p>
    </div>
  </section>

  <div class="sl-body cat-wrap">
    <div class="rf-body">

      @if($mine)
        <section class="rf-mine" id="tu-enlace" aria-labelledby="rf-mine-h">
          <h2 class="ct-h2" id="rf-mine-h">Tu enlace</h2>
          <p class="rf-note">Es siempre el mismo. Si lo pides otra vez con tu teléfono, te doy este.</p>

          {{-- Shown whole, as text. A read-only input clipped it at 390 to
               ".../r/LUCIACF" — the one thing on this page that has to be read
               complete. --}}
          <p class="rf-link" id="rf-link">{{ $mine->link }}</p>

          <div class="rf-go">
            {{-- data-no-ref: this is YOUR link going out, and must not carry
                 someone else's code if you once opened theirs. --}}
            <a class="mc-btn mc-btn--cta" data-no-ref target="_blank" rel="noopener"
               href="https://wa.me/?text={{ rawurlencode('Te recomiendo IV MOTORCLASS, coches alemanes en Málaga. Mira sus coches aquí: ' . $mine->link) }}">Enviar por WhatsApp</a>
            <button class="rf-copy" type="button" data-copy="{{ $mine->link }}">Copiar enlace</button>
          </div>
          <p class="rf-note" id="rf-said" role="status" aria-live="polite"></p>
        </section>
      @else
        <form class="rf-form" method="post" action="{{ route('refer.store') }}" novalidate>
          @csrf

          {{-- The trap and the clock, as on /vende. --}}
          <div class="sl-hp" aria-hidden="true">
            <label for="apellido_2">No rellenes esto</label>
            <input type="text" id="apellido_2" name="apellido_2" tabindex="-1" autocomplete="off" value="">
          </div>
          <input type="hidden" name="t" value="{{ $stamp }}">

          <label class="mc-field">
            <span class="mc-field__label">Tu nombre</span>
            <input class="mc-input {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name"
                   value="{{ old('name') }}" required maxlength="80" autocomplete="given-name">
            @error('name')<span class="mc-err">{{ $message }}</span>@enderror
          </label>

          <label class="mc-field">
            <span class="mc-field__label">Tu teléfono</span>
            <input class="mc-input {{ $errors->has('phone') ? 'is-invalid' : '' }}" type="tel" name="phone"
                   value="{{ old('phone') }}" required maxlength="30" inputmode="tel" autocomplete="tel">
            @error('phone')<span class="mc-err">{{ $message }}</span>@enderror
          </label>

          <div class="sl-send">
            <button class="mc-btn sl-send__btn" type="submit">Crear mi enlace</button>
            <p class="sl-send__note">Sólo tus datos. No te pido los de tu amigo: él decide si me escribe.</p>
          </div>
        </form>
      @endif

    </div>
  </div>
</main>
@endsection

@push('js')
<script>
(function () {
  var b = document.querySelector('[data-copy]');
  if (!b) return;
  var said = document.getElementById('rf-said');
  b.addEventListener('click', function () {
    var v = b.getAttribute('data-copy');
    var done = function () { said.textContent = 'Enlace copiado.'; };
    var pick = function () {
      var r = document.createRange(); r.selectNodeContents(document.getElementById('rf-link'));
      var s = window.getSelection(); s.removeAllRanges(); s.addRange(r);
      said.textContent = 'Enlace seleccionado: mantén pulsado para copiarlo.';
    };
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(v).then(done, pick);
    } else {
      pick();
    }
  });
})();
</script>
@endpush
