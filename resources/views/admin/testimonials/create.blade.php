@extends('layouts.ad')

@section('title', 'Añadir opinión — IV MOTORCLASS')

@section('content')

<div class="ad-head">
  <div class="ad-head__t">
    <h1 class="ad-h1">Añadir una opinión</h1>
    <p class="ad-head__p">Sale en la portada, entre las demás.</p>
  </div>
  <div class="ad-head__go">
    <a class="ad-btn ad-btn--q" href="{{ route('admin.testimonials.index') }}">Volver</a>
  </div>
</div>

@include('admin.testimonials._form', ['testimonial' => null, 'action' => route('admin.testimonials.store')])

@endsection
