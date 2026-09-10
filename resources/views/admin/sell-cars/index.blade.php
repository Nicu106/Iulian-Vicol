@extends('layouts.ad')

@section('title', 'Te quieren vender — IV MOTORCLASS')

@section('content')

<div class="ad-head">
  <div class="ad-head__t">
    <h1 class="ad-h1">Te quieren vender</h1>
    <p class="ad-head__p">Lo que llega del formulario «Vende tu coche».</p>
  </div>
</div>

@if($vehicles->isNotEmpty())
  <ul class="ad-list">
    @foreach($vehicles as $v)
      <li>
        <article class="ad-car">
          <span class="ad-car__shot">
            @if($v->cover_image)
              <x-img :src="$v->cover_image" :alt="trim($v->brand . ' ' . $v->model)"
                     sizes="(min-width:900px) 112px, 88px" :max="320" :fallback="320" />
            @endif
          </span>
          <div class="ad-car__body">
            <h2 class="ad-car__n"><a href="{{ route('admin.sell-cars.show', $v) }}">{{ trim($v->brand . ' ' . $v->model . ' ' . $v->year) }}</a></h2>
            <p class="ad-car__f">
              {{ collect([$v->seller_name, $v->seller_phone, $v->created_at?->format('d/m/Y')])->filter()->implode(' · ') }}
            </p>
            <p class="ad-car__s">
              <span class="ad-chip ad-chip--{{ $v->status === 'pending' ? 'wait' : 'live' }}">{{ $v->status === 'pending' ? 'Sin revisar' : ucfirst($v->status) }}</span>
              <span class="ad-chip">{{ count($v->images ?: []) }} fotos</span>
            </p>
          </div>
          <p class="ad-car__p">{{ $v->price ? number_format((int) $v->price, 0, ',', '.') . ' €' : '—' }}</p>
          <div class="ad-car__act">
            <a class="ad-btn ad-btn--q ad-btn--s" href="{{ route('admin.sell-cars.show', $v) }}">Ver</a>
          </div>
        </article>
      </li>
    @endforeach
  </ul>
@else
  <div class="ad-empty">
    <p class="ad-empty__t">Nadie te ha ofrecido un coche todavía</p>
    <p class="ad-empty__p">Cuando alguien rellene el formulario de «Vende tu coche», aparecerá
      aquí con sus fotos y su teléfono.</p>
  </div>
@endif

@endsection
