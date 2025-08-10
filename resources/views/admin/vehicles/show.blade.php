@extends('layouts.admin')
@section('title','Admin • Vehicul')
@section('content')
<div class="py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">{{ $vehicle['title'] ?? (($vehicle['brand'] ?? '') . ' ' . ($vehicle['model'] ?? '') . ' ' . ($vehicle['year'] ?? '')) }}</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('admin.vehicles.edit', $vehicle['slug']) }}" class="btn btn-primary">Editează</a>
      <a href="{{ route('vehicle.show', $vehicle['slug']) }}" class="btn btn-outline-secondary" target="_blank">Vezi pe site</a>
    </div>
  </div>
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="ratio ratio-16x9 rounded overflow-hidden bg-light">
        <img src="{{ $vehicle['cover_image'] ?? ($vehicle['images'][0] ?? 'https://picsum.photos/seed/cover/1200/800') }}" class="w-100 h-100 object-fit-cover" alt="cover" />
      </div>
      <div class="row g-2 mt-2">
        @foreach(($vehicle['gallery_images'] ?? []) as $img)
          <div class="col-4"><div class="ratio ratio-16x9 rounded overflow-hidden"><img src="{{ $img }}" class="w-100 h-100 object-fit-cover" /></div></div>
        @endforeach
      </div>
      <div class="mt-4">
        <h5>Descriere</h5>
        <p class="text-secondary">{{ $vehicle['description'] }}</p>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card shadow-sm"><div class="card-body">
        <div class="display-6 text-primary fw-bold">{{ $vehicle['price'] }}</div>
        <ul class="list-unstyled small mt-3 mb-0">
          <li><strong>An:</strong> {{ $vehicle['year'] }}</li>
          <li><strong>Kilometraj:</strong> {{ $vehicle['mileage'] }}</li>
          <li><strong>Combustibil:</strong> {{ $vehicle['fuel'] }}</li>
          <li><strong>Cutie:</strong> {{ $vehicle['transmission'] }}</li>
          <li><strong>Putere:</strong> {{ $vehicle['power'] }}</li>
          <li><strong>Tracțiune:</strong> {{ $vehicle['drivetrain'] }}</li>
          <li><strong>Culoare:</strong> {{ $vehicle['color'] }}</li>
        </ul>
        @if(!empty($vehicle['features']))
          <h6 class="mt-3">Dotări</h6>
          <div class="d-flex flex-wrap gap-2">
            @foreach($vehicle['features'] as $f)
              <span class="badge bg-light text-dark border">{{ $f }}</span>
            @endforeach
          </div>
        @endif
      </div></div>
    </div>
  </div>
</div>
@endsection


