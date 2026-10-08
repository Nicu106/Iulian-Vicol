@extends('muestras.layout')
@section('title', 'Opiniones: cuatro propuestas · IV MOTORCLASS')
@section('theme', '#F4F6FA')
@section('body', 'sh-body')
@section('content')
{{-- The showroom, second round (2026-10-08). Internal: a link sent to the
     client so he can choose how the reviews are shown. Each proposal is the
     real page in a frame:
       - a section that is as tall as its content (2, 4): /n?embed=1, the
         frame takes the height the proposal posts — no scroll in a scroll;
       - a pinned scroll sequence (1, 3): /n?embed=2, a frame of a screen's
         height that scrolls on its own, which is the sequence exactly as a
         visitor's screen would hold it.
     On a phone the frame is the full width and there is no toggle (it IS a
     phone). On a computer: a phone or a tablet at their real widths, or the
     full width. Frames load only as they come near the screen. --}}
@php $src = fn ($n) => route('muestras.opiniones.show', $n) . '?embed=' . (in_array($n, $pinned, true) ? 2 : 1); @endphp
<header class="sh-top">
  <div class="ms-wrap">
    <p class="sh-brand">IV&nbsp;MOTORCLASS</p>
    <h1 class="sh-h1">Opiniones de clientes: cuatro propuestas</h1>
    <p class="sh-lede">Tres ideas nuevas y la tuya, con las {{ $count }} opiniones reales de la web y sus fotos enteras.
      En el teléfono se ven tal cual las vería un cliente. En el ordenador, cada una se puede ver
      como en un teléfono, una tablet o a todo lo ancho, o abrir a pantalla completa.</p>
  </div>
</header>

<nav class="sh-index" aria-label="Propuestas">
  <ol class="sh-index__l">
    @foreach($variants as $n => [$name])
      <li><a class="sh-index__a" href="#p{{ $n }}">{{ $name }}</a></li>
    @endforeach
  </ol>
</nav>

<main>
{{-- The four at a glance: each one's first phone screen (a still taken by
     the verification script, public/img/muestras/), a touch away from the
     real thing below. --}}
<section class="sh-over" aria-labelledby="sh-over-h">
  <div class="ms-wrap">
    <h2 class="sh-h3" id="sh-over-h">Las cuatro, como se ven en el teléfono</h2>
  </div>
  <ol class="sh-over__l">
    @foreach($variants as $n => [$name])
      @php $th = 'img/muestras/opiniones-' . $n . '.webp'; @endphp
      <li><a class="sh-over__a" href="#p{{ $n }}">
        <img src="{{ asset($th) }}?v={{ @filemtime(public_path($th)) ?: 1 }}" alt="" width="390" height="780" loading="lazy" decoding="async">
        <span class="sh-over__n">{{ $name }}</span>
      </a></li>
    @endforeach
  </ol>
</section>

{{-- Two side by side, each as a phone shows it. A computer only. --}}
<section class="sh-cmp" aria-labelledby="sh-cmp-h">
  <div class="ms-wrap">
    <h2 class="sh-h3" id="sh-cmp-h">Comparar dos</h2>
    <div class="sh-cmp__pick">
      @foreach(['a' => 4, 'b' => 1] as $k => $def)
        <label class="sh-cmp__sel"><span class="ms-sr">{{ $k === 'a' ? 'Primera propuesta' : 'Segunda propuesta' }}</span>
          <select data-cmp="{{ $k }}">
            @foreach($variants as $n => [$name])
              <option value="{{ $n }}" data-pinned="{{ in_array($n, $pinned, true) ? 1 : 0 }}" @selected($n === $def)>{{ $name }}</option>
            @endforeach
          </select>
        </label>
      @endforeach
    </div>
  </div>
  <div class="sh-cmp__stage">
    @foreach(['a' => 4, 'b' => 1] as $k => $def)
      <div class="sh-dev sh-dev--390">
        <iframe class="sh-frame" data-cmp-frame="{{ $k }}" src="{{ $src($def) }}"
                title="Comparar: {{ $variants[$def][0] }}" height="{{ in_array($def, $pinned, true) ? 760 : 800 }}" loading="lazy" style="width:390px"></iframe>
      </div>
    @endforeach
  </div>
</section>

@foreach($variants as $n => [$name, $line, $fit])
  @php $pin = in_array($n, $pinned, true); @endphp
  <section class="sh-v{{ $pin ? ' is-pinned' : '' }}" id="p{{ $n }}" aria-labelledby="p{{ $n }}-h" data-v="{{ $n }}">
    <div class="ms-wrap sh-v__head">
      <h2 class="sh-h2" id="p{{ $n }}-h">{{ $name }}</h2>
      <p class="sh-line">{{ $line }}</p>
      <p class="sh-fitl">{{ $fit }}</p>
      @if($pin)
        <p class="sh-hint"><svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M12 4v16M6 14l6 6 6-6" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>Se recorre deslizando dentro del marco.</p>
      @endif
      <div class="sh-tools">
        <fieldset class="sh-seg">
          <legend class="ms-sr">Ver como</legend>
          <label class="sh-seg__o"><input type="radio" name="dev{{ $n }}" value="390"><span>Teléfono</span></label>
          <label class="sh-seg__o"><input type="radio" name="dev{{ $n }}" value="768"><span>Tablet</span></label>
          <label class="sh-seg__o"><input type="radio" name="dev{{ $n }}" value="full" checked><span>Ordenador</span></label>
        </fieldset>
        <a class="sh-open" href="{{ route('muestras.opiniones.show', $n) }}" target="_blank" rel="noopener">Abrir a pantalla completa
          <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M14 4h6v6M20 4l-9 9M18 14v6H4V6h6" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
          <span class="ms-sr">(se abre en otra pestaña)</span></a>
      </div>
    </div>
    <div class="sh-stage" data-stage>
      <div class="sh-fit">
        <div class="sh-dev is-bare">
          <iframe class="sh-frame" src="{{ $src($n) }}" title="Propuesta: {{ $name }}"
                  height="800" loading="lazy" @if($pin) data-pinned @endif></iframe>
        </div>
      </div>
    </div>
  </section>
@endforeach
</main>
@endsection
