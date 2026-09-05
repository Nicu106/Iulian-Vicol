{{-- One car card. $car may be a Vehicle or an example array; $kind is
     'available' | 'sold' | 'demo'. --}}
@php
  $g = fn($k) => is_array($car) ? ($car[$k] ?? null) : ($car->$k ?? null);
  $img = $kind === 'demo' ? asset('storage/'.$g('img')) : $car->thumbUrl(800);
  // 'demo' is an invented example and has no page. Everything real does, sold
  // included: the photographs are the record of what he has actually delivered.
  $href = in_array($kind, ['available', 'sold'], true) ? '/coche/'.$g('slug') : null;
  $alt = $kind === 'demo' ? 'Ejemplo de ficha — Porsche '.$g('model') : trim($g('brand').' '.$g('model').' '.$g('year'));
@endphp
<article class="mc-card {{ $kind === 'sold' ? 'mc-card--sold' : '' }} {{ $kind === 'demo' ? 'mc-card--demo' : '' }}">
  @if($href)<a class="mc-card__link" href="{{ $href }}">@else<div class="mc-card__link">@endif
    <div class="mc-frame mc-frame--card">
      <img class="mc-img mc-img--vehicle" src="{{ $img }}" alt="{{ $alt }}"
           width="800" height="600" loading="lazy" decoding="async">
      @if($kind === 'sold')<span class="mc-badge mc-badge--sold">Entregado</span>@endif
      @if($kind === 'demo')<span class="mc-badge mc-badge--demo">Ejemplo</span>@endif
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
