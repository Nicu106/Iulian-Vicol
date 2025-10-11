@extends('layouts.app')
@section('title', 'Coches guardados - MOTORCLASS')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/pages/saved-vehicles.css') }}">
@endpush

@section('content')
<section class="py-5 bg-light saved-vehicles-page">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="h2 mb-0">
            <i class="bi bi-bookmark-heart text-primary me-2"></i>
            Coches guardados
          </h1>
          <button type="button" class="btn btn-outline-danger" id="clearAllSaved">
            <i class="bi bi-trash me-2"></i>Șterge toate
          </button>
        </div>
        
        <div id="savedVehiclesContainer" class="row g-4">
          <!-- Mașinile salvate vor fi afișate aici din JavaScript -->
        </div>
        
        <div id="noSavedVehicles" class="text-center py-5" style="display: none;">
          <i class="bi bi-bookmark-heart text-muted mb-3" style="font-size: 4rem;"></i>
          <h4 class="text-muted">No tienes coches guardados</h4>
          <p class="text-muted mb-4">Guarda los coches que te interesan para verlos aquí más tarde.</p>
          <a href="{{ route('catalog') }}" class="btn btn-primary">
            <i class="bi bi-search me-2"></i>Vezi catalogul
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/pages/saved-vehicles.js') }}"></script>
@endpush
