@extends('layouts.ad')

@section('title', 'Crear enlace — IV MOTORCLASS')

@section('content')

<div class="ad-head">
  <div class="ad-head__t">
    <h1 class="ad-h1">Crear enlace</h1>
    <p class="ad-head__p">Para alguien que te ha comprado, o que quiere recomendarte.</p>
  </div>
  <div class="ad-head__go">
    <a class="ad-btn ad-btn--q" href="{{ route('admin.referrals.index') }}">Volver</a>
  </div>
</div>

@if($errors->any())
  <p class="ad-flash ad-flash--bad">Revisa {{ $errors->count() === 1 ? 'el campo marcado' : 'los campos marcados' }}.</p>
@endif

<form class="ad-form" action="{{ route('admin.referrals.store') }}" method="POST">
  @csrf
  <fieldset class="ad-fs">
    <div class="ad-grid">
      <label class="ad-field">
        <span>Nombre</span>
        <input class="ad-in {{ $errors->has('name') ? 'is-bad' : '' }}" type="text" name="name" required maxlength="120"
               value="{{ old('name', $pick?->buyer_name) }}">
        @error('name')<span class="ad-err">{{ $message }}</span>@enderror
      </label>
      <label class="ad-field">
        <span>Teléfono <em>— para enviarle el enlace por WhatsApp</em></span>
        <input class="ad-in {{ $errors->has('phone') ? 'is-bad' : '' }}" type="tel" name="phone" required maxlength="30"
               inputmode="tel" value="{{ old('phone', $pick?->buyer_phone) }}">
        @error('phone')<span class="ad-err">{{ $message }}</span>@enderror
      </label>
      <div class="ad-field ad-wide">
        <span>Coche que compró <em>— opcional</em></span>
        <select class="ad-in" name="vehicle_id">
          <option value="">Ninguno</option>
          @foreach($sold as $v)
            <option value="{{ $v->id }}" @selected((string) old('vehicle_id', $pick?->id) === (string) $v->id)>
              {{ trim($v->brand . ' ' . $v->model . ' ' . $v->year) }}
            </option>
          @endforeach
        </select>
      </div>
    </div>
    <p class="ad-note">Si eliges el coche, el nombre y el teléfono se guardan también como
      comprador de ese coche, y esa compra nunca cuenta como recomendación suya.</p>
  </fieldset>

  <div class="ad-go">
    <button class="ad-btn" type="submit">Crear enlace</button>
    <a class="ad-btn ad-btn--q" href="{{ route('admin.referrals.index') }}">Cancelar</a>
  </div>
</form>

@endsection
