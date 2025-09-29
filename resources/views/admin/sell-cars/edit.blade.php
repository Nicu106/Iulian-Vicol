@extends('layouts.admin')

@section('title', 'Editează mașină de vânzare - Admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2"><i class="bi bi-pencil me-2"></i>Editează mașină de vânzare</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <div class="btn-group me-2">
      <a href="{{ route('admin.sell-cars.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Înapoi la listă
      </a>
      <a href="{{ route('admin.sell-cars.show', $vehicle) }}" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-eye me-1"></i>Vezi detalii
      </a>
    </div>
  </div>
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

<form action="{{ route('admin.sell-cars.update', $vehicle) }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
  @csrf
  @method('PUT')
  
  <div class="row">
    <div class="col-lg-8">
      <!-- Informații despre mașină -->
      <div class="card mb-4">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="bi bi-car-front me-2"></i>Informații despre mașină</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="title" class="form-label">Titlu anunț *</label>
              <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $vehicle->title) }}" required>
            </div>
            <div class="col-md-3">
              <label for="brand" class="form-label">Marca *</label>
              <select class="form-select" id="brand" name="brand" required>
                <option value="">Selectează marca</option>
                <option value="BMW" {{ old('brand', $vehicle->brand) == 'BMW' ? 'selected' : '' }}>BMW</option>
                <option value="Audi" {{ old('brand', $vehicle->brand) == 'Audi' ? 'selected' : '' }}>Audi</option>
                <option value="Mercedes" {{ old('brand', $vehicle->brand) == 'Mercedes' ? 'selected' : '' }}>Mercedes</option>
                <option value="Volkswagen" {{ old('brand', $vehicle->brand) == 'Volkswagen' ? 'selected' : '' }}>Volkswagen</option>
                <option value="Toyota" {{ old('brand', $vehicle->brand) == 'Toyota' ? 'selected' : '' }}>Toyota</option>
                <option value="Honda" {{ old('brand', $vehicle->brand) == 'Honda' ? 'selected' : '' }}>Honda</option>
                <option value="Ford" {{ old('brand', $vehicle->brand) == 'Ford' ? 'selected' : '' }}>Ford</option>
                <option value="Nissan" {{ old('brand', $vehicle->brand) == 'Nissan' ? 'selected' : '' }}>Nissan</option>
                <option value="Altele" {{ old('brand', $vehicle->brand) == 'Altele' ? 'selected' : '' }}>Altele</option>
              </select>
            </div>
            <div class="col-md-3">
              <label for="model" class="form-label">Model *</label>
              <input type="text" class="form-control" id="model" name="model" value="{{ old('model', $vehicle->model) }}" required>
            </div>
            <div class="col-md-3">
              <label for="year" class="form-label">Anul *</label>
              <select class="form-select" id="year" name="year" required>
                <option value="">Selectează anul</option>
                @for($i = date('Y'); $i >= 1990; $i--)
                  <option value="{{ $i }}" {{ old('year', $vehicle->year) == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
              </select>
            </div>
            <div class="col-md-3">
              <label for="price" class="form-label">Preț (EUR) *</label>
              <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $vehicle->price) }}" min="0" step="100" required>
            </div>
            <div class="col-md-3">
              <label for="mileage" class="form-label">Kilometraj *</label>
              <input type="number" class="form-control" id="mileage" name="mileage" value="{{ old('mileage', $vehicle->mileage) }}" min="0" required>
            </div>
            <div class="col-md-3">
              <label for="fuel_type" class="form-label">Combustibil *</label>
              <select class="form-select" id="fuel_type" name="fuel_type" required>
                <option value="">Selectează</option>
                <option value="Benzină" {{ old('fuel_type', $vehicle->fuel_type) == 'Benzină' ? 'selected' : '' }}>Benzină</option>
                <option value="Diesel" {{ old('fuel_type', $vehicle->fuel_type) == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                <option value="Hibrid" {{ old('fuel_type', $vehicle->fuel_type) == 'Hibrid' ? 'selected' : '' }}>Hibrid</option>
                <option value="Electric" {{ old('fuel_type', $vehicle->fuel_type) == 'Electric' ? 'selected' : '' }}>Electric</option>
              </select>
            </div>
            <div class="col-md-3">
              <label for="transmission" class="form-label">Transmisie *</label>
              <select class="form-select" id="transmission" name="transmission" required>
                <option value="">Selectează</option>
                <option value="Manuală" {{ old('transmission', $vehicle->transmission) == 'Manuală' ? 'selected' : '' }}>Manuală</option>
                <option value="Automată" {{ old('transmission', $vehicle->transmission) == 'Automată' ? 'selected' : '' }}>Automată</option>
              </select>
            </div>
            <div class="col-md-3">
              <label for="body_type" class="form-label">Tip caroserie *</label>
              <select class="form-select" id="body_type" name="body_type" required>
                <option value="">Selectează</option>
                <option value="Sedan" {{ old('body_type', $vehicle->body_type) == 'Sedan' ? 'selected' : '' }}>Sedan</option>
                <option value="SUV" {{ old('body_type', $vehicle->body_type) == 'SUV' ? 'selected' : '' }}>SUV</option>
                <option value="Hatchback" {{ old('body_type', $vehicle->body_type) == 'Hatchback' ? 'selected' : '' }}>Hatchback</option>
                <option value="Coupe" {{ old('body_type', $vehicle->body_type) == 'Coupe' ? 'selected' : '' }}>Coupe</option>
                <option value="Convertible" {{ old('body_type', $vehicle->body_type) == 'Convertible' ? 'selected' : '' }}>Convertible</option>
                <option value="Wagon" {{ old('body_type', $vehicle->body_type) == 'Wagon' ? 'selected' : '' }}>Wagon</option>
              </select>
            </div>
            <div class="col-md-3">
              <label for="color" class="form-label">Culoarea *</label>
              <input type="text" class="form-control" id="color" name="color" value="{{ old('color', $vehicle->color) }}" required>
            </div>
            <div class="col-md-3">
              <label for="engine_capacity" class="form-label">Capacitate motor (cm³) *</label>
              <input type="number" class="form-control" id="engine_capacity" name="engine_capacity" value="{{ old('engine_capacity', $vehicle->engine_capacity) }}" min="0" required>
            </div>
            <div class="col-md-3">
              <label for="power" class="form-label">Putere (CP) *</label>
              <input type="number" class="form-control" id="power" name="power" value="{{ old('power', $vehicle->power) }}" min="0" required>
            </div>
          </div>
        </div>
      </div>

      <!-- Imagini existente -->
      @if($vehicle->images && is_array($vehicle->images) && count($vehicle->images) > 0)
      <div class="card mb-4">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0"><i class="bi bi-images me-2"></i>Imagini existente ({{ count($vehicle->images) }})</h5>
        </div>
        <div class="card-body">
          <div class="row g-2" id="existingImages">
            @foreach($vehicle->images as $index => $image)
              <div class="col-md-3" data-image="{{ $image }}">
                <div class="position-relative">
                  <img src="{{ Storage::url($image) }}" 
                       class="img-thumbnail" 
                       style="width: 100%; height: 150px; object-fit: cover;">
                  <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" 
                          onclick="removeExistingImage(this, '{{ $image }}')">
                    <i class="bi bi-x"></i>
                  </button>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
      @endif

      <!-- Imagini noi -->
      <div class="card mb-4">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Adaugă imagini noi (opțional)</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="images" class="form-label">Selectează imagini noi</label>
            <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
            <div class="form-text">Format acceptat: JPG, PNG, GIF. Dimensiune maximă: 5MB per imagine.</div>
          </div>
          <div id="imagePreview" class="row g-2"></div>
        </div>
      </div>

      <!-- Descriere -->
      <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
          <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Descriere</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="description" class="form-label">Descriere detaliată *</label>
            <textarea class="form-control" id="description" name="description" rows="5" required>{{ old('description', $vehicle->description) }}</textarea>
            <div class="form-text">Descrie starea mașinii, opțiunile, istoricul, etc.</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <!-- Informații contact -->
      <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
          <h5 class="mb-0"><i class="bi bi-person me-2"></i>Informații contact</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12">
              <label for="seller_name" class="form-label">Numele vânzătorului *</label>
              <input type="text" class="form-control" id="seller_name" name="seller_name" value="{{ old('seller_name', $vehicle->seller_name) }}" required>
            </div>
            <div class="col-12">
              <label for="seller_phone" class="form-label">Telefon *</label>
              <input type="tel" class="form-control" id="seller_phone" name="seller_phone" value="{{ old('seller_phone', $vehicle->seller_phone) }}" required>
            </div>
            <div class="col-12">
              <label for="seller_email" class="form-label">Email *</label>
              <input type="email" class="form-control" id="seller_email" name="seller_email" value="{{ old('seller_email', $vehicle->seller_email) }}" required>
            </div>
          </div>
        </div>
      </div>

      <!-- Status -->
      <div class="card mb-4">
        <div class="card-header bg-dark text-white">
          <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Status</h5>
        </div>
        <div class="card-body">
          <div class="mb-2">
            <small class="text-muted">Status curent:</small> 
            <span class="badge bg-{{ $vehicle->status === 'pending' ? 'warning' : ($vehicle->status === 'available' ? 'success' : 'danger') }}">
              {{ $vehicle->status }}
            </span>
          </div>
          <div class="mb-2">
            <small class="text-muted">Creat la:</small> {{ $vehicle->created_at->format('d.m.Y H:i') }}
          </div>
          <div class="mb-2">
            <small class="text-muted">Actualizat la:</small> {{ $vehicle->updated_at->format('d.m.Y H:i') }}
          </div>
        </div>
      </div>

      <!-- Acțiuni -->
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Acțiuni</h5>
        </div>
        <div class="card-body">
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-success">
              <i class="bi bi-check me-2"></i>Salvează modificările
            </button>
            <a href="{{ route('admin.sell-cars.show', $vehicle) }}" class="btn btn-outline-secondary">
              <i class="bi bi-x me-2"></i>Anulează
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<!-- Hidden input for removed images -->
<input type="hidden" id="removedImages" name="removed_images" value="">
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Image preview for new images
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
              <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeNewImage(${index})">
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

let removedImages = [];

function removeExistingImage(button, imagePath) {
  if (confirm('Ești sigur că vrei să ștergi această imagine?')) {
    const col = button.closest('.col-md-3');
    col.remove();
    removedImages.push(imagePath);
    document.getElementById('removedImages').value = JSON.stringify(removedImages);
  }
}

function removeNewImage(index) {
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

