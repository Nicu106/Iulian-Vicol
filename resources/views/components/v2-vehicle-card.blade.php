@props(['vehicle'])
@php
  $v      = $vehicle;
  $title  = $v->title ?? trim($v->brand.' '.$v->model.' '.$v->year);
  $thumb  = $v->thumbUrl(600);
  $srcset = $v->thumbSrcset();
  $isSold = $v->status === 'sold';
  $hasOffer = $v->has_offer && $v->offer_price && $v->offer_price < ($v->original_price ?: $v->price);
  $price  = $hasOffer ? $v->offer_price : $v->price;
@endphp
<article class="v2-card v2-vcard">
  <a href="{{ route('vehicle.show', $v->slug) }}" class="v2-vcard__media" aria-label="{{ $title }}">
    @if($thumb)
      <img src="{{ $thumb }}" @if($srcset) srcset="{{ $srcset }}" sizes="(min-width:992px) 25vw, (min-width:576px) 50vw, 100vw" @endif
           alt="{{ $title }}" loading="lazy" decoding="async">
    @else
      <span class="v2-vcard__noimg"><i class="bi bi-car-front"></i></span>
    @endif

    <span class="v2-vcard__tags">
      @if($isSold)<span class="v2-pill v2-pill--accent">Vendido</span>@endif
      @if($hasOffer)<span class="v2-pill v2-pill--accent">Oferta</span>@endif
      @if($v->featured)<span class="v2-pill v2-pill--brand">Destacado</span>@endif
    </span>
  </a>

  <div class="v2-card__body">
    <h3 class="v2-vcard__title">
      <a href="{{ route('vehicle.show', $v->slug) }}">{{ $title }}</a>
    </h3>

    <ul class="v2-vcard__specs">
      @if($v->year)<li><i class="bi bi-calendar3"></i>{{ $v->year }}</li>@endif
      @if($v->mileage)<li><i class="bi bi-speedometer2"></i>{{ number_format($v->mileage, 0, ',', '.') }} km</li>@endif
      @if($v->fuel ?? $v->fuel_type)<li><i class="bi bi-fuel-pump"></i>{{ $v->fuel ?? $v->fuel_type }}</li>@endif
      @if($v->transmission)<li><i class="bi bi-gear"></i>{{ $v->transmission }}</li>@endif
    </ul>

    <div class="v2-vcard__foot">
      <div class="v2-vcard__price">
        @if($hasOffer)
          <span class="v2-vcard__was">€ {{ number_format($v->original_price ?: $v->price, 0, ',', '.') }}</span>
        @endif
        <strong>€ {{ number_format($price, 0, ',', '.') }}</strong>
      </div>
      <a href="{{ route('vehicle.show', $v->slug) }}" class="v2-btn v2-btn--outline">Ver ficha</a>
    </div>
  </div>
</article>
