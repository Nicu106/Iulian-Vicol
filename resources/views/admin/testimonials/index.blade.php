@extends('layouts.ad')

@section('title', 'Opiniones — IV MOTORCLASS')

@section('content')

<div class="ad-head">
  <div class="ad-head__t">
    <h1 class="ad-h1">Opiniones</h1>
    <p class="ad-head__p">{{ $testimonials->total() }} en total. Salen en la portada, con su foto entera.</p>
  </div>
  <div class="ad-head__go">
    <a class="ad-btn" href="{{ route('admin.testimonials.create') }}">Añadir opinión</a>
  </div>
</div>

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
              <span class="ad-chip {{ $t->is_active ? 'ad-chip--live' : '' }}">{{ $t->is_active ? 'En la portada' : 'Oculta' }}</span>
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

@endsection
