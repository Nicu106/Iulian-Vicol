{{-- One form for both. $t is a Testimonial or null. --}}
@php
  $t = $testimonial ?? null;
  $f = fn ($k, $d = '') => old($k, $t->$k ?? $d);
@endphp

@if($errors->any())
  <div class="ad-flash ad-flash--bad">
    <span>Revisa {{ $errors->count() === 1 ? 'el campo marcado' : 'los campos marcados' }}.</span>
  </div>
@endif

<form class="ad-form" action="{{ $action }}" method="POST" enctype="multipart/form-data">
  @csrf
  @if($t)@method('PUT')@endif

  <fieldset class="ad-fs">
    <div class="ad-grid">
      <label class="ad-field">
        <span>Quién lo dice</span>
        <input class="ad-in {{ $errors->has('author_name') ? 'is-bad' : '' }}" type="text" name="author_name" value="{{ $f('author_name') }}" required>
        @error('author_name')<span class="ad-err">{{ $message }}</span>@enderror
      </label>
      <label class="ad-field">
        {{-- Kept because the column exists, but the home page does not print
             it: 19 of 25 rows say Santander and the quotes say otherwise. --}}
        <span>De dónde <em>— no sale en la web</em></span>
        <input class="ad-in" type="text" name="author_location" value="{{ $f('author_location') }}">
      </label>
      <label class="ad-field ad-wide">
        <span>Lo que escribió</span>
        <textarea class="ad-in {{ $errors->has('quote') ? 'is-bad' : '' }}" name="quote" rows="5" maxlength="1000" required>{{ $f('quote') }}</textarea>
        @error('quote')<span class="ad-err">{{ $message }}</span>@enderror
      </label>
      <label class="ad-field">
        <span>Foto <em>— se enseña entera, sin recortar</em></span>
        <input class="ad-in" type="file" name="image" accept="image/*">
        @error('image')<span class="ad-err">{{ $message }}</span>@enderror
      </label>
      <label class="ad-field">
        <span>Orden <em>— más bajo, más pronto</em></span>
        <input class="ad-in" type="number" name="order_index" value="{{ $f('order_index', 0) }}" min="0" max="255">
      </label>
      <div class="ad-field ad-wide">
        <label class="ad-check">
          <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $t->is_active ?? true))>
          <span>Se enseña en la portada</span>
        </label>
      </div>
    </div>
  </fieldset>

  @if($t?->image_path)
    <fieldset class="ad-fs">
      <h2 class="ad-fs__h">Foto actual</h2>
      <img class="ad-shot__i" style="max-width:16rem" src="{{ $t->image_path }}" alt="Foto de {{ $t->author_name }}">
    </fieldset>
  @endif

  <div class="ad-go">
    <button class="ad-btn" type="submit">{{ $t ? 'Guardar' : 'Añadir la opinión' }}</button>
    <a class="ad-btn ad-btn--q" href="{{ route('admin.testimonials.index') }}">Cancelar</a>
  </div>
</form>
