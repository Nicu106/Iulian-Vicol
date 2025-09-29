@extends('layouts.app')

@section('title', 'Sell Your Car - Vinde mașina ta cu MOTORCLASS')
@section('description', 'Vinde mașina ta rapid și sigur cu MOTORCLASS. Proces simplu, evaluare gratuită și prețuri competitive.')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/pages/sell-car.css') }}">
@endpush

@section('content')
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <!-- Header -->
      <div class="text-center mb-5">
        <h1 class="display-5 fw-bold text-primary">Vende tu coche</h1>
        <p class="lead text-secondary">Completa el formulario y nuestro equipo te contactará lo antes posible.</p>
      </div>

      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <ul class="mb-0">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <!-- Form -->
      <form action="{{ route('sell-car.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        @csrf
        
        <!-- Informații despre mașină -->
        <div class="card mb-4">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-car-front me-2"></i>Información del vehículo</h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="title" class="form-label">Título del anuncio *</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required>
              </div>
              <div class="col-md-3">
                <label for="brand" class="form-label">Marca *</label>
                <select class="form-select" id="brand" name="brand" required>
                  <option value="">Selectează marca</option>
                  <option value="BMW" {{ old('brand') == 'BMW' ? 'selected' : '' }}>BMW</option>
                  <option value="Audi" {{ old('brand') == 'Audi' ? 'selected' : '' }}>Audi</option>
                  <option value="Mercedes" {{ old('brand') == 'Mercedes' ? 'selected' : '' }}>Mercedes</option>
                  <option value="Volkswagen" {{ old('brand') == 'Volkswagen' ? 'selected' : '' }}>Volkswagen</option>
                  <option value="Toyota" {{ old('brand') == 'Toyota' ? 'selected' : '' }}>Toyota</option>
                  <option value="Honda" {{ old('brand') == 'Honda' ? 'selected' : '' }}>Honda</option>
                  <option value="Ford" {{ old('brand') == 'Ford' ? 'selected' : '' }}>Ford</option>
                  <option value="Nissan" {{ old('brand') == 'Nissan' ? 'selected' : '' }}>Nissan</option>
                  <option value="Otros" {{ old('brand') == 'Otros' ? 'selected' : '' }}>Otros</option>
                </select>
              </div>
              <div class="col-md-3">
                <label for="model" class="form-label">Modelo *</label>
                <input type="text" class="form-control" id="model" name="model" value="{{ old('model') }}" required>
              </div>
              <div class="col-md-3">
                <label for="year" class="form-label">Año *</label>
                <select class="form-select" id="year" name="year" required>
                  <option value="">Selectează anul</option>
                  @for($i = date('Y'); $i >= 1990; $i--)
                    <option value="{{ $i }}" {{ old('year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                  @endfor
                </select>
              </div>
              <div class="col-md-3">
                <label for="price" class="form-label">Preț (EUR) *</label>
                <input type="number" class="form-control" id="price" name="price" value="{{ old('price') }}" min="0" step="100" required>
              </div>
              <div class="col-md-3">
                <label for="mileage" class="form-label">Kilometraj *</label>
                <input type="number" class="form-control" id="mileage" name="mileage" value="{{ old('mileage') }}" min="0" required>
              </div>
              <div class="col-md-3">
                <label for="fuel_type" class="form-label">Combustibil *</label>
                <select class="form-select" id="fuel_type" name="fuel_type" required>
                  <option value="">Selectează</option>
                  <option value="Benzina" {{ old('fuel_type') == 'Benzina' ? 'selected' : '' }}>Benzina</option>
                  <option value="Diésel" {{ old('fuel_type') == 'Diésel' ? 'selected' : '' }}>Diésel</option>
                  <option value="Híbrido" {{ old('fuel_type') == 'Híbrido' ? 'selected' : '' }}>Híbrido</option>
                  <option value="Electric" {{ old('fuel_type') == 'Electric' ? 'selected' : '' }}>Electric</option>
                </select>
              </div>
              <div class="col-md-3">
                <label for="transmission" class="form-label">Transmisión *</label>
                <select class="form-select" id="transmission" name="transmission" required>
                  <option value="">Selectează</option>
                  <option value="Manual" {{ old('transmission') == 'Manual' ? 'selected' : '' }}>Manual</option>
                  <option value="Automático" {{ old('transmission') == 'Automático' ? 'selected' : '' }}>Automático</option>
                </select>
              </div>
              <div class="col-md-3">
                <label for="body_type" class="form-label">Tipo de carrocería *</label>
                <select class="form-select" id="body_type" name="body_type" required>
                  <option value="">Selectează</option>
                  <option value="Sedan" {{ old('body_type') == 'Sedan' ? 'selected' : '' }}>Sedan</option>
                  <option value="SUV" {{ old('body_type') == 'SUV' ? 'selected' : '' }}>SUV</option>
                  <option value="Hatchback" {{ old('body_type') == 'Hatchback' ? 'selected' : '' }}>Hatchback</option>
                  <option value="Coupe" {{ old('body_type') == 'Coupe' ? 'selected' : '' }}>Coupe</option>
                  <option value="Convertible" {{ old('body_type') == 'Convertible' ? 'selected' : '' }}>Convertible</option>
                  <option value="Wagon" {{ old('body_type') == 'Wagon' ? 'selected' : '' }}>Wagon</option>
                </select>
              </div>
              <div class="col-md-3">
                <label for="color" class="form-label">Color *</label>
                <input type="text" class="form-control" id="color" name="color" value="{{ old('color') }}" required>
              </div>
              <div class="col-md-3">
                <label for="engine_capacity" class="form-label">Cilindrada (cm³) *</label>
                <input type="number" class="form-control" id="engine_capacity" name="engine_capacity" value="{{ old('engine_capacity') }}" min="0" required>
              </div>
              <div class="col-md-3">
                <label for="power" class="form-label">Potencia (CV) *</label>
                <input type="number" class="form-control" id="power" name="power" value="{{ old('power') }}" min="0" required>
              </div>
            </div>
          </div>
        </div>

        <!-- Imagini -->
        <div class="card mb-4">
          <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-images me-2"></i>Imágenes (máx 10)</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label for="images" class="form-label">Selecciona imágenes *</label>
              <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*" required>
              <div class="form-text">Formatos: JPG, PNG, GIF. Tamaño máximo: 5MB por imagen.</div>
            </div>
            <div id="imagePreview" class="row g-2"></div>
          </div>
        </div>

        <!-- Descriere -->
        <div class="card mb-4">
          <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Descripción</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label for="description" class="form-label">Descripción detallada *</label>
              <textarea class="form-control" id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
              <div class="form-text">Describe el estado del coche, opciones, historial, etc.</div>
            </div>
          </div>
        </div>

        <!-- Informații contact -->
        <div class="card mb-4">
          <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-person me-2"></i>Información de contacto</h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="seller_name" class="form-label">Tu nombre *</label>
                <input type="text" class="form-control" id="seller_name" name="seller_name" value="{{ old('seller_name') }}" required>
              </div>
              <div class="col-md-6">
                <label for="seller_phone" class="form-label">Teléfono *</label>
                <input type="tel" class="form-control" id="seller_phone" name="seller_phone" value="{{ old('seller_phone') }}" required>
              </div>
              <div class="col-12">
                <label for="seller_email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="seller_email" name="seller_email" value="{{ old('seller_email') }}" required>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit -->
        <div class="text-center">
          <button type="submit" class="btn btn-success btn-lg px-5">
            <i class="bi bi-send me-2"></i>Enviar anuncio
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Image preview
  const imageInput = document.getElementById('images');
  const imagePreview = document.getElementById('imagePreview');
  
  imageInput.addEventListener('change', function(e) {
    imagePreview.innerHTML = '';
    const files = Array.from(e.target.files);
    
    files.forEach((file, index) => {
      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const col = document.createElement('div');
          col.className = 'col-md-3';
          col.innerHTML = `
            <div class="position-relative">
              <img src="${e.target.result}" class="img-thumbnail" style="width: 100%; height: 150px; object-fit: cover;">
              <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeImage(${index})">
                <i class="bi bi-x"></i>
              </button>
            </div>
          `;
          imagePreview.appendChild(col);
        };
        reader.readAsDataURL(file);
      }
    });
  });
  
  // Form validation
  const form = document.querySelector('.needs-validation');
  form.addEventListener('submit', function(e) {
    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    form.classList.add('was-validated');
  });
});

function removeImage(index) {
  const imageInput = document.getElementById('images');
  const dt = new DataTransfer();
  const files = Array.from(imageInput.files);
  
  files.forEach((file, i) => {
    if (i !== index) {
      dt.items.add(file);
    }
  });
  
  imageInput.files = dt.files;
  
  // Refresh preview
  imageInput.dispatchEvent(new Event('change'));
}
</script>
@endpush

