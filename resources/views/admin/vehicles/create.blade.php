@extends('layouts.ad')

@section('title', 'Añadir coche — IV MOTORCLASS')

@section('content')

<div class="ad-head">
  <div class="ad-head__t">
    <h1 class="ad-h1">Añadir coche</h1>
    <p class="ad-head__p">Las fotos primero. Lo demás son dos minutos.</p>
  </div>
  <div class="ad-head__go">
    <a class="ad-btn ad-btn--q" href="{{ route('admin.vehicles.index') }}">Volver a los coches</a>
  </div>
</div>

@include('admin.vehicles._form', [
  'vehicle' => null,
  'action'  => route('admin.vehicles.store'),
])

@endsection
