@extends('layouts.ad')

@section('title', trim(($vehicle['brand'] ?? '') . ' ' . ($vehicle['model'] ?? '')) . ' — IV MOTORCLASS')

@section('content')

<div class="ad-head">
  <div class="ad-head__t">
    <h1 class="ad-h1">{{ trim(($vehicle['brand'] ?? '') . ' ' . ($vehicle['model'] ?? '') . ' ' . ($vehicle['year'] ?? '')) }}</h1>
    <p class="ad-head__p">{{ count($vehicle['gallery_images'] ?? []) }} fotos · añadido el
      {{ \Illuminate\Support\Carbon::parse($vehicle['created_at'] ?? now())->format('d/m/Y') }}</p>
  </div>
  <div class="ad-head__go">
    <a class="ad-btn ad-btn--q" href="{{ route('admin.vehicles.index') }}">Volver a los coches</a>
  </div>
</div>

@include('admin.vehicles._form', [
  'vehicle' => $vehicle,
  'action'  => route('admin.vehicles.update', $vehicle['slug']),
])

@endsection
