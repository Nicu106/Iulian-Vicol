{{-- One form for adding a car and for editing one.

     There were two before, 888 and 718 lines, and they had already drifted:
     the create form was still in Romanian, still carried fifteen console.log
     calls left over from the week the gallery upload broke five times, and
     loaded TinyMCE from cdn.tiny.cloud/1/no-api-key/ — which shows the OWNER
     the banner "A valid API key is required. Please alert the admin." Not one
     of the 41 descriptions in the database contains a single HTML tag, so the
     editor was never doing anything except displaying an error; it is gone and
     the field is a textarea.

     The order is the order of the work. The photographs come first because
     they ARE the work: 1,366 files across 41 cars, a median of 30 each. They
     used to be the seventeenth thing on the page.

     $vehicle is an array (the controller casts the model with toArray) or null
     on create. --}}
@php
  $v = $vehicle ?? [];

  /* features, badges and tags are cast to array on the model but validated as
     strings, so edit hands back an array where create hands back a string.
     One textarea, one line each. */
  $f = function ($k, $d = '') use ($v) {
      $val = old($k, $v[$k] ?? $d);

      return is_array($val) ? implode("\n", $val) : $val;
  };

  $isEdit = ! empty($v);

  $gallery = $v['gallery_images'] ?? [];
  $cover   = $v['cover_image'] ?? null;

  $fuels  = ['Gasolina', 'Diésel', 'Híbrido', 'Eléctrico'];
  $gears  = ['Manual', 'Automático'];
  $bodies = ['Berlina', 'Familiar', 'SUV', 'Monovolumen', 'Coupé', 'Cabrio', 'Furgoneta'];
  $states = [
    'available' => 'En la web',
    'reserved'  => 'Reservado',
    'sold'      => 'Vendido',
    'draft'     => 'Borrador — no aparece en la web',
  ];
@endphp

@if($errors->any())
  <div class="ad-flash ad-flash--bad">
    <span>Revisa {{ $errors->count() === 1 ? 'el campo marcado' : 'los ' . $errors->count() . ' campos marcados' }} más abajo.</span>
  </div>
@endif

<form class="ad-form" action="{{ $action }}" method="POST" enctype="multipart/form-data">
  @csrf

  {{-- ---------------------------------------------------------------- 1 --}}
  <fieldset class="ad-fs">
    <h2 class="ad-fs__h">Las fotos</h2>
    <p class="ad-fs__p">Lo primero, porque es lo que vende. Tus coches llevan 30 de media.</p>

    <label class="ad-drop" id="ad-drop">
      <span class="ad-drop__t">Elegir fotos</span>
      <span class="ad-drop__n" id="ad-drop-n">Puedes seleccionar todas a la vez</span>
      <input type="file" name="gallery_images[]" id="ad-gallery" accept="image/*" multiple>
    </label>

    {{-- What the browser has in hand right now. Read from input.files and
         nothing else: the old form rebuilt the FileList through a DataTransfer
         on submit and lost the files five times in one week. --}}
    <ul class="ad-shots" id="ad-new"></ul>

    @error('gallery_images.*')<p class="ad-err">{{ $message }}</p>@enderror

    @if($isEdit && $gallery)
      <h3 class="ad-h2" style="margin-top:var(--s-6)">Ya subidas <span class="ad-seg__n">{{ count($gallery) }}</span></h3>
      <p class="ad-fs__p">Toca una foto para que sea la portada, o quítala con la ×.</p>

      <ul class="ad-shots">
        @foreach($gallery as $i => $url)
          <li class="ad-shot" data-url="{{ $url }}">
            <img class="ad-shot__i" src="{{ $url }}" alt="Foto {{ $i + 1 }}" loading="lazy" decoding="async">
            @if($url === $cover)
              <span class="ad-shot__flag">Portada</span>
            @endif
            <span class="ad-shot__n">{{ $i + 1 }}</span>
            <button class="ad-shot__x" type="button" data-drop aria-label="Quitar la foto {{ $i + 1 }}">×</button>
            @if($url !== $cover)
              <label class="ad-shot__pick">
                <input type="radio" name="cover_from" value="{{ $url }}">
                <span>Portada</span>
              </label>
            @endif
          </li>
        @endforeach
      </ul>
      <input type="hidden" name="removed_gallery_images" id="ad-removed">
    @endif

    {{-- The cover is the first photograph unless he says otherwise, and on the
         edit page he says otherwise by tapping one of the thirty above. This
         input is for the rare case of a picture that is not in the gallery at
         all, so it does not stand at the top of the page asking to be filled. --}}
    <details class="ad-more" style="margin-top:var(--s-5)">
      <summary>Subir una portada distinta</summary>
      <div class="ad-more__in">
        <label class="ad-field">
          <span>Se usará esta en lugar de la primera foto</span>
          <input class="ad-in" type="file" name="cover_image" accept="image/*">
        </label>
        @error('cover_image')<p class="ad-err">{{ $message }}</p>@enderror
      </div>
    </details>
  </fieldset>

  {{-- ---------------------------------------------------------------- 2 --}}
  <fieldset class="ad-fs">
    <h2 class="ad-fs__h">El coche</h2>
    <div class="ad-grid">
      <label class="ad-field">
        <span>Marca</span>
        <input class="ad-in {{ $errors->has('brand') ? 'is-bad' : '' }}" type="text" name="brand" value="{{ $f('brand') }}" required>
        @error('brand')<span class="ad-err">{{ $message }}</span>@enderror
      </label>
      <label class="ad-field">
        <span>Modelo</span>
        <input class="ad-in {{ $errors->has('model') ? 'is-bad' : '' }}" type="text" name="model" value="{{ $f('model') }}" required>
        @error('model')<span class="ad-err">{{ $message }}</span>@enderror
      </label>
      <label class="ad-field">
        <span>Año</span>
        <input class="ad-in {{ $errors->has('year') ? 'is-bad' : '' }}" type="number" name="year" value="{{ $f('year') }}" min="1900" max="{{ date('Y') }}" required>
        @error('year')<span class="ad-err">{{ $message }}</span>@enderror
      </label>
      <label class="ad-field">
        <span>Precio</span>
        <span class="ad-unit">
          <input class="ad-in {{ $errors->has('price') ? 'is-bad' : '' }}" type="number" name="price" value="{{ $f('price') }}" min="0" step="1" required>
          <span class="ad-unit__u">€</span>
        </span>
        @error('price')<span class="ad-err">{{ $message }}</span>@enderror
      </label>
      <label class="ad-field">
        <span>Kilómetros</span>
        <span class="ad-unit">
          <input class="ad-in" type="number" name="mileage" value="{{ $f('mileage') }}" min="0" step="1">
          <span class="ad-unit__u">km</span>
        </span>
      </label>
      <div class="ad-field">
        <span>Estado</span>
        <select class="ad-in" name="status">
          @foreach($states as $key => $label)
            <option value="{{ $key }}" @selected($f('status', 'available') === $key)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </fieldset>

  {{-- ---------------------------------------------------------------- 3 --}}
  <fieldset class="ad-fs">
    <h2 class="ad-fs__h">Datos</h2>

    <div class="ad-field" style="margin-bottom:var(--s-4)">
      <span>Combustible</span>
      <div class="ad-pills">
        @foreach($fuels as $x)
          <label class="ad-pill"><input type="radio" name="fuel" value="{{ $x }}" @checked($f('fuel') === $x)><span>{{ $x }}</span></label>
        @endforeach
      </div>
    </div>

    <div class="ad-field" style="margin-bottom:var(--s-4)">
      <span>Cambio</span>
      <div class="ad-pills">
        @foreach($gears as $x)
          <label class="ad-pill"><input type="radio" name="transmission" value="{{ $x }}" @checked($f('transmission') === $x)><span>{{ $x }}</span></label>
        @endforeach
      </div>
    </div>

    <div class="ad-grid">
      <div class="ad-field">
        <span>Carrocería</span>
        <select class="ad-in" name="body_type">
          <option value="">—</option>
          @foreach($bodies as $x)
            <option value="{{ $x }}" @selected($f('body_type') === $x)>{{ $x }}</option>
          @endforeach
        </select>
      </div>
      <label class="ad-field"><span>Motor</span><input class="ad-in" type="text" name="engine" value="{{ $f('engine') }}" placeholder="2.0 TDI"></label>
      <label class="ad-field"><span>Potencia</span><input class="ad-in" type="text" name="power" value="{{ $f('power') }}" placeholder="150 CV"></label>
      <label class="ad-field"><span>Tracción</span><input class="ad-in" type="text" name="drivetrain" value="{{ $f('drivetrain') }}"></label>
      <label class="ad-field"><span>Color</span><input class="ad-in" type="text" name="color" value="{{ $f('color') }}"></label>
      <label class="ad-field"><span>Etiqueta medioambiental</span><input class="ad-in" type="text" name="condition" value="{{ $f('condition') }}" placeholder="C, ECO, 0"></label>
    </div>
  </fieldset>

  {{-- ---------------------------------------------------------------- 4 --}}
  <fieldset class="ad-fs">
    <h2 class="ad-fs__h">Descripción</h2>
    <p class="ad-fs__p">Como se lo contarías a alguien por WhatsApp. Los saltos de línea se respetan.</p>
    <label class="ad-field">
      <span class="ad-vh">Descripción</span>
      <textarea class="ad-in" name="description" rows="10">{{ $f('description') }}</textarea>
    </label>
    <label class="ad-field" style="margin-top:var(--s-4)">
      <span>Equipamiento <em>— uno por línea</em></span>
      <textarea class="ad-in" name="features" rows="5">{{ $f('features') }}</textarea>
    </label>
  </fieldset>

  {{-- The rest is real and the controller accepts all of it, but it is not
       touched on most cars, so it does not get to stand between him and the
       save button. <details> is native: no script, and it prints open. --}}
  <details class="ad-more">
    <summary>Oferta, vídeo y ajustes</summary>
    <div class="ad-more__in">
      <div class="ad-grid">
        <label class="ad-field">
          <span>Precio anterior <em>— para enseñar el descuento</em></span>
          <span class="ad-unit"><input class="ad-in" type="number" name="original_price" value="{{ $f('original_price') }}" min="0"><span class="ad-unit__u">€</span></span>
        </label>
        <label class="ad-field">
          <span>Precio de oferta</span>
          <span class="ad-unit"><input class="ad-in {{ $errors->has('offer_price') ? 'is-bad' : '' }}" type="number" name="offer_price" value="{{ $f('offer_price') }}" min="0"><span class="ad-unit__u">€</span></span>
          @error('offer_price')<span class="ad-err">{{ $message }}</span>@enderror
        </label>
        <label class="ad-field">
          <span>La oferta acaba</span>
          <input class="ad-in" type="date" name="offer_expires_at" value="{{ $f('offer_expires_at') }}">
        </label>
        <div class="ad-field">
          <span>Tipo de oferta</span>
          <select class="ad-in" name="offer_type">
            <option value="">Sin oferta</option>
            @foreach(['flash_sale' => 'Flash', 'seasonal' => 'De temporada', 'clearance' => 'Liquidación', 'negotiable' => 'Negociable', 'promotion' => 'Promoción'] as $k => $label)
              <option value="{{ $k }}" @selected($f('offer_type') === $k)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <label class="ad-field ad-wide">
          <span>Qué dice la oferta</span>
          <input class="ad-in" type="text" name="offer_description" value="{{ $f('offer_description') }}" maxlength="500">
        </label>
        <label class="ad-field ad-wide">
          <span>Vídeo <em>— enlace de YouTube</em></span>
          <input class="ad-in {{ $errors->has('video_url') ? 'is-bad' : '' }}" type="url" name="video_url" value="{{ $f('video_url') }}">
          @error('video_url')<span class="ad-err">{{ $message }}</span>@enderror
        </label>
        <label class="ad-field"><span>Dónde está</span><input class="ad-in" type="text" name="location" value="{{ $f('location') }}" placeholder="Málaga"></label>
        <label class="ad-field"><span>Orden <em>— más alto, más arriba</em></span><input class="ad-in" type="number" name="priority" value="{{ $f('priority', 0) }}" min="0" max="999"></label>
        <label class="ad-field ad-wide">
          <span>Notas para ti <em>— no salen en la web</em></span>
          <textarea class="ad-in" name="internal_notes" rows="3">{{ $f('internal_notes') }}</textarea>
        </label>
      </div>
    </div>
  </details>

  <div class="ad-go">
    <button class="ad-btn" type="submit">{{ $isEdit ? 'Guardar cambios' : 'Publicar el coche' }}</button>
    <a class="ad-btn ad-btn--q" href="{{ route('admin.vehicles.index') }}">Cancelar</a>
    @if($isEdit)
      <span style="flex:1"></span>
      <a class="ad-btn ad-btn--q" href="{{ url('/coche/' . ($v['slug'] ?? '')) }}">Ver la ficha</a>
    @endif
  </div>
</form>

@if($isEdit)
  {{-- Deleting a car is not a button that sits beside "Guardar". --}}
  <form class="ad-danger" action="{{ route('admin.vehicles.destroy', $v['slug']) }}" method="POST"
        onsubmit="return confirm('Se borra {{ trim(($v['brand'] ?? '') . ' ' . ($v['model'] ?? '')) }} y todas sus fotos. ¿Seguro?')">
    @csrf
    @method('DELETE')
    <button class="ad-btn ad-btn--bad ad-btn--s" type="submit">Borrar este coche</button>
  </form>
@endif

@push('js')
<script>
/* Two jobs, and nothing else.
   1. Show what the file input is holding, straight from input.files. The form
      this replaces rebuilt that FileList through a DataTransfer on submit, and
      the git log has five commits in one week trying to make that work
      ("CRITICAL FIX", "ULTIMATE FIX", "FINAL FIX"). The native input is the
      truth and is never written to.
   2. Mark an already-uploaded photograph for removal. The row greys out and
      its URL joins a hidden field the controller already reads. */
(function () {
  var input = document.getElementById('ad-gallery');
  var list  = document.getElementById('ad-new');
  var count = document.getElementById('ad-drop-n');
  var drop  = document.getElementById('ad-drop');

  if (input && list) {
    input.addEventListener('change', function () {
      list.textContent = '';
      var files = Array.prototype.slice.call(input.files || []);
      count.textContent = files.length
        ? files.length + (files.length === 1 ? ' foto elegida' : ' fotos elegidas') + ' — se suben al guardar'
        : 'Puedes seleccionar todas a la vez';

      files.forEach(function (file, i) {
        var li  = document.createElement('li');
        li.className = 'ad-shot';
        var img = document.createElement('img');
        img.className = 'ad-shot__i';
        img.alt = file.name;
        img.src = URL.createObjectURL(file);
        img.onload = function () { URL.revokeObjectURL(img.src); };
        var n = document.createElement('span');
        n.className = 'ad-shot__n';
        n.textContent = i + 1;
        li.appendChild(img);
        li.appendChild(n);
        list.appendChild(li);
      });
    });
  }

  /* A drag onto the label has to be told not to open the file in the tab. */
  if (drop && input) {
    ['dragenter', 'dragover'].forEach(function (e) {
      drop.addEventListener(e, function (ev) { ev.preventDefault(); drop.classList.add('is-over'); });
    });
    ['dragleave', 'drop'].forEach(function (e) {
      drop.addEventListener(e, function () { drop.classList.remove('is-over'); });
    });
    drop.addEventListener('drop', function (ev) {
      ev.preventDefault();
      if (ev.dataTransfer && ev.dataTransfer.files.length) {
        input.files = ev.dataTransfer.files;
        input.dispatchEvent(new Event('change'));
      }
    });
  }

  var removed = document.getElementById('ad-removed');
  if (removed) {
    var gone = [];
    document.querySelectorAll('[data-drop]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var li = btn.closest('.ad-shot');
        var url = li.getAttribute('data-url');
        var i = gone.indexOf(url);
        if (i === -1) { gone.push(url); li.classList.add('is-gone'); }
        else { gone.splice(i, 1); li.classList.remove('is-gone'); }
        removed.value = JSON.stringify(gone);
        btn.textContent = i === -1 ? '↺' : '×';
        btn.setAttribute('aria-label', i === -1 ? 'Recuperar esta foto' : 'Quitar esta foto');
      });
    });
  }
})();
</script>
@endpush
