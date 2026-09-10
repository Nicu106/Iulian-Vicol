@extends('layouts.ad')

@section('title', 'Editar la oferta — IV MOTORCLASS')

@section('content')

@php
  $f = fn ($k, $d = '') => old($k, $vehicle->$k ?? $d);
  $fuels = \App\Http\Controllers\SellCarController::FUEL;
  $gears = \App\Http\Controllers\SellCarController::GEAR;
@endphp

<div class="ad-head">
  <div class="ad-head__t">
    <h1 class="ad-h1">Editar la oferta</h1>
    <p class="ad-head__p">Corrige lo que haga falta antes de publicarla.</p>
  </div>
  <div class="ad-head__go">
    <a class="ad-btn ad-btn--q" href="{{ route('admin.sell-cars.show', $vehicle) }}">Volver</a>
  </div>
</div>

@if($errors->any())
  <div class="ad-flash ad-flash--bad"><span>Revisa los campos marcados.</span></div>
@endif

<form class="ad-form" action="{{ route('admin.sell-cars.update', $vehicle) }}" method="POST" enctype="multipart/form-data">
  @csrf
  @method('PUT')

  <fieldset class="ad-fs">
    <h2 class="ad-fs__h">El coche</h2>
    <div class="ad-grid">
      <label class="ad-field ad-wide"><span>Título</span>
        <input class="ad-in {{ $errors->has('title') ? 'is-bad' : '' }}" type="text" name="title" value="{{ $f('title') }}" required>
        @error('title')<span class="ad-err">{{ $message }}</span>@enderror</label>
      <label class="ad-field"><span>Marca</span><input class="ad-in" type="text" name="brand" value="{{ $f('brand') }}" required></label>
      <label class="ad-field"><span>Modelo</span><input class="ad-in" type="text" name="model" value="{{ $f('model') }}" required></label>
      <label class="ad-field"><span>Año</span><input class="ad-in" type="number" name="year" value="{{ $f('year') }}" min="1990" max="{{ date('Y') }}" required></label>
      <label class="ad-field"><span>Precio</span>
        <span class="ad-unit"><input class="ad-in" type="number" name="price" value="{{ $f('price') }}" min="0" required><span class="ad-unit__u">€</span></span></label>
      <label class="ad-field"><span>Kilómetros</span>
        <span class="ad-unit"><input class="ad-in" type="number" name="mileage" value="{{ $f('mileage') }}" min="0" required><span class="ad-unit__u">km</span></span></label>
      <label class="ad-field"><span>Carrocería</span><input class="ad-in" type="text" name="body_type" value="{{ $f('body_type') }}" required></label>
      <label class="ad-field"><span>Color</span><input class="ad-in" type="text" name="color" value="{{ $f('color') }}" required></label>
      <label class="ad-field"><span>Cilindrada</span>
        <span class="ad-unit"><input class="ad-in" type="number" name="engine_capacity" value="{{ $f('engine_capacity') }}" min="0" required><span class="ad-unit__u">cc</span></span></label>
      <label class="ad-field"><span>Potencia</span>
        <span class="ad-unit"><input class="ad-in" type="number" name="power" value="{{ $f('power') }}" min="0" required><span class="ad-unit__u">CV</span></span></label>
    </div>

    <div class="ad-field" style="margin-top:var(--s-4)">
      <span>Combustible</span>
      <div class="ad-pills">
        @foreach($fuels as $x)
          <label class="ad-pill"><input type="radio" name="fuel_type" value="{{ $x }}" @checked($f('fuel_type') === $x) required><span>{{ $x }}</span></label>
        @endforeach
      </div>
      @error('fuel_type')<span class="ad-err">{{ $message }}</span>@enderror
    </div>

    <div class="ad-field" style="margin-top:var(--s-4)">
      <span>Cambio</span>
      <div class="ad-pills">
        @foreach($gears as $x)
          <label class="ad-pill"><input type="radio" name="transmission" value="{{ $x }}" @checked($f('transmission') === $x) required><span>{{ $x }}</span></label>
        @endforeach
      </div>
      @error('transmission')<span class="ad-err">{{ $message }}</span>@enderror
    </div>
  </fieldset>

  <fieldset class="ad-fs">
    <h2 class="ad-fs__h">Lo que cuenta</h2>
    <label class="ad-field">
      <span class="ad-vh">Descripción</span>
      <textarea class="ad-in {{ $errors->has('description') ? 'is-bad' : '' }}" name="description" rows="8" maxlength="2000" required>{{ $f('description') }}</textarea>
      @error('description')<span class="ad-err">{{ $message }}</span>@enderror
    </label>
  </fieldset>

  <fieldset class="ad-fs">
    <h2 class="ad-fs__h">Quién lo vende</h2>
    <div class="ad-grid">
      <label class="ad-field"><span>Nombre</span><input class="ad-in" type="text" name="seller_name" value="{{ $f('seller_name') }}" required></label>
      <label class="ad-field"><span>Teléfono</span><input class="ad-in" type="tel" name="seller_phone" value="{{ $f('seller_phone') }}" maxlength="20" required></label>
      <label class="ad-field ad-wide"><span>Correo</span><input class="ad-in" type="email" name="seller_email" value="{{ $f('seller_email') }}" required></label>
    </div>
  </fieldset>

  <fieldset class="ad-fs">
    <h2 class="ad-fs__h">Añadir fotos</h2>
    <label class="ad-drop">
      <span class="ad-drop__t">Elegir fotos</span>
      <span class="ad-drop__n">Se suman a las que ya mandó</span>
      <input type="file" name="images[]" accept="image/*" multiple>
    </label>
    @error('images.*')<p class="ad-err">{{ $message }}</p>@enderror
  </fieldset>

  <div class="ad-go">
    <button class="ad-btn" type="submit">Guardar</button>
    <a class="ad-btn ad-btn--q" href="{{ route('admin.sell-cars.show', $vehicle) }}">Cancelar</a>
  </div>
</form>

@endsection
