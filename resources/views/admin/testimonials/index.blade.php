@extends('layouts.ad')

@section('title', 'Opiniones — IV MOTORCLASS')

@section('content')

<div class="ad-head">
  <div class="ad-head__t">
    <h1 class="ad-h1">Opiniones</h1>
    <p class="ad-head__p">{{ $testimonials->total() }} en total. Salen en la portada, con su foto entera.</p>
  </div>
  <div class="ad-head__go">
    {{-- with the links ready, a window: "La escribo yo" or "Le mando un enlace" (_add) --}}
    <a class="ad-btn" href="{{ route('admin.testimonials.create') }}" @if(\App\Models\ReviewLink::ready()) data-rv-add aria-haspopup="dialog" @endif>Añadir opinión</a>
  </div>
</div>

@if($links->isNotEmpty())
  {{-- Links sent and not yet answered. "Ver el QR" opens the window on it again;
       "Cerrar" stops anyone writing through it. --}}
  <section class="ad-rvl" aria-labelledby="ad-rvl-h">
    <h2 class="ad-h2" id="ad-rvl-h">{{ $links->count() === 1 ? 'Un enlace abierto' : $links->count() . ' enlaces abiertos' }}</h2>
    <ul class="ad-rvl__list">
      @foreach($links as $l)
        <li class="ad-rvl__i">
          <span class="ad-rvl__who">{{ $l->name ?: 'Sin nombre' }}</span>
          <span class="ad-rvl__when">Abierto hasta el {{ $l->until() }}</span>
          <span class="ad-rvl__act">
            <button class="ad-btn ad-btn--q ad-btn--s" type="button" data-rv-show="{{ route('admin.review-links.show', $l) }}">Ver el QR</button>
            <form method="post" action="{{ route('admin.review-links.close', $l) }}">
              @csrf
              <button class="ad-btn ad-btn--bad ad-btn--s" type="submit">Cerrar</button>
            </form>
          </span>
        </li>
      @endforeach
    </ul>
  </section>
@endif

@if($testimonials->isNotEmpty())
  {{-- The quote was a single table cell with white-space:nowrap and an
       ellipsis, so every opinion showed its first eight words and the whole
       point of the page — reading what people wrote — was not on it. --}}
  <ul class="ad-list">
    @foreach($testimonials as $t)
      <li>
        <article class="ad-say">
          @if($t->image_path)
            <img class="ad-say__i" src="{{ $t->image_path }}" alt="Foto de {{ $t->author_name }}" loading="lazy" decoding="async">
          @endif
          <div class="ad-say__b">
            <div class="ad-msg__h">
              <span class="ad-msg__who">{{ $t->author_name }}</span>
              {{-- one the customer wrote through a link waits for him to read it --}}
              @if(!$t->is_active && isset($viaLink[$t->id]))
                <span class="ad-chip ad-chip--wait">Nueva · la escribió el cliente</span>
              @else
                <span class="ad-chip {{ $t->is_active ? 'ad-chip--live' : '' }}">{{ $t->is_active ? 'En la portada' : 'Oculta' }}</span>
              @endif
              <span class="ad-msg__when">Orden {{ $t->order_index }}</span>
            </div>
            <p class="ad-msg__body">{{ $t->quote }}</p>
            <div class="ad-msg__act">
              <a class="ad-btn ad-btn--q ad-btn--s" href="{{ route('admin.testimonials.edit', $t) }}">Editar</a>
            </div>
          </div>
        </article>
      </li>
    @endforeach
  </ul>

  @if($testimonials->hasPages())
    <nav class="ad-pages" aria-label="Páginas">
      @foreach($testimonials->getUrlRange(1, $testimonials->lastPage()) as $n => $url)
        @if($n === $testimonials->currentPage())
          <span class="is-on" aria-current="page">{{ $n }}</span>
        @else
          <a href="{{ $url }}">{{ $n }}</a>
        @endif
      @endforeach
    </nav>
  @endif
@else
  <div class="ad-empty">
    <p class="ad-empty__t">Todavía no hay opiniones</p>
    <p class="ad-empty__p">La portada enseña esta sección sólo cuando hay algo que enseñar.</p>
  </div>
@endif

@if(\App\Models\ReviewLink::ready())
  @include('admin.testimonials._add')
@endif

@endsection
