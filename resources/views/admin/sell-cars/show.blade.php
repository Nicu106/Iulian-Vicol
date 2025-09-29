@extends('layouts.admin')

@section('title', 'Detalii mașină de vânzare - Admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2"><i class="bi bi-car-front me-2"></i>Detalii mașină de vânzare</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <div class="btn-group me-2">
      <a href="{{ route('admin.sell-cars.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Înapoi la listă
      </a>
    </div>
  </div>
</div>

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
            <label class="form-label fw-bold">Titlu anunț:</label>
            <p class="form-control-plaintext">{{ $vehicle->title }}</p>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">Marca:</label>
            <p class="form-control-plaintext">{{ $vehicle->brand }}</p>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">Model:</label>
            <p class="form-control-plaintext">{{ $vehicle->model }}</p>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">Anul:</label>
            <p class="form-control-plaintext">{{ $vehicle->year }}</p>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">Preț:</label>
            <p class="form-control-plaintext text-success fw-bold">{{ number_format($vehicle->price, 0, ',', '.') }} EUR</p>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">Kilometraj:</label>
            <p class="form-control-plaintext">{{ number_format($vehicle->mileage, 0, ',', '.') }} km</p>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">Combustibil:</label>
            <p class="form-control-plaintext">{{ $vehicle->fuel_type }}</p>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">Transmisie:</label>
            <p class="form-control-plaintext">{{ $vehicle->transmission }}</p>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">Tip caroserie:</label>
            <p class="form-control-plaintext">{{ $vehicle->body_type }}</p>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">Culoarea:</label>
            <p class="form-control-plaintext">{{ $vehicle->color }}</p>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">Capacitate motor:</label>
            <p class="form-control-plaintext">{{ $vehicle->engine_capacity }} cm³</p>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">Putere:</label>
            <p class="form-control-plaintext">{{ $vehicle->power }} CP</p>
          </div>
        </div>
        
        <div class="mt-3">
          <label class="form-label fw-bold">Descriere:</label>
          <div class="border rounded p-3 bg-light">
            {{ $vehicle->description }}
          </div>
        </div>
      </div>
    </div>

    <!-- Imagini -->
    @if($vehicle->images && is_array($vehicle->images) && count($vehicle->images) > 0)
    <div class="card mb-4">
      <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-images me-2"></i>Imagini ({{ count($vehicle->images) }})</h5>
      </div>
      <div class="card-body">
        <div class="row g-2">
          @foreach($vehicle->images as $image)
            <div class="col-md-3">
              <img src="{{ Storage::url($image) }}" 
                   alt="{{ $vehicle->title }}" 
                   class="img-thumbnail" 
                   style="width: 100%; height: 150px; object-fit: cover; cursor: pointer;"
                   data-bs-toggle="modal" 
                   data-bs-target="#imageModal"
                   data-bs-src="{{ Storage::url($image) }}">
            </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif
  </div>

  <div class="col-lg-4">
    <!-- Informații vânzător -->
    <div class="card mb-4">
      <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="bi bi-person me-2"></i>Informații vânzător</h5>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label fw-bold">Nume:</label>
          <p class="form-control-plaintext">{{ $vehicle->seller_name }}</p>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Telefon:</label>
          <p class="form-control-plaintext">
            <a href="tel:{{ $vehicle->seller_phone }}" class="text-decoration-none">
              {{ $vehicle->seller_phone }}
            </a>
          </p>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Email:</label>
          <p class="form-control-plaintext">
            <a href="mailto:{{ $vehicle->seller_email }}" class="text-decoration-none">
              {{ $vehicle->seller_email }}
            </a>
          </p>
        </div>
      </div>
    </div>

    <!-- Acțiuni -->
    <div class="card mb-4">
      <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Acțiuni</h5>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a href="{{ route('admin.sell-cars.download-photos', $vehicle) }}" class="btn btn-outline-secondary w-100">
            <i class="bi bi-download me-2"></i>Descarcă pozele proprietarului
          </a>
          <a href="{{ route('admin.sell-cars.edit', $vehicle) }}" class="btn btn-info w-100">
            <i class="bi bi-pencil me-2"></i>Editează anunțul
          </a>
          
          @if($vehicle->status === 'pending')
            <form action="{{ route('admin.sell-cars.approve', $vehicle) }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-success w-100" 
                      onclick="return confirm('Ești sigur că vrei să aprobi acest anunț?')">
                <i class="bi bi-check me-2"></i>Aprobă și publică
              </button>
            </form>
            
            <form action="{{ route('admin.sell-cars.reject', $vehicle) }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-warning w-100" 
                      onclick="return confirm('Ești sigur că vrei să respingi acest anunț?')">
                <i class="bi bi-x me-2"></i>Respinge
              </button>
            </form>
          @elseif($vehicle->status === 'available')
            <span class="badge bg-success fs-6 w-100 py-2">
              <i class="bi bi-check-circle me-2"></i>Publicat
            </span>
          @elseif($vehicle->status === 'rejected')
            <span class="badge bg-danger fs-6 w-100 py-2">
              <i class="bi bi-x-circle me-2"></i>Respins
            </span>
          @endif
          
          <form action="{{ route('admin.sell-cars.destroy', $vehicle) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger w-100" 
                    onclick="return confirm('Ești sigur că vrei să ștergi acest anunț? Această acțiune nu poate fi anulată!')">
              <i class="bi bi-trash me-2"></i>Șterge anunțul
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Informații tehnice -->
    <div class="card">
      <div class="card-header bg-secondary text-white">
        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informații tehnice</h5>
      </div>
      <div class="card-body">
        <div class="mb-2">
          <small class="text-muted">ID:</small> {{ $vehicle->id }}
        </div>
        <div class="mb-2">
          <small class="text-muted">Slug:</small> {{ $vehicle->slug }}
        </div>
        <div class="mb-2">
          <small class="text-muted">Status:</small> 
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
  </div>
</div>

<!-- Modal pentru imagini -->
<div class="modal fade" id="imageModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Imagine mașină</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <img id="modalImage" src="" alt="" class="img-fluid">
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const imageModal = document.getElementById('imageModal');
  const modalImage = document.getElementById('modalImage');
  
  imageModal.addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const imageSrc = button.getAttribute('data-bs-src');
    modalImage.src = imageSrc;
  });
});
</script>
@endpush
