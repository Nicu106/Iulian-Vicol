@extends('layouts.ad')

@section('title', trim($vehicle->brand . ' ' . $vehicle->model) . ' — te lo quieren vender')

@section('content')

@php
  $shots = $vehicle->images ?: [];
  $facts = array_filter([
    'Año'          => $vehicle->year,
    'Kilómetros'   => $vehicle->mileage ? number_format((int) $vehicle->mileage, 0, ',', '.') . ' km' : null,
    'Combustible'  => $vehicle->fuel ?? $vehicle->fuel_type,
    'Cambio'       => $vehicle->transmission,
    'Motor'        => $vehicle->engine,
    'Potencia'     => $vehicle->power,
    'Color'        => $vehicle->color,
    'Carrocería'   => $vehicle->body_type,
  ]);
@endphp

<div class="ad-head">
  <div class="ad-head__t">
    <h1 class="ad-h1">{{ trim($vehicle->brand . ' ' . $vehicle->model . ' ' . $vehicle->year) }}</h1>
    <p class="ad-head__p">Llegó el {{ $vehicle->created_at?->format('d/m/Y') }} · {{ count($shots) }} fotos</p>
  </div>
  <div class="ad-head__go">
    <a class="ad-btn ad-btn--q" href="{{ route('admin.sell-cars.index') }}">Volver</a>
  </div>
</div>

{{-- Who to call, first: this page exists so he can pick up the phone. --}}
<section class="ad-panel">
  <div class="ad-panel__h"><h2>Quién te lo vende</h2></div>
  <ul class="ad-facts">
    <li>
      <span class="ad-fact__k">Nombre</span>
      <span class="ad-fact__v" style="font-size:var(--t-h3)">{{ $vehicle->seller_name ?: '—' }}</span>
    </li>
    @if($vehicle->seller_phone)
      <li>
        <a class="ad-fact" href="https://wa.me/{{ preg_replace('~\D~', '', $vehicle->seller_phone) }}">
          <span class="ad-fact__k">Teléfono</span>
          <span class="ad-fact__v" style="font-size:var(--t-h3)">{{ $vehicle->seller_phone }}</span>
          <span class="ad-fact__n">WhatsApp</span>
        </a>
      </li>
    @endif
    @if($vehicle->seller_email)
      <li>
        <a class="ad-fact" href="mailto:{{ $vehicle->seller_email }}">
          <span class="ad-fact__k">Correo</span>
          <span class="ad-fact__v" style="font-size:var(--t-h3);overflow-wrap:anywhere">{{ $vehicle->seller_email }}</span>
        </a>
      </li>
    @endif
    <li>
      <span class="ad-fact__k">Pide</span>
      <span class="ad-fact__v" style="color:var(--mc-price)">{{ $vehicle->price ? number_format((int) $vehicle->price, 0, ',', '.') . ' €' : '—' }}</span>
    </li>
  </ul>
</section>

@if($shots)
  <section class="ad-sec">
    <h2 class="ad-h2">Las fotos <span class="ad-seg__n">{{ count($shots) }}</span></h2>
    <ul class="ad-shots">
      @foreach($shots as $i => $url)
        <li class="ad-shot">
          <img class="ad-shot__i" src="{{ $url }}" alt="Foto {{ $i + 1 }}" loading="lazy" decoding="async">
          <span class="ad-shot__n">{{ $i + 1 }}</span>
        </li>
      @endforeach
    </ul>
    <p class="ad-note"><a href="{{ route('admin.sell-cars.download-photos', $vehicle) }}">Descargar todas</a></p>
  </section>
@endif

@if($facts)
  <section class="ad-sec">
    <h2 class="ad-h2">Lo que dice del coche</h2>
    <div class="ad-panel">
      <dl class="ad-pairs">
        @foreach($facts as $k => $v)
          <div><dt>{{ $k }}</dt><dd>{{ $v }}</dd></div>
        @endforeach
      </dl>
    </div>
  </section>
@endif

@if($vehicle->description)
  <section class="ad-sec">
    <h2 class="ad-h2">Lo que cuenta</h2>
    <div class="ad-panel"><p class="ad-msg__body">{{ $vehicle->description }}</p></div>
  </section>
@endif

<section class="ad-sec">
  <div class="ad-go" style="border-top:0;padding-top:0">
    <form action="{{ route('admin.sell-cars.approve', $vehicle) }}" method="POST">
      @csrf
      <button class="ad-btn" type="submit">Aceptar y publicarlo</button>
    </form>
    <a class="ad-btn ad-btn--q" href="{{ route('admin.sell-cars.edit', $vehicle) }}">Editar antes</a>
    <form action="{{ route('admin.sell-cars.reject', $vehicle) }}" method="POST">
      @csrf
      <button class="ad-btn ad-btn--q" type="submit">Rechazar</button>
    </form>
  </div>
</section>

<form class="ad-danger" action="{{ route('admin.sell-cars.destroy', $vehicle) }}" method="POST"
      onsubmit="return confirm('Se borra la oferta y sus fotos. ¿Seguro?')">
  @csrf
  @method('DELETE')
  <button class="ad-btn ad-btn--bad ad-btn--s" type="submit">Borrar la oferta</button>
</form>

@endsection
