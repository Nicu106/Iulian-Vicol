@extends('muestras.layout')
@section('title', 'Opiniones: diez propuestas · IV MOTORCLASS')
@section('body', 'sh-body')
@section('content')
{{-- The showroom. Internal: a link sent to the client so he can choose how
     the reviews are shown. Each proposal is the real page
     (/muestras/opiniones/{n}?embed=1) in a frame as tall as its content —
     no scroll inside a scroll: the child reports its height by postMessage.
     On a phone the frame is the full width and there is no toggle (it IS a
     phone). On a computer: a phone or a tablet at their real widths, or the
     full width. Frames load only as they come near the screen. --}}
<header class="sh-top">
  <div class="ms-wrap">
    <p class="sh-brand">IV&nbsp;MOTORCLASS</p>
    <h1 class="sh-h1">Opiniones de clientes: diez propuestas</h1>
    <p class="sh-lede">Las diez usan las {{ $count }} opiniones reales de la web, con sus fotos enteras.
      En el teléfono se ven tal cual las vería un cliente. En el ordenador, cada una se puede ver
      como en un teléfono, una tablet o a todo lo ancho, o abrir a pantalla completa.</p>
  </div>
</header>

<nav class="sh-index" aria-label="Propuestas">
  <ol class="sh-index__l">
    @foreach($variants as $n => [$name])
      <li><a class="sh-index__a" href="#p{{ $n }}"><span class="sh-n">{{ $n }}</span>{{ $name }}</a></li>
    @endforeach
  </ol>
</nav>

<main>
{{-- The ten at a glance: each one's first phone screen (a still taken by
     the verification script, public/img/muestras/), a touch away from the
     real thing below. --}}
<section class="sh-over" aria-labelledby="sh-over-h">
  <div class="ms-wrap">
    <h2 class="sh-h3" id="sh-over-h">Las diez, como se ven en el teléfono</h2>
  </div>
  <ol class="sh-over__l">
    @foreach($variants as $n => [$name])
      @php $th = 'img/muestras/opiniones-' . $n . '.webp'; @endphp
      <li><a class="sh-over__a" href="#p{{ $n }}">
        <img src="{{ asset($th) }}?v={{ @filemtime(public_path($th)) ?: 1 }}" alt="" width="390" height="780" loading="lazy" decoding="async">
        <span class="sh-over__n"><span class="sh-n">{{ $n }}</span>{{ $name }}</span>
      </a></li>
    @endforeach
  </ol>
</section>

{{-- Two side by side, each as a phone shows it. A computer only: a phone
     has no room for two phones. --}}
<section class="sh-cmp" aria-labelledby="sh-cmp-h">
  <div class="ms-wrap">
    <h2 class="sh-h3" id="sh-cmp-h">Comparar dos</h2>
    <div class="sh-cmp__pick">
      @foreach(['a' => 1, 'b' => 5] as $k => $def)
        <label class="sh-cmp__sel"><span class="ms-sr">{{ $k === 'a' ? 'Primera propuesta' : 'Segunda propuesta' }}</span>
          <select data-cmp="{{ $k }}">
            @foreach($variants as $n => [$name])
              <option value="{{ $n }}" @selected($n === $def)>{{ $n }} · {{ $name }}</option>
            @endforeach
          </select>
        </label>
      @endforeach
    </div>
  </div>
  <div class="sh-cmp__stage">
    @foreach(['a' => 1, 'b' => 5] as $k => $def)
      <div class="sh-dev sh-dev--390">
        <iframe class="sh-frame" data-cmp-frame="{{ $k }}" src="{{ route('muestras.opiniones.show', $def) }}?embed=1"
                title="Comparar: {{ $variants[$def][0] }}" height="800" loading="lazy" style="width:390px"></iframe>
      </div>
    @endforeach
  </div>
</section>

@foreach($variants as $n => [$name, $line, $fit])
  <section class="sh-v" id="p{{ $n }}" aria-labelledby="p{{ $n }}-h" data-v="{{ $n }}">
    <div class="ms-wrap sh-v__head">
      <h2 class="sh-h2" id="p{{ $n }}-h"><span class="sh-n">{{ $n }}</span>{{ $name }}</h2>
      <p class="sh-line">{{ $line }}</p>
      <p class="sh-fitl">{{ $fit }}</p>
      <div class="sh-tools">
        <fieldset class="sh-seg">
          <legend class="ms-sr">Ver como</legend>
          <label class="sh-seg__o"><input type="radio" name="dev{{ $n }}" value="390"><span>Teléfono <small>390</small></span></label>
          <label class="sh-seg__o"><input type="radio" name="dev{{ $n }}" value="768"><span>Tablet <small>768</small></span></label>
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
          <iframe class="sh-frame" src="{{ route('muestras.opiniones.show', $n) }}?embed=1" title="Propuesta {{ $n }}: {{ $name }}"
                  height="800" loading="lazy"></iframe>
        </div>
      </div>
    </div>
  </section>
@endforeach
</main>
@endsection
