@extends('muestras.layout')
@section('title', 'Opiniones · ' . $variant[0])
@section('content')
{{-- 7 · Foto fija. On a computer the photograph holds still on the left and
     changes, with a slow crossfade, to the customer whose words are crossing
     the middle of the screen on the right; the review being read is in full
     ink, the others a step lighter. On a phone there is no room for a column
     that stays: each photograph comes back above its own words. --}}
<main class="ms-sec ms-v7" aria-labelledby="h-op">
  @include('muestras._head')
  <div class="ms-wrap ms-split" data-split>
    <div class="ms-split__stick" aria-hidden="true">
      <div class="ms-split__frame">
        @foreach($reviews as $i => $r)
          <img class="ms-split__p {{ $i === 0 ? 'is-on' : '' }}" data-for="{{ $r->id }}"
               src="{{ \App\Support\Img::url($r->img, 1080) ?? $r->img }}"
               srcset="{{ \App\Support\Img::srcset($r->img, 1600) }}"
               sizes="(min-width:1264px) 470px, 38vw" width="{{ $r->w }}" height="{{ $r->h }}"
               alt="" loading="lazy" decoding="async">
        @endforeach
      </div>
    </div>
    <ul class="ms-split__list" role="list" id="split-list">
      @foreach($reviews as $i => $r)
        <li class="ms-split__i {{ $i === 0 ? 'is-active' : '' }} {{ $i >= 3 ? 'is-later-m' : '' }} {{ $i >= 8 ? 'is-later' : '' }}" data-id="{{ $r->id }}" tabindex="-1">
          <figure class="ms-split__f">
            @include('muestras._ph', ['r' => $r, 'cls' => 'ms-split__inl', 'sizes' => 'calc(100vw - 32px)', 'max' => 1080])
            <figcaption>
              @include('muestras._q', ['r' => $r, 'limit' => 420, 'limitM' => 170, 'key' => 'f'])
              <p class="ms-by">{{ $r->caption }}</p>
            </figcaption>
          </figure>
        </li>
      @endforeach
    </ul>
    @if($reviews->count() > 3)
      <p class="ms-reveal ms-split__more"><button class="mc-btn mc-btn--outline" type="button" data-reveal="split-list">Ver todas las opiniones</button></p>
    @endif
  </div>
</main>
@endsection
