{{-- One form for adding a car and for editing one.

     There were two before, 888 and 718 lines, and they had already drifted:
     the create form was still in Romanian, still carried fifteen console.log
     calls left over from the week the gallery upload broke five times, and
     loaded TinyMCE from cdn.tiny.cloud/1/no-api-key/. The editor is gone and
     the description is a textarea.

     2026-10-16 — the owner adds cars from his PHONE, standing next to the car.
     The form is now built for that:
     - Sections in the order of the work: photos, the car, price, details,
       video, publish. Each is its own panel.
     - Photos in three groups — Exterior, Interior, Desperfectos — which is what
       the car page's tabs show (CarPageController). Each photo carries its group
       as a native <select> under it, so moving one is a tap and a native picker,
       and it works without JavaScript too. Old untagged photos wait in
       "Sin clasificar" at the top.
     - With JavaScript each photo is shrunk on the phone and sent on its own
       (POST /admin/fotos) with progress and retries; the form only carries the
       paths. A car not yet created works the same: the draft token owns them.
     - What he types is kept on the phone (localStorage) until the save succeeds;
       the session is kept alive while the page is open; an expired one is said
       in Spanish with a way back. Script: _form-script.blade.php.

     2026-10-17 — and in his order. A photo is held and dragged to its place,
     in its group or into another one; the order goes with the form as
     photo_order and is the order of the car page. The keyboard does the same
     from each photo's "Mover" button (visible only when it has focus).

     $vehicle is an array (the controller casts the model with toArray) or null
     on create. --}}
@php
  use App\Support\CarPhotos;
  use App\Support\YouTube;

  $v = $vehicle ?? [];
  $isEdit = ! empty($v);

  /* features, badges and tags are cast to array on the model but validated as
     strings, so edit hands back an array where create hands back a string.
     One textarea, one line each. Prices come back as 23700.0 — shown as 23700,
     because the phone's number pad has no decimal key. */
  $f = function ($k, $d = '') use ($v) {
      $val = old($k, $v[$k] ?? $d);
      if (is_array($val)) {
          return implode("\n", $val);
      }
      if (is_float($val) && fmod($val, 1.0) === 0.0) {
          return (string) (int) $val;
      }
      if (in_array($k, ['price', 'original_price', 'offer_price'], true) && is_string($val)) {
          return preg_replace('/^(\d+)\.0+$/', '$1', $val);
      }

      return $val;
  };

  $slug = $v['slug'] ?? null;
  $token = CarPhotos::validToken(old('photo_token')) ? old('photo_token') : CarPhotos::newToken();
  $hasOld = session()->hasOldInput();

  // The photos already on the car, in the car page's order, each in its group —
  // or in the group he had moved it to, if the form is coming back with errors.
  $oldPhotos = old('photos');
  $shots = [];
  foreach ($isEdit ? CarPhotos::listFor($v) : [] as $k => $ph) {
      $cat = $ph['tag'] ?? 'none';
      $rm = false;
      if (is_array($oldPhotos) && ($oldPhotos[$k]['path'] ?? null) === $ph['path']) {
          $cat = CarPhotos::tag($oldPhotos[$k]['tag'] ?? null) ?? 'none';
          $rm = ! empty($oldPhotos[$k]['remove']);
      }
      $shots[] = $ph + ['k' => $k, 'cat' => $cat, 'rm' => $rm];
  }
  // Sent one by one but not yet saved with the car (only after a failed save).
  $staged = [];
  foreach ((array) old('staged', []) as $row) {
      if (is_array($row) && is_string($row['path'] ?? null) && str_starts_with($row['path'], '/storage/' . CarPhotos::DRAFTS . '/' . $token . '/')) {
          $staged[] = ['path' => $row['path'], 'cat' => CarPhotos::tag($row['tag'] ?? null) ?? 'none'];
      }
  }
  $coverNow = old('cover_from', $v['cover_image'] ?? null);

  // One list for both kinds, so a photo sent today can sit between two that
  // were already on the car. Back from a failed save, in the order he had left
  // them (photo_order); otherwise the car's order, then the new ones.
  $tiles = array_merge(
      array_map(fn ($s) => $s + ['kind' => 'car'], $shots),
      array_map(fn ($s, $j) => $s + ['kind' => 'staged', 'j' => $j], $staged, array_keys($staged))
  );
  if ($was = CarPhotos::orderOf(old('photo_order'))) {
      $at = array_flip(array_reverse($was, true));
      $seq = array_keys($tiles);
      usort($seq, fn ($a, $b) => [$at[$tiles[$a]['path']] ?? PHP_INT_MAX, $a] <=> [$at[$tiles[$b]['path']] ?? PHP_INT_MAX, $b]);
      $tiles = array_map(fn ($k) => $tiles[$k], $seq);
  }

  $groups = [
      'none'     => ['Sin clasificar', 'Fotos de antes, sin grupo. Dile a cada una qué es con la etiqueta de debajo.'],
      'exterior' => ['Exterior', 'El coche por fuera, desde todos los lados.'],
      'interior' => ['Interior', 'Asientos, salpicadero, maletero.'],
      'flaw'     => ['Desperfectos', 'Golpes, arañazos, desgaste. Enseñarlos da confianza.'],
  ];
  $tagNames = ['exterior' => 'Exterior', 'interior' => 'Interior', 'flaw' => 'Desperfectos', 'none' => 'Sin clasificar'];
  $count = fn ($cat) => count(array_filter($shots, fn ($s) => $s['cat'] === $cat)) + count(array_filter($staged, fn ($s) => $s['cat'] === $cat));

  $video = YouTube::parse($f('video_url'));

  // The five marques first, then whatever else is already in the list, so
  // "Mercedes" and "Mercedes-benz" stop being two brands.
  $brands = collect(\App\Support\Marques::ALL)->pluck('name')
      ->merge(\App\Models\Vehicle::query()->distinct()->pluck('brand')->filter()
          ->reject(fn ($b) => \App\Support\Marques::for($b))->map(fn ($b) => \Illuminate\Support\Str::title($b)))
      ->unique()->values();

  $fuels  = ['Gasolina', 'Diésel', 'Híbrido', 'Eléctrico'];
  $gears  = ['Manual', 'Automático'];
  $bodies = ['Berlina', 'Familiar', 'SUV', 'Monovolumen', 'Coupé', 'Cabrio', 'Furgoneta'];
  $states = [
    'available' => 'En la web',
    'reserved'  => 'Reservado',
    'sold'      => 'Vendido',
    'draft'     => 'Borrador',
  ];
  $hasOffer = $f('offer_price') || $f('original_price') || $errors->hasAny(['offer_price', 'original_price', 'offer_expires_at']);
@endphp

@if($errors->any())
  <div class="ad-flash ad-flash--bad">
    <span>Revisa {{ $errors->count() === 1 ? 'el campo marcado' : 'los ' . $errors->count() . ' campos marcados' }} más abajo.</span>
  </div>
@endif

<form class="ad-form ad-car-form" id="ad-car" action="{{ $action }}" method="POST" enctype="multipart/form-data"
      data-draft="mc-car:{{ $slug ?? 'new' }}"
      data-base="{{ $isEdit ? (string) ($v['updated_at'] ?? '') : '' }}"
      data-old="{{ $hasOld ? 1 : 0 }}"
      data-photos="{{ route('admin.photos.store') }}"
      data-ping="{{ route('admin.ping') }}"
      data-login="{{ route('login') }}"
      data-staged="{{ count($staged) }}">
  @csrf
  <input type="hidden" name="photo_token" id="ad-token" value="{{ $token }}">
  {{-- Every photo's path, in the order of the groups and of the photos in them;
       written by the script, empty (and ignored) without it. --}}
  <input type="hidden" name="photo_order" id="ad-order" value="">

  {{-- The notice for a restored draft and for an expired session; filled by the script. --}}
  <div class="ad-note-box" id="ad-notice" role="status" aria-live="polite" hidden></div>

  {{-- ------------------------------------------------------------ photos --}}
  <fieldset class="ad-fs ad-box ad-photos" id="ad-photos">
    <h2 class="ad-fs__h">Las fotos</h2>
    <p class="ad-fs__p">Así salen en la ficha, en este orden. Toca una foto para hacerla la portada.<span data-dnd hidden> Para moverla, mantenla pulsada y arrástrala a su sitio o a otro grupo.</span></p>
    <p class="ad-vh" id="ad-dnd-help">Espacio para coger la foto, las flechas para moverla, Espacio para dejarla y Escape para cancelar.</p>
    <p class="ad-vh" id="ad-dnd-live" aria-live="assertive" aria-atomic="true"></p>

    @foreach($groups as $key => [$label, $hint])
      @php $n = $count($key); @endphp
      <section class="ad-cat" data-cat="{{ $key }}" @if($key === 'none' && ! $n) hidden @endif>
        <div class="ad-cat__h">
          <h3 class="ad-cat__t"><span class="ad-dot" data-tag="{{ $key }}" aria-hidden="true"></span>{{ $label }}
            <span class="ad-cat__n" data-count>{{ $n }}</span></h3>
          <p class="ad-cat__p">{{ $hint }}</p>
        </div>

        <ul class="ad-shots" data-list>
          @foreach($tiles as $s)
            @continue($s['cat'] !== $key)
            @if($s['kind'] === 'car')
            <li class="ad-shot {{ $s['rm'] ? 'is-gone' : '' }}" data-i="{{ $s['i'] }}" data-path="{{ $s['path'] }}">
              <input type="hidden" name="photos[{{ $s['k'] }}][path]" value="{{ $s['path'] }}">
              <label class="ad-shot__pic">
                <input class="ad-shot__cv" type="radio" name="cover_from" value="{{ $s['path'] }}" @checked($coverNow === $s['path'])>
                <img class="ad-shot__i" src="{{ \App\Support\Img::url($s['path'], 320) ?? $s['path'] }}" alt="" loading="lazy" decoding="async" draggable="false">
                <span class="ad-shot__flag">Portada</span>
                <span class="ad-vh">Hacer esta foto la portada</span>
              </label>
              <button class="ad-shot__mv" type="button" aria-describedby="ad-dnd-help" hidden><span>Mover</span></button>
              <label class="ad-shot__x">
                <input type="checkbox" name="photos[{{ $s['k'] }}][remove]" value="1" @checked($s['rm'])>
                <span class="ad-shot__xi" aria-hidden="true"></span>
                <span class="ad-vh">Quitar esta foto</span>
              </label>
              <label class="ad-tag" data-tag="{{ $s['cat'] }}">
                <span class="ad-vh">Grupo de la foto</span>
                <select class="ad-tag__s" name="photos[{{ $s['k'] }}][tag]">
                  @foreach($tagNames as $tk => $tn)<option value="{{ $tk }}" @selected($tk === $s['cat'])>{{ $tn }}</option>@endforeach
                </select>
              </label>
            </li>
            @else
            <li class="ad-shot is-new" data-i="{{ 100000 + $s['j'] }}" data-path="{{ $s['path'] }}" data-state="done" data-j="{{ $s['j'] }}">
              <input type="hidden" name="staged[{{ $s['j'] }}][path]" value="{{ $s['path'] }}">
              <label class="ad-shot__pic">
                <input class="ad-shot__cv" type="radio" name="cover_from" value="{{ $s['path'] }}" @checked($coverNow === $s['path'])>
                <img class="ad-shot__i" src="{{ $s['path'] }}" alt="" loading="lazy" decoding="async" draggable="false">
                <span class="ad-shot__flag">Portada</span>
                <span class="ad-vh">Hacer esta foto la portada</span>
              </label>
              <button class="ad-shot__mv" type="button" aria-describedby="ad-dnd-help" hidden><span>Mover</span></button>
              <button class="ad-shot__x" type="button" data-unstage><span class="ad-shot__xi" aria-hidden="true"></span><span class="ad-vh">Quitar esta foto</span></button>
              <label class="ad-tag" data-tag="{{ $s['cat'] }}">
                <span class="ad-vh">Grupo de la foto</span>
                <select class="ad-tag__s" name="staged[{{ $s['j'] }}][tag]">
                  @foreach($tagNames as $tk => $tn)<option value="{{ $tk }}" @selected($tk === $s['cat'])>{{ $tn }}</option>@endforeach
                </select>
              </label>
            </li>
            @endif
          @endforeach
        </ul>

        @if($key !== 'none')
          {{-- The input's own label: one tap opens the phone's picker (camera or
               library). accept="image/*" is what makes iOS hand over JPEG
               instead of HEIC; a HEIC that gets through anyway is converted on
               the server. --}}
          <label class="ad-up">
            <input type="file" name="photos_{{ $key }}[]" accept="image/*" multiple data-up="{{ $key }}">
            <span class="ad-up__t">Añadir fotos{{ ['exterior' => ' del exterior', 'interior' => ' del interior', 'flaw' => ' de desperfectos'][$key] }}</span>
          </label>
        @endif
      </section>
    @endforeach

    @error('gallery_images.*')<p class="ad-err">{{ $message }}</p>@enderror
    @foreach(['photos_exterior.*', 'photos_interior.*', 'photos_flaw.*'] as $pk)
      @error($pk)<p class="ad-err">{{ $message }}</p>@enderror
    @endforeach

    {{-- Rare: a cover that is not one of the photos at all. --}}
    <details class="ad-more ad-more--in">
      <summary>Subir una portada distinta</summary>
      <div class="ad-more__in">
        <label class="ad-field">
          <span>Se usará esta en lugar de la elegida arriba</span>
          <input class="ad-in" type="file" name="cover_image" accept="image/*">
        </label>
        @error('cover_image')<p class="ad-err">{{ $message }}</p>@enderror
      </div>
    </details>
  </fieldset>

  {{-- ------------------------------------------------------------ the car --}}
  <fieldset class="ad-fs ad-box">
    <h2 class="ad-fs__h">El coche</h2>
    <p class="ad-fs__p">Marca, modelo, año y precio son lo único obligatorio; lo demás puede esperar.</p>
    <div class="ad-grid">
      <label class="ad-field">
        <span>Marca</span>
        <input class="ad-in {{ $errors->has('brand') ? 'is-bad' : '' }}" type="text" name="brand" value="{{ $f('brand') }}"
               list="ad-brands" autocomplete="off" autocapitalize="words" enterkeyhint="next" required>
        @error('brand')<span class="ad-err">{{ $message }}</span>@enderror
      </label>
      <datalist id="ad-brands">@foreach($brands as $b)<option value="{{ $b }}"></option>@endforeach</datalist>
      <label class="ad-field">
        <span>Modelo</span>
        <input class="ad-in {{ $errors->has('model') ? 'is-bad' : '' }}" type="text" name="model" value="{{ $f('model') }}"
               autocomplete="off" autocapitalize="words" enterkeyhint="next" required>
        @error('model')<span class="ad-err">{{ $message }}</span>@enderror
      </label>
      <label class="ad-field">
        <span>Año</span>
        <input class="ad-in {{ $errors->has('year') ? 'is-bad' : '' }}" type="text" name="year" value="{{ $f('year') }}"
               inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="off" enterkeyhint="next" required>
        @error('year')<span class="ad-err">{{ $message }}</span>@enderror
      </label>
      <label class="ad-field">
        <span>Kilómetros</span>
        <span class="ad-unit">
          <input class="ad-in {{ $errors->has('mileage') ? 'is-bad' : '' }}" type="text" name="mileage" value="{{ $f('mileage') }}"
                 inputmode="numeric" autocomplete="off" enterkeyhint="next">
          <span class="ad-unit__u">km</span>
        </span>
        @error('mileage')<span class="ad-err">{{ $message }}</span>@enderror
      </label>
    </div>
  </fieldset>

  {{-- ------------------------------------------------------------ price --}}
  <fieldset class="ad-fs ad-box">
    <h2 class="ad-fs__h">Precio</h2>
    <div class="ad-grid">
      <label class="ad-field">
        <span>Precio</span>
        <span class="ad-unit">
          <input class="ad-in ad-in--big {{ $errors->has('price') ? 'is-bad' : '' }}" type="text" name="price" value="{{ $f('price') }}"
                 inputmode="numeric" autocomplete="off" enterkeyhint="next" required>
          <span class="ad-unit__u">€</span>
        </span>
        @error('price')<span class="ad-err">{{ $message }}</span>@enderror
      </label>
    </div>

    <details class="ad-more ad-more--in" @if($hasOffer) open @endif>
      <summary>Poner una oferta</summary>
      <div class="ad-more__in">
        <div class="ad-grid">
          <label class="ad-field">
            <span>Precio anterior <em>— para enseñar el descuento</em></span>
            <span class="ad-unit"><input class="ad-in {{ $errors->has('original_price') ? 'is-bad' : '' }}" type="text" inputmode="numeric" name="original_price" value="{{ $f('original_price') }}" autocomplete="off"><span class="ad-unit__u">€</span></span>
            @error('original_price')<span class="ad-err">{{ $message }}</span>@enderror
          </label>
          <label class="ad-field">
            <span>Precio de oferta</span>
            <span class="ad-unit"><input class="ad-in {{ $errors->has('offer_price') ? 'is-bad' : '' }}" type="text" inputmode="numeric" name="offer_price" value="{{ $f('offer_price') }}" autocomplete="off"><span class="ad-unit__u">€</span></span>
            @error('offer_price')<span class="ad-err">{{ $message }}</span>@enderror
          </label>
          <label class="ad-field">
            <span>La oferta acaba</span>
            <input class="ad-in {{ $errors->has('offer_expires_at') ? 'is-bad' : '' }}" type="date" name="offer_expires_at" value="{{ $f('offer_expires_at') }}">
            @error('offer_expires_at')<span class="ad-err">{{ $message }}</span>@enderror
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
            <input class="ad-in" type="text" name="offer_description" value="{{ $f('offer_description') }}" maxlength="500" enterkeyhint="next">
          </label>
        </div>
      </div>
    </details>
  </fieldset>

  {{-- ------------------------------------------------------------ details --}}
  <fieldset class="ad-fs ad-box">
    <h2 class="ad-fs__h">Detalles</h2>

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
      <label class="ad-field"><span>Motor</span><input class="ad-in" type="text" name="engine" value="{{ $f('engine') }}" placeholder="2.0 TDI" autocomplete="off" enterkeyhint="next"></label>
      <label class="ad-field"><span>Potencia</span><input class="ad-in" type="text" name="power" value="{{ $f('power') }}" placeholder="150 CV" autocomplete="off" enterkeyhint="next"></label>
      <label class="ad-field"><span>Tracción</span><input class="ad-in" type="text" name="drivetrain" value="{{ $f('drivetrain') }}" autocomplete="off" enterkeyhint="next"></label>
      <label class="ad-field"><span>Color</span><input class="ad-in" type="text" name="color" value="{{ $f('color') }}" autocomplete="off" autocapitalize="words" enterkeyhint="next"></label>
      <label class="ad-field"><span>Etiqueta medioambiental</span><input class="ad-in" type="text" name="condition" value="{{ $f('condition') }}" placeholder="C, ECO, 0" autocomplete="off" autocapitalize="characters" enterkeyhint="next"></label>
    </div>

    <label class="ad-field" style="margin-top:var(--s-5)">
      <span>Descripción <em>— como se lo contarías por WhatsApp</em></span>
      <textarea class="ad-in" name="description" rows="8">{{ $f('description') }}</textarea>
    </label>
    <label class="ad-field" style="margin-top:var(--s-4)">
      <span>Equipamiento <em>— uno por línea</em></span>
      <textarea class="ad-in" name="features" rows="5">{{ $f('features') }}</textarea>
    </label>
  </fieldset>

  {{-- ------------------------------------------------------------ video --}}
  <fieldset class="ad-fs ad-box">
    <h2 class="ad-fs__h">Vídeo</h2>
    <p class="ad-fs__p">Si lo has subido a YouTube, pega el enlace del botón «Compartir». Sale en la ficha, debajo de las fotos.</p>
    <label class="ad-field">
      <span class="ad-vh">Enlace de YouTube</span>
      <input class="ad-in {{ $errors->has('video_url') ? 'is-bad' : '' }}" id="ad-video" type="url" inputmode="url" name="video_url"
             value="{{ $f('video_url') }}" placeholder="https://youtu.be/…" autocomplete="off" autocapitalize="none" autocorrect="off" spellcheck="false" enterkeyhint="done">
      @error('video_url')<span class="ad-err">{{ $message }}</span>@enderror
    </label>
    {{-- The still comes from i.ytimg.com (img-src https: allows it): one picture,
         no player, so he can see it is the right video. --}}
    <div class="ad-yt" id="ad-yt" @unless($video) hidden @endunless>
      <img class="ad-yt__i" id="ad-yt-i" alt="" width="320" height="180" @if($video) src="{{ YouTube::poster($video['id'], 'mqdefault') }}" @endif>
      <div class="ad-yt__t">
        <p class="ad-yt__p">Este es el vídeo que verá el comprador.</p>
        <p class="ad-yt__miss">No encuentro este vídeo en YouTube: comprueba que es público u «oculto», no privado.</p>
        <a class="ad-yt__a" id="ad-yt-a" target="_blank" rel="noopener" @if($video) href="{{ YouTube::watch($video) }}" @endif>Abrirlo en YouTube</a>
        <button class="ad-yt__x" type="button" id="ad-yt-x">Quitar el vídeo</button>
      </div>
    </div>
  </fieldset>

  {{-- ------------------------------------------------------------ publish --}}
  <fieldset class="ad-fs ad-box">
    <h2 class="ad-fs__h">Publicar</h2>

    <div class="ad-field" style="margin-bottom:var(--s-5)">
      <span>Estado</span>
      <div class="ad-pills">
        @foreach($states as $key => $label)
          <label class="ad-pill"><input type="radio" name="status" value="{{ $key }}" @checked($f('status', 'available') === $key)><span>{{ $label }}</span></label>
        @endforeach
      </div>
    </div>

    {{-- Who sent the buyer. When the car is sold with this set, a pending
         reward is created (App\Support\Referral::syncReward). --}}
    @php
      /* Links whose people looked at THIS car, strongest first: when he sells it,
         the likely answer to "Vino de parte de" is already at the top. */
      $viewers = ($isEdit && ! empty($v['id']))
        ? \App\Models\ReferralEvent::query()
            ->join('referral_visitors', 'referral_visitors.id', '=', 'referral_events.visitor_id')
            ->where('referral_events.type', 'car')->where('referral_events.vehicle_id', $v['id'])
            ->selectRaw('referral_visitors.referrer_id as rid, count(distinct referral_visitors.id) as people')
            ->groupBy('referral_visitors.referrer_id')->pluck('people', 'rid')
        : collect();
      $referrers = \App\Models\Referrer::orderBy('name')->get(['id', 'name', 'code'])
        ->sortByDesc(fn ($r) => (int) ($viewers[$r->id] ?? 0))->values();
    @endphp
    <div class="ad-grid">
      <div class="ad-field ad-wide">
        <span>Vino de parte de <em>— si alguien te lo recomendó</em></span>
        <select class="ad-in" name="referred_by">
          <option value="">Nadie</option>
          @foreach($referrers as $r)
            <option value="{{ $r->id }}" @selected((string) $f('referred_by') === (string) $r->id)>{{ $r->name }} · {{ $r->code }}@if($viewers[$r->id] ?? 0) — {{ $viewers[$r->id] === 1 ? '1 persona suya vio este coche' : $viewers[$r->id] . ' personas suyas vieron este coche' }}@endif</option>
          @endforeach
        </select>
        @error('referred_by')<span class="ad-err">{{ $message }}</span>@enderror
        @if($viewers->isNotEmpty())
          <span class="ad-note" style="margin:0">Llegaron por una recomendación y miraron este coche:
            {{ $referrers->filter(fn ($r) => $viewers[$r->id] ?? 0)->map(fn ($r) => $r->name . ' (' . $viewers[$r->id] . ')')->implode(', ') }}.</span>
        @endif
        @if($referrers->isEmpty())
          <span class="ad-note" style="margin:0">Todavía no hay enlaces de recomendación.
            <a href="{{ route('admin.referrals.index') }}">Ver cómo funciona</a>.</span>
        @endif
      </div>
      <label class="ad-field"><span>Dónde está</span><input class="ad-in" type="text" name="location" value="{{ $f('location') }}" placeholder="Málaga" autocomplete="off" enterkeyhint="next"></label>
      <label class="ad-field"><span>Orden <em>— más alto, más arriba</em></span><input class="ad-in {{ $errors->has('priority') ? 'is-bad' : '' }}" type="text" inputmode="numeric" name="priority" value="{{ $f('priority', 0) }}" autocomplete="off" enterkeyhint="next">
        @error('priority')<span class="ad-err">{{ $message }}</span>@enderror</label>
      <label class="ad-field ad-wide">
        <span>Notas para ti <em>— no salen en la web</em></span>
        <textarea class="ad-in" name="internal_notes" rows="3">{{ $f('internal_notes') }}</textarea>
      </label>
    </div>

    {{-- A sold car's buyer is the best person to give a link to. --}}
    @if($isEdit && ($v['status'] ?? null) === 'sold')
      @php $buyerLink = \App\Models\Referrer::where('vehicle_id', $v['id'] ?? 0)->first(); @endphp
      <div class="ad-panel" style="margin-top:var(--s-5)">
        <div class="ad-panel__h"><h3>Enlace de recomendación del comprador</h3></div>
        @if($buyerLink)
          <p class="ad-lede" style="margin-bottom:var(--s-3)">{{ $buyerLink->name }} ya tiene su enlace: <b>{{ $buyerLink->code }}</b>.</p>
          <a class="ad-btn ad-btn--q ad-btn--s" target="_blank" rel="noopener"
             href="https://wa.me/{{ $buyerLink->phone }}?text={{ rawurlencode(\App\Support\Referral::messageFor($buyerLink)) }}">Enviarle su enlace</a>
        @else
          <p class="ad-lede" style="margin-bottom:var(--s-3)">Quien compró este coche puede recomendarte a otros. Crea su enlace y mándaselo por WhatsApp.</p>
          <a class="ad-btn ad-btn--q ad-btn--s" href="{{ route('admin.referrals.create', ['coche' => $v['slug'] ?? null]) }}">Crear enlace para el comprador</a>
        @endif
      </div>
    @endif
  </fieldset>

  {{-- Always within reach of the thumb: sticky above the phone's tab bar. The
       line above the button says what is happening — photos still going up, a
       draft kept, the session gone — so the button never just sits there. --}}
  <div class="ad-save" id="ad-save">
    <p class="ad-save__msg" id="ad-save-msg" aria-live="polite"></p>
    <div class="ad-save__row">
      <button class="ad-btn ad-save__go" type="submit" id="ad-save-go"
              data-label="{{ $isEdit ? 'Guardar cambios' : 'Publicar el coche' }}">{{ $isEdit ? 'Guardar cambios' : 'Publicar el coche' }}</button>
      @if($isEdit)
        <a class="ad-btn ad-btn--q" href="{{ url('/coche/' . ($v['slug'] ?? '')) }}">Ver la ficha</a>
      @endif
    </div>
  </div>
</form>

@if($isEdit)
  {{-- Deleting a car is not a button that sits beside "Guardar". --}}
  <form class="ad-danger" action="{{ route('admin.vehicles.destroy', $v['slug']) }}" method="POST"
        onsubmit="return confirm(@js('Se borra '.trim(($v['brand'] ?? '') . ' ' . ($v['model'] ?? '')).' y todas sus fotos. ¿Seguro?'))">
    @csrf
    @method('DELETE')
    <button class="ad-btn ad-btn--bad ad-btn--s" type="submit">Borrar este coche</button>
  </form>
@endif

@include('admin.vehicles._form-script', ['tagNames' => $tagNames])
