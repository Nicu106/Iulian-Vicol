{{-- One car, in a list. Used by the Panel and by Coches, so a change to what a
     car looks like happens once.

     $car is a Vehicle model OR the array the vehicle list hands out (that
     controller can read from a JSON store as well as the database, and returns
     plain arrays), so every read goes through $g().

     The photograph slot is 88px on a phone and 112px from 900. Measured, and
     passed to <x-img> as such: the old list asked for the full-size file and
     drew it at 100px. --}}
@php
  $g = fn ($k, $d = null) => is_array($car) ? ($car[$k] ?? $d) : ($car->$k ?? $d);

  $slug  = $g('slug');
  $name  = trim($g('brand') . ' ' . $g('model') . ' ' . $g('year'));
  $state = $g('status', 'available');
  $gone  = $state === 'sold';

  $shot = is_array($car)
    ? ($g('cover_image') ?: (($g('gallery_images') ?: [null])[0] ?? null))
    : $car->primary_image;

  $shots = count($g('gallery_images') ?: []);

  $facts = array_filter([
    $g('year'),
    $g('mileage') ? number_format((int) $g('mileage'), 0, ',', '.') . ' km' : null,
    $g('fuel') ?: $g('fuel_type'),
    $g('transmission'),
  ]);

  $chip = match ($state) {
    'sold'     => ['gone', 'Vendido'],
    'reserved' => ['wait', 'Reservado'],
    'pending'  => ['wait', 'Pendiente'],
    'draft'    => ['bad',  'Borrador'],
    default    => ['live', 'En la web'],
  };
@endphp

<article class="ad-car {{ $gone ? 'ad-car--gone' : '' }}">
  <span class="ad-car__shot">
    @if($shot)
      <x-img :src="$shot" :alt="$name" sizes="(min-width:900px) 112px, 88px" :max="320" :fallback="320" />
    @endif
  </span>

  <div class="ad-car__body">
    <h3 class="ad-car__n">
      <a href="{{ route('admin.vehicles.edit', $slug) }}">{{ $name ?: 'Sin nombre' }}</a>
    </h3>
    @if($facts)
      <p class="ad-car__f">{{ implode(' · ', $facts) }}</p>
    @endif
    <p class="ad-car__s">
      <span class="ad-chip ad-chip--{{ $chip[0] }}">{{ $chip[1] }}</span>
      {{-- The number of photographs is the one fact that decides whether the
           advert is finished, so it is on the row rather than one page in. --}}
      <span class="ad-chip">{{ $shots }} {{ $shots === 1 ? 'foto' : 'fotos' }}</span>
    </p>
  </div>

  <p class="ad-car__p">{{ $g('price') ? number_format((int) $g('price'), 0, ',', '.') . ' €' : '—' }}</p>

  <div class="ad-car__act">
    <a class="ad-btn ad-btn--q ad-btn--s" href="{{ route('admin.vehicles.edit', $slug) }}">Editar</a>
    <a class="ad-btn ad-btn--q ad-btn--s" href="{{ url('/coche/' . $slug) }}">Ver ficha</a>
    {{-- Selling is the second most frequent thing that happens to a car here.
         It used to mean opening the edit form and finding "Estado" among
         seventeen fields. --}}
    <form action="{{ route('admin.vehicles.status', $slug) }}" method="POST">
      @csrf
      <input type="hidden" name="status" value="{{ $gone ? 'available' : 'sold' }}">
      <button class="ad-btn ad-btn--q ad-btn--s" type="submit">
        {{ $gone ? 'Volver a la venta' : 'Marcar vendido' }}
      </button>
    </form>
  </div>
</article>
