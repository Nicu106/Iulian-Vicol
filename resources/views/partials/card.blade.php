{{-- One car card. $car is a Vehicle; $kind is 'available' | 'sold'. A marque
     with nothing to show uses partials/card-soon instead. --}}
@php
  $g = fn($k) => is_array($car) ? ($car[$k] ?? null) : ($car->$k ?? null);
  // The path, not a built URL: <x-img> needs the source to derive a srcset from.
  $imgPath = $car->primary_image;
  // Every card has a page, sold included: the photographs are the record of what
  // he has actually delivered.
  $href = '/coche/'.$g('slug');
  $alt = trim($g('brand').' '.$g('model').' '.$g('year'));
@endphp
<article class="mc-card {{ $kind === 'sold' ? 'mc-card--sold' : '' }}">
  @if($href)<a class="mc-card__link" href="{{ $href }}">@else<div class="mc-card__link">@endif
    <div class="mc-frame mc-frame--card">
      {{-- Measured across the column ladder: 228px at 320, 279 at 390, 302 at
           768 (two up), 202-210 from 900 (three and four up). It was fetching one
           800px file for all of them. --}}
      <x-img class="mc-img mc-img--vehicle" :src="$imgPath" :alt="$alt"
             sizes="(min-width:900px) 210px, (min-width:560px) 40vw, 72vw"
             :max="720" :fallback="480" />
      @if($kind === 'sold')<span class="mc-badge mc-badge--sold">Entregado</span>@endif
    </div>
    <div class="mc-card__body">
      <h3 class="mc-card__title">{{ $g('model') }}</h3>
      @if($sub($car))<p class="mc-card__sub">{{ $sub($car) }}</p>@endif
      <ul class="mc-chips">
        @foreach($chips($car) as $c)<li class="mc-chip">{{ $c }}</li>@endforeach
      </ul>
      <div class="mc-pair">
        <span class="mc-price">{{ $euros($g('price')) }}</span>
        @php $m = $g('mileage') ?? $g('km'); @endphp
        @if($m)<span class="mc-km">{{ $km($m) }}</span>
        @elseif($kind === 'available')<span class="mc-km is-unknown">Km sin confirmar</span>@endif
      </div>
    </div>
  @if($href)</a>@else</div>@endif
</article>
