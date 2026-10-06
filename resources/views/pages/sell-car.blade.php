@extends('layouts.site')

@section('title', 'Vende tu coche — IV MOTORCLASS')
@section('description', 'Vende tu coche alemán en Málaga a quien lo va a vender: dime marca, modelo y kilómetros, y te digo qué puedo hacer.')
@section('current', 'vender')

@push('css')
<link rel="stylesheet" href="{{ asset('css/contact.css') }}">
<link rel="stylesheet" href="{{ asset('css/sell.css') }}">
@endpush

@section('content')
<main class="sl">

@if($sent)
  {{-- ================================================================
       Sent. A state of the page, not a green bar over the form: what happens
       next is the content now, and it has to survive a refresh.
       ================================================================ --}}
  <section class="sl-done ct-grid" aria-labelledby="done-h">
    <div class="sl-done__in">
      <p class="ct-open__kick">Recibido</p>
      <h1 class="ct-open__h" id="done-h">Lo tengo.<br>Te escribo&nbsp;yo.</h1>
      <p class="ct-say">Miro las fotos, te llamo o te escribo por WhatsApp, y si
        el coche me encaja quedamos para verlo. Normalmente el mismo día.</p>
      <p class="sl-done__note">No te llegará nada automático: cuando recibas un
        mensaje, seré yo.</p>
      <p class="sl-done__go">
        <a class="mc-btn" href="/catalogo">Ver lo que tengo ahora</a>
        <a class="mc-link sl-done__again" href="/vende">Enviar otro coche</a>
      </p>
    </div>
  </section>
@else

  {{-- ================================================================
       The opening IS the first question. He works with five marques and no
       others — that is the one hard constraint on this page, so it is said
       before anything is typed, in the catalogue's own colours. A Renault
       owner learns it here, not after seventeen fields.
       ================================================================ --}}
  <section class="sl-open ct-grid" aria-labelledby="sl-h">
    <div class="sl-open__say">
      <p class="ct-open__kick">Málaga · compro coches alemanes</p>
      <h1 class="ct-open__h" id="sl-h">Vende tu coche<br>a quien lo va a&nbsp;vender.</h1>
      <p class="ct-say sl-open__lead">Trabajo con cinco marcas. Si la tuya es una
        de ellas, dime cuál y te digo qué puedo hacer.</p>
    </div>
  </section>

  <form class="sl-form" method="post" action="{{ route('sell-car.store') }}" enctype="multipart/form-data" novalidate id="sl-form">
    @csrf
    {{-- Two traps, neither of which asks the visitor anything.

         The field: no person sees it, tabs to it, or is offered it by a password
         manager. A bot reads the HTML and fills what it finds. Not display:none —
         that is the first thing a modern bot checks for — but pushed out of the
         viewport, hidden from the accessibility tree, and skipped by the keyboard.

         The clock: an encrypted timestamp, so it cannot be back-dated. Under four
         seconds nobody has read the page, chosen a marque and typed a phone
         number. A person filling this as fast as they can takes about 25.

         A CAPTCHA was the other option and was not taken: it charges every honest
         seller — the ones with the worst eyesight and the oldest phones most of
         all — for the few who are not. --}}
    <div class="sl-hp" aria-hidden="true">
      <label for="hp_note">No rellenes esto</label>
      <input type="text" id="hp_note" name="hp_note" tabindex="-1" autocomplete="off" value="">
    </div>
    <input type="hidden" name="t" value="{{ $stamp }}">

    @if($errors->any())
      <div class="sl-alert cat-wrap" role="alert" tabindex="-1" id="sl-alert">
        <p class="sl-alert__h">No he podido guardarlo.</p>
        <ul class="sl-alert__l">
          @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
        <p class="sl-alert__note">El resto lo he guardado. Las fotos no se pueden conservar: vuelve a elegirlas.</p>
      </div>
    @endif

    {{-- ---- 1 · la marca ------------------------------------------ --}}
    <fieldset class="sl-marques" aria-labelledby="marca-h">
      <legend class="mc-vh" id="marca-h">Marca</legend>
      <div class="sl-marques__row">
        @foreach($marques as $m)
          <label class="sl-marque" style="--brand:{{ $m['colour'] }}">
            <input class="sl-marque__in mc-vh" type="radio" name="brand" value="{{ $m['key'] }}"
                   {{ old('brand') === $m['key'] ? 'checked' : '' }} required>
            <span class="sl-marque__logo" style="--logo:url('{{ asset('img/marques/'.$m['key'].'.svg') }}')" aria-hidden="true"></span>
            <span class="sl-marque__name">{{ $m['name'] }}</span>
          </label>
        @endforeach
        <label class="sl-marque sl-marque--other">
          <input class="sl-marque__in mc-vh" type="radio" name="brand" value="otra" {{ old('brand') === 'otra' ? 'checked' : '' }}>
          <span class="sl-marque__name">Otra marca</span>
          <span class="sl-marque__sub">Normalmente no, pero pregunta.</span>
        </label>
      </div>
      @error('brand')<p class="mc-err">{{ $message }}</p>@enderror
      <div class="sl-other" id="sl-other" hidden>
        <label class="mc-field">
          <span class="mc-field__label">¿Qué marca?</span>
          <input class="mc-input" type="text" name="brand_other" value="{{ old('brand_other') }}" maxlength="60" autocomplete="off">
        </label>
        @error('brand_other')<p class="mc-err">{{ $message }}</p>@enderror
      </div>
    </fieldset>

    <div class="sl-body cat-wrap">

      {{-- ---- 2 · el coche ------------------------------------------ --}}
      <section class="sl-sec" aria-labelledby="coche-h">
        <div class="sl-sec__head">
          <span class="sl-sec__n" aria-hidden="true">1</span>
          <h2 class="ct-h2" id="coche-h">El coche</h2>
          <p class="sl-sec__p">Lo que ya sabes. Lo demás lo miro yo.</p>
        </div>
        <div class="sl-sec__body">
        <div class="sl-grid">
          <label class="mc-field sl-span2">
            <span class="mc-field__label">Modelo</span>
            <input class="mc-input" type="text" name="model" value="{{ old('model') }}" placeholder="Golf, A4, Serie 3, Clase C…" required maxlength="100" autocomplete="off">
            @error('model')<span class="mc-err">{{ $message }}</span>@enderror
          </label>
          <label class="mc-field">
            <span class="mc-field__label">Año</span>
            <select class="mc-input sl-select" name="year" required>
              <option value="">—</option>
              @foreach($years as $y)<option value="{{ $y }}" {{ (string) old('year') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
            </select>
            @error('year')<span class="mc-err">{{ $message }}</span>@enderror
          </label>
          <label class="mc-field">
            <span class="mc-field__label">Kilómetros</span>
            <input class="mc-input" type="text" name="mileage" value="{{ old('mileage') }}" inputmode="numeric" pattern="[0-9 .]*" autocomplete="off" placeholder="120.000" required>
            @error('mileage')<span class="mc-err">{{ $message }}</span>@enderror
          </label>

          <fieldset class="sl-pick sl-span2">
            <legend class="mc-field__label">Combustible <span class="mc-field__opt">(si lo sabes)</span></legend>
            <div class="sl-pick__row">
              @foreach($fuels as $f)
                <label class="sl-pill"><input type="radio" name="fuel" value="{{ $f }}" {{ old('fuel') === $f ? 'checked' : '' }}><span>{{ $f }}</span></label>
              @endforeach
            </div>
          </fieldset>
          <fieldset class="sl-pick sl-span2">
            <legend class="mc-field__label">Cambio <span class="mc-field__opt">(si lo sabes)</span></legend>
            <div class="sl-pick__row">
              @foreach($gears as $g)
                <label class="sl-pill"><input type="radio" name="transmission" value="{{ $g }}" {{ old('transmission') === $g ? 'checked' : '' }}><span>{{ $g }}</span></label>
              @endforeach
            </div>
          </fieldset>

          <label class="mc-field sl-span2">
            <span class="mc-field__label">Lo que pides <span class="mc-field__opt">(opcional — si no lo tienes claro, te digo yo)</span></span>
            <span class="sl-money"><input class="mc-input" type="text" name="price" value="{{ old('price') }}" inputmode="numeric" pattern="[0-9 .]*" autocomplete="off" placeholder="14.500"><b>€</b></span>
            @error('price')<span class="mc-err">{{ $message }}</span>@enderror
          </label>
        </div>
        </div>
      </section>

      {{-- ---- 3 · las fotos ------------------------------------------
           The centre of the page. He buys on photographs; the ones he takes of
           his own stock are the argument of the whole site. Previews are shown
           WHOLE — object-fit:contain — the same rule as every customer
           photograph here: nothing anyone hands us gets cropped. --}}
      <section class="sl-sec" aria-labelledby="fotos-h">
        <div class="sl-sec__head">
          <span class="sl-sec__n" aria-hidden="true">2</span>
          <h2 class="ct-h2" id="fotos-h">Fotos</h2>
          <p class="sl-sec__p">Las del móvil valen. Exterior por los cuatro lados,
            el interior, el cuentakilómetros, y lo que no esté bien — eso también.
            Hasta {{ $maxPhotos }}.</p>
        </div>
        <div class="sl-sec__body">
        <div class="sl-drop" id="sl-drop">
          {{-- The input lives INSIDE its label. The target a finger meets is the
               label — the whole zone — and the audit measures it that way only
               when the control is nested, which is also how every other field on
               this site is built. --}}
          <label class="sl-drop__label">
            <input class="sl-drop__in" type="file" name="photos[]" id="sl-files" accept="image/*" multiple>
            <span class="sl-drop__big">Elige las fotos</span>
            <span class="sl-drop__small">o arrástralas aquí</span>
          </label>
          <ul class="sl-previews" id="sl-previews" aria-live="polite"></ul>
          <p class="sl-drop__count" id="sl-count" tabindex="-1" aria-live="polite" hidden></p>
        </div>
        @error('photos')<p class="mc-err">{{ $message }}</p>@enderror
        @error('photos.*')<p class="mc-err">{{ $message }}</p>@enderror
        </div>
      </section>

      {{-- ---- 4 · cómo está ----------------------------------------- --}}
      <section class="sl-sec" aria-labelledby="estado-h">
        <div class="sl-sec__head">
          <span class="sl-sec__n" aria-hidden="true">3</span>
          <h2 class="ct-h2" id="estado-h">Cómo está</h2>
          <p class="sl-sec__p">Opcional. Golpes, averías, cuántos dueños, si tiene
            libro. Cuanto más claro ahora, menos sorpresas después.</p>
        </div>
        <div class="sl-sec__body">
        <label class="mc-field">
          <span class="mc-vh">Estado del coche</span>
          <textarea class="mc-textarea sl-text" name="description" rows="4" maxlength="2000"
                    placeholder="Un dueño, siempre en garaje, revisiones en la casa. Un roce en la puerta trasera.">{{ old('description') }}</textarea>
        </label>
        </div>
      </section>

      {{-- ---- 5 · tú ------------------------------------------------- --}}
      <section class="sl-sec" aria-labelledby="tu-h">
        <div class="sl-sec__head">
          <span class="sl-sec__n" aria-hidden="true">4</span>
          <h2 class="ct-h2" id="tu-h">Tú</h2>
          <p class="sl-sec__p">Te contesto por WhatsApp o te llamo. Contesto yo.</p>
        </div>
        <div class="sl-sec__body">
        <div class="sl-grid">
          <label class="mc-field">
            <span class="mc-field__label">Tu nombre</span>
            <input class="mc-input" type="text" name="seller_name" value="{{ old('seller_name') }}" autocomplete="name" required maxlength="120">
            @error('seller_name')<span class="mc-err">{{ $message }}</span>@enderror
          </label>
          <label class="mc-field">
            <span class="mc-field__label">Tu teléfono</span>
            <input class="mc-input" type="tel" name="seller_phone" value="{{ old('seller_phone') }}" autocomplete="tel" inputmode="tel" required placeholder="6xx xxx xxx">
            @error('seller_phone')<span class="mc-err">{{ $message }}</span>@enderror
          </label>
          <label class="mc-field sl-span2">
            <span class="mc-field__label">Email <span class="mc-field__opt">(opcional)</span></span>
            <input class="mc-input" type="email" name="seller_email" value="{{ old('seller_email') }}" autocomplete="email">
            @error('seller_email')<span class="mc-err">{{ $message }}</span>@enderror
          </label>
        </div>

        <div class="sl-send">
          <button class="mc-btn sl-send__btn" type="submit" id="sl-submit">Enviar</button>
          <p class="sl-send__note">Esto no publica nada. Me llega a mí, lo miro, y
            te escribo.</p>
        </div>
        </div>
      </section>

    </div>
  </form>
@endif

</main>
@endsection

@push('js')
<script>
(function () {
  document.documentElement.className += ' js';
  var form = document.getElementById('sl-form');
  if (!form) { return; }

  /* ---- "otra marca" reveals its one question ------------------------------ */
  var other = document.getElementById('sl-other');
  var radios = form.querySelectorAll('input[name=brand]');
  var syncOther = function () {
    var on = form.querySelector('input[name=brand]:checked');
    var show = !!on && on.value === 'otra';
    other.hidden = !show;
    other.querySelector('input').required = show;
  };
  for (var i = 0; i < radios.length; i++) { radios[i].addEventListener('change', syncOther); }
  syncOther();

  /* ---- photographs: previews, whole, and honest counts ---------------------
     The file input keeps the truth. The previews are drawn from it, never the
     other way round, so a removed preview really removes the file: a DataTransfer
     is rebuilt without it and handed back to the input. */
  var input = document.getElementById('sl-files');
  var list  = document.getElementById('sl-previews');
  var count = document.getElementById('sl-count');
  var drop  = document.getElementById('sl-drop');
  var MAX   = {{ (int) $maxPhotos }};
  var MAX_KB = 12288;

  /* A picture is read without blob: URLs — the site's security policy allows
     images from data: and https: only, and a blob: preview was silently blocked
     (empty squares). createImageBitmap reads the file directly; where it is
     missing, FileReader gives a data: URL. */
  var decode = function (f) {
    if (window.createImageBitmap) {
      return createImageBitmap(f, { imageOrientation: 'from-image' }).catch(function () { return createImageBitmap(f); });
    }
    return new Promise(function (ok, no) {
      var r = new FileReader();
      r.onload = function () { var i = new Image(); i.onload = function () { ok(i); }; i.onerror = no; i.src = r.result; };
      r.onerror = no; r.readAsDataURL(f);
    });
  };
  var draw = function (pic, maxSide) {
    var w = pic.naturalWidth || pic.width, h = pic.naturalHeight || pic.height;
    var k = Math.min(1, maxSide / Math.max(w, h));
    var c = document.createElement('canvas');
    c.width = Math.max(1, Math.round(w * k)); c.height = Math.max(1, Math.round(h * k));
    c.getContext('2d').drawImage(pic, 0, 0, c.width, c.height);
    return c;
  };
  var thumb = function (f, img) {
    if (!window.Promise) { return; }
    decode(f).then(function (pic) { img.src = draw(pic, 320).toDataURL('image/jpeg', 0.75); if (pic.close) { pic.close(); } },
                   function () { img.alt = f.name; });
  };

  var render = function () {
    list.textContent = '';
    var files = input.files, total = 0, over = 0;
    for (var i = 0; i < files.length; i++) {
      var f = files[i]; total += f.size;
      if (f.size > MAX_KB * 1024) { over++; }
      var li = document.createElement('li'); li.className = 'sl-prev' + (f.size > MAX_KB * 1024 ? ' is-over' : '');
      var img = document.createElement('img'); img.alt = ''; img.decoding = 'async';
      thumb(f, img);
      var x = document.createElement('button'); x.type = 'button'; x.className = 'sl-prev__x';
      x.setAttribute('aria-label', 'Quitar la foto ' + (i + 1)); x.textContent = '×'; x.dataset.i = i;
      li.appendChild(img); li.appendChild(x); list.appendChild(li);
    }
    if (files.length) {
      count.hidden = false;
      count.textContent = files.length + (files.length === 1 ? ' foto' : ' fotos') + ' · ' + (total / 1048576).toFixed(1) + ' MB'
        + (files.length > MAX ? ' — son más de ' + MAX + ', quita ' + (files.length - MAX) : '')
        + (over ? ' — ' + over + (over === 1 ? ' pesa' : ' pesan') + ' más de 12 MB' : '');
      count.className = 'sl-drop__count' + (files.length > MAX || over ? ' is-bad' : '');
    } else { count.hidden = true; }
    drop.classList.toggle('has-files', files.length > 0);
  };

  var setFiles = function (arr) {
    var dt = new DataTransfer();
    for (var i = 0; i < arr.length; i++) { dt.items.add(arr[i]); }
    input.files = dt.files;
    render();
  };

  /* Made lighter on the phone before they travel. A phone photograph is 3-5 MB;
     twelve of them over 4G is the upload that fails half-way. Anything over
     1.2 MB that the browser can draw is redrawn at 2400px on the long side as
     JPEG 85% — still more than the admin and the car page ever use — and kept
     only if it came out smaller. Whatever cannot be drawn (HEIC outside Safari)
     travels as it is; any failure keeps the original. */
  var shrinkOne = function (f) {
    if (!/^image\/(jpeg|png|webp)$/.test(f.type) || f.size < 1.2 * 1048576 || !window.HTMLCanvasElement) { return Promise.resolve(f); }
    return decode(f).then(function (pic) {
      var c = draw(pic, 2400); if (pic.close) { pic.close(); }
      return new Promise(function (done) {
        c.toBlob(function (b) {
          if (!b || b.size >= f.size) { return done(f); }
          done(new File([b], f.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg', lastModified: f.lastModified }));
        }, 'image/jpeg', 0.85);
      });
    }).catch(function () { return f; });
  };
  var busy = false;
  var take = function (arr) {
    if (!window.Promise || !window.DataTransfer) { return render(); }
    busy = true; count.hidden = false; count.className = 'sl-drop__count'; count.textContent = 'Preparando las fotos…';
    // one after another: twelve 48 MP photos decoded at once can take an older iPhone down
    var out = [], k = 0;
    var step = function () {
      if (k >= arr.length) { busy = false; return setFiles(out); }
      count.textContent = 'Preparando las fotos… ' + (k + 1) + ' de ' + arr.length;
      shrinkOne(arr[k]).then(function (f) { out.push(f); k++; step(); }, function () { out.push(arr[k]); k++; step(); });
    };
    step();
  };

  input.addEventListener('change', function () {
    var arr = []; for (var i = 0; i < input.files.length; i++) { arr.push(input.files[i]); }
    take(arr);
  });
  list.addEventListener('click', function (e) {
    var b = e.target.closest('.sl-prev__x'); if (!b) { return; }
    var keep = []; for (var i = 0; i < input.files.length; i++) { if (i !== +b.dataset.i) { keep.push(input.files[i]); } }
    setFiles(keep);
  });

  // drag and drop adds to what is there; it does not replace it
  ['dragenter', 'dragover'].forEach(function (t) { drop.addEventListener(t, function (e) { e.preventDefault(); drop.classList.add('is-over'); }); });
  ['dragleave', 'drop'].forEach(function (t) { drop.addEventListener(t, function (e) { e.preventDefault(); drop.classList.remove('is-over'); }); });
  drop.addEventListener('drop', function (e) {
    var add = []; var dt = e.dataTransfer; if (!dt) { return; }
    for (var i = 0; i < dt.files.length; i++) { if (/^image\//.test(dt.files[i].type)) { add.push(dt.files[i]); } }
    var all = []; for (var j = 0; j < input.files.length; j++) { all.push(input.files[j]); }
    take(all.concat(add));
  });

  /* ---- sending: say so, once ------------------------------------------------
     Twelve phone photographs are 50 MB on a 4G upload; a button that looks
     asleep for twenty seconds gets pressed again. */
  var btn = document.getElementById('sl-submit');
  form.addEventListener('submit', function (e) {
    if (busy) { e.preventDefault(); count.textContent = 'Espera un momento: estoy preparando las fotos…'; count.focus(); return; }
    if (input.files.length > MAX) { e.preventDefault(); count.focus(); return; }
    if (!form.checkValidity()) { e.preventDefault(); form.reportValidity(); return; }
    btn.disabled = true; btn.textContent = 'Enviando…';
    form.classList.add('is-sending');
  });
})();
</script>
@endpush
