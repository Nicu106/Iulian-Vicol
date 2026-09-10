@extends('layouts.ad')

@section('title', 'Opinión de ' . $testimonial->author_name . ' — IV MOTORCLASS')

@section('content')

<div class="ad-head">
  <div class="ad-head__t">
    <h1 class="ad-h1">{{ $testimonial->author_name }}</h1>
    <p class="ad-head__p">Opinión añadida el {{ $testimonial->created_at?->format('d/m/Y') }}</p>
  </div>
  <div class="ad-head__go">
    <a class="ad-btn ad-btn--q" href="{{ route('admin.testimonials.index') }}">Volver</a>
  </div>
</div>

@include('admin.testimonials._form', ['testimonial' => $testimonial, 'action' => route('admin.testimonials.update', $testimonial)])

<form class="ad-danger" action="{{ route('admin.testimonials.destroy', $testimonial) }}" method="POST"
      onsubmit="return confirm('¿Borrar la opinión de {{ $testimonial->author_name }}?')">
  @csrf
  @method('DELETE')
  <button class="ad-btn ad-btn--bad ad-btn--s" type="submit">Borrar esta opinión</button>
</form>

@endsection
