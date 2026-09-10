@extends('layouts.ad')

@section('title', 'Coches — IV MOTORCLASS')

@section('content')

@php
  /* The controller hands out plain arrays (it can read from a JSON store as
     well as the database), so the counts are taken from the same list the page
     is about rather than from a second query that could disagree with it. */
  $all   = collect($items);
  $nLive = $all->where('status', 'available')->count();
  $nGone = $all->where('status', 'sold')->count();

  $now = $status ?: 'todos';
  $piles = [
    ['available', 'En la web', $nLive],
    ['sold',      'Vendidos',  $nGone],
    ['',          'Todos',     $all->count()],
  ];
@endphp

<div class="ad-head">
  <div class="ad-head__t">
    <h1 class="ad-h1">Coches</h1>
    <p class="ad-head__p">{{ $all->count() }} en total · {{ $nLive }} a la venta</p>
  </div>
  <div class="ad-head__go">
    <a class="ad-btn" href="{{ route('admin.vehicles.create') }}">Añadir coche</a>
  </div>
</div>

{{-- Two piles and a search box, which is the whole of it.

     What this replaces: five dropdowns (Buscar, Estado, Destacado, Ordenar,
     Precio), five bulk-action buttons, Exportar and Analíticas, and four stat
     tiles repeated from the panel — over a list of 41 cars. "Destacado" has
     never had a row to filter. The price filter offered €0-10k / 10k-25k /
     25k-50k / 50k+ for a stock whose prices run 4.650 to 56.200 with an
     average of 15.783, so two of the four buckets held nearly everything and
     one held a single car. Sorting by "Más vistos" ordered a page that has
     nine live entries.

     They are all still reachable by URL — ?featured=1, ?sort=price_asc — and
     the controller still honours them. They are simply not worth a control
     each on the screen the man opens every week. --}}
<div class="ad-seg" role="group" aria-label="Qué coches">
  @foreach($piles as [$key, $label, $n])
    <a class="ad-seg__o {{ $now === ($key ?: 'todos') ? 'is-on' : '' }}"
       href="{{ route('admin.vehicles.index', array_filter(['status' => $key, 'q' => $q])) }}"
       @if($now === ($key ?: 'todos')) aria-current="true" @endif>
      {{ $label }} <span class="ad-seg__n">{{ $n }}</span>
    </a>
  @endforeach
</div>

<form action="{{ route('admin.vehicles.index') }}" method="GET" class="ad-find">
  @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
  <label class="ad-field">
    <span class="ad-vh">Buscar por marca o modelo</span>
    <input class="ad-in" type="search" name="q" value="{{ $q }}" placeholder="Marca o modelo…">
  </label>
  <button class="ad-btn ad-btn--q" type="submit">Buscar</button>
  @if($q)
    <a class="ad-btn ad-btn--q" href="{{ route('admin.vehicles.index', array_filter(['status' => $status])) }}">Quitar</a>
  @endif
</form>

@if($all->isNotEmpty())
  <ul class="ad-list">
    @foreach($items as $car)
      <li>@include('admin.partials.car', ['car' => $car])</li>
    @endforeach
  </ul>
@else
  <div class="ad-empty">
    <p class="ad-empty__t">No hay ningún coche aquí</p>
    <p class="ad-empty__p">
      @if($q)
        Nada que coincida con «{{ $q }}».
      @else
        Añade el primero y aparecerá en el catálogo.
      @endif
    </p>
  </div>
@endif

@endsection
