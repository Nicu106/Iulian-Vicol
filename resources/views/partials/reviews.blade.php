{{-- ============ what they wrote ============
     The home page's reviews: 4.6 "La fila", chosen in the showroom
     (/muestras/opiniones/4.6, tag opiniones-v4.6), as a site component.
     Data: App\Support\ReviewRow::rows() (every active review with a
     photograph, newest first). Style: public/css/reviews.css. Script:
     public/js/reviews.js. The heading and its sentence are the home page's own.

     Every review is a pair: the photograph as a white print, the words on a
     navy card under its corner. All of them stand in one row that is always
     wider than the screen, so the next pair always shows: the thumb, a mouse,
     the arrows or the keys move it pair by pair; nothing moves on its own.
     A tap on the card behind brings it forward; a tap on the card in front, or
     "Ver más", opens the whole review with the whole photograph.
     Without script the row still scrolls and each card holds its whole text.
     ========================================== --}}
@php
  $v = fn ($p) => asset($p) . '?v=' . (@filemtime(public_path($p)) ?: 1);
@endphp
@push('css')
<link rel="stylesheet" href="{{ $v('css/reviews.css') }}">
@endpush
<section class="rv" id="reviews" aria-labelledby="h-fb">
  <div class="rv__win">
    <div class="rv__head">
      <h2 class="hm-h2 rv__h" id="h-fb">La confianza se gana. Ellos te cuentan cómo.</h2>
      <div class="rv__nav">
        <button class="rv__arw rv__arw--prev" type="button" data-rv-step="-1" aria-label="Anteriores" aria-controls="rv-row" aria-disabled="true"></button>
        <button class="rv__arw rv__arw--next" type="button" data-rv-step="1" aria-label="Siguientes" aria-controls="rv-row"></button>
      </div>
      <p class="hm-sec__p rv__p">Conoce a quienes ya han comprado su coche en IV Motorclass. Estas son
        sus fotos el día de la entrega y sus opiniones sobre la experiencia.</p>
    </div>
    <div class="rv__row" id="rv-row" tabindex="0" role="region" aria-label="Lo que escribieron los clientes" data-rv-row>
      <ul class="rv__list" role="list">
        @foreach($reviews as $r)
          <li class="rv__i" data-id="{{ $r->id }}" aria-label="{{ $r->name }}" style="--card:{{ $r->card }}">
            <div class="rv__pair">
              <button class="rv__ph" type="button" aria-haspopup="dialog"
                      data-front="Ver entera la foto y la opinión de {{ $r->name }}"
                      data-back="Traer delante la foto de {{ $r->name }}"
                      aria-label="Ver entera la foto y la opinión de {{ $r->name }}">
                <span class="rv__mat rv__mat--{{ $r->fit }}" style="--r:{{ $r->ratio }}@if($r->amb);--amb:url('{{ $r->amb }}')@endif">
                  <x-img :src="$r->img" alt="" :sizes="'(min-width:1000px) 208px, (min-width:700px) 182px, 168px'" :max="720" class="rv__img"
                         style=";object-position:{{ $r->fx }}% {{ $r->fy }}%" />
                </span>
              </button>
              <div class="rv__q">
                <button class="rv__swap" type="button" aria-pressed="false"
                        data-front="Leer entera la opinión de {{ $r->name }}"
                        data-back="Traer delante la opinión de {{ $r->name }}"
                        aria-label="Traer delante la opinión de {{ $r->name }}"></button>
                <div class="rv__body">
                  <p class="rv__qt">{{ $r->text }}</p>
                  <p class="rv__by">{{ $r->name }}</p>
                  <button class="rv__more" type="button" aria-haspopup="dialog" tabindex="-1">Ver más<span class="mc-vh">: {{ $r->name }}</span></button>
                </div>
                {{-- without the script: the whole review, scrolling inside the card --}}
                <div class="rv__all">@foreach($r->paras as $p)<p>{{ $p }}</p>@endforeach</div>
              </div>
            </div>
          </li>
        @endforeach
      </ul>
    </div>
    {{-- where the row is: the part in view, along all of it --}}
    <div class="rv__bar" aria-hidden="true"><i></i></div>
  </div>
</section>
{{-- the whole review, with the whole photograph --}}
<dialog class="rv-ov" id="rv-ov" aria-labelledby="rv-ov-by">
  <div class="rv-ov__dim" aria-hidden="true"></div>
  <div class="rv-ov__panel">
    <div class="rv-ov__ph"><img alt="" decoding="async"></div>
    <div class="rv-ov__txt">
      <blockquote class="rv-ov__q"></blockquote>
      <p class="rv-ov__by" id="rv-ov-by"></p>
    </div>
  </div>
  <button class="rv-x" type="button" aria-label="Cerrar">&times;</button>
</dialog>
<script type="application/json" id="rv-data">{!! \App\Support\ReviewRow::json($reviews) !!}</script>
@push('js')
<script src="{{ $v('js/reviews.js') }}" defer></script>
@endpush
