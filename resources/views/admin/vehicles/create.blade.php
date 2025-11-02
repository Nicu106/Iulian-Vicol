@extends('layouts.admin')

@section('title', 'Admin • Adaugă vehicul')

@push('styles')
<style>
  /* Mobile-first responsive form styles */
  .admin-card { 
    border-radius: 12px; 
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 1rem;
  }
  
  .form-section-title { 
    font-weight: 700; 
    margin-bottom: 1rem; 
    color: #2563eb; 
    font-size: 1.1rem;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 0.5rem;
  }
  
  .hint { 
    color: #6b7280; 
    font-size: 0.875rem; 
    margin-top: 0.25rem;
  }
  
  .offer-section { 
    border: 2px dashed #f59e0b; 
    border-radius: 12px; 
    padding: 1rem; 
    background: #fffbeb; 
  }
  
  .status-badge { 
    display: inline-block; 
    padding: 4px 8px; 
    border-radius: 6px; 
    font-size: 0.75rem; 
    font-weight: 600; 
  }
  
  .status-available { background: #dcfce7; color: #166534; }
  .status-draft { background: #f3f4f6; color: #374151; }
  
  .image-upload-area {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 2rem 1rem;
    text-align: center;
    background: #f9fafb;
    transition: all 0.2s ease;
  }
  
  .image-upload-area:hover {
    border-color: #3b82f6;
    background: #eff6ff;
  }
  
  .image-upload-area.dragover {
    border-color: #3b82f6;
    background: #dbeafe;
  }
  
  .form-floating-custom {
    position: relative;
  }
  
  .form-floating-custom textarea {
    min-height: 120px;
    resize: vertical;
  }
  
  /* Mobile responsive adjustments */
  @media (max-width: 767.98px) {
    .admin-card {
      margin-bottom: 0.75rem;
    }
    
    .form-section-title {
      font-size: 1rem;
      margin-bottom: 0.75rem;
    }
    
    .image-upload-area {
      padding: 1.5rem 0.75rem;
    }
    
    .btn-group-mobile {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    
    .btn-group-mobile .btn {
      width: 100%;
    }
  }
  
  /* Tablet adjustments */
  @media (min-width: 768px) and (max-width: 991.98px) {
    .form-section-title {
      font-size: 1.05rem;
    }
  }
</style>
@endpush

@section('content')
<section class="py-2 py-md-4">
  <div class="container-fluid px-2 px-md-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mb-md-4">
      <div class="mb-2 mb-md-0">
        <h1 class="h4 h-md-3 mb-1 fw-bold">Adaugă vehicul nou</h1>
        <p class="text-muted mb-0 small">Completează informațiile pentru a adăuga un vehicul nou</p>
      </div>
      <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-arrow-left me-1"></i>Lista vehicule
        </a>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-eye me-1"></i>Vezi site
        </a>
      </div>
    </div>

    @if(session('status'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
        @if(session('preview_url'))
          <a href="{{ session('preview_url') }}" class="ms-2 alert-link">Vezi previzualizare</a>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <form action="{{ route('admin.vehicles.store') }}" method="post" enctype="multipart/form-data" class="row g-2 g-md-4">
      @csrf
      
      <!-- Main Content -->
      <div class="col-12 col-lg-8">
        <!-- Basic Information -->
        <div class="card admin-card">
          <div class="card-body p-3 p-md-4">
            <div class="form-section-title">
              <i class="bi bi-car-front me-2"></i>Información básica
            </div>
            <div class="row g-2 g-md-3">
              <div class="col-12 col-md-4">
                <label class="form-label fw-medium">Marca *</label>
                <input class="form-control" name="brand" required value="{{ old('brand') }}" placeholder="BMW, Mercedes, Audi...">
                @error('brand')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
              <div class="col-12 col-md-5">
                <label class="form-label fw-medium">Modelo *</label>
                <input class="form-control" name="model" required value="{{ old('model') }}" placeholder="Serie 3, Clase C, A4...">
                @error('model')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label fw-medium">Año *</label>
                <input type="number" class="form-control" name="year" required value="{{ old('year') }}" min="1990" max="{{ date('Y') }}" placeholder="{{ date('Y') }}">
                @error('year')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
        </div>

        <!-- Pricing Information -->
        <div class="card admin-card">
          <div class="card-body p-3 p-md-4">
            <div class="form-section-title">
              <i class="bi bi-currency-euro me-2"></i>Información de precio
            </div>
            <div class="row g-2 g-md-3">
              <div class="col-12 col-md-4">
                <label class="form-label fw-medium">Precio *</label>
                <div class="input-group">
                  <span class="input-group-text">€</span>
                  <input type="number" class="form-control" name="price" required value="{{ old('price') }}" min="0" step="0.01" placeholder="25000">
                </div>
                @error('price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label fw-medium">Precio original</label>
                <div class="input-group">
                  <span class="input-group-text">€</span>
                  <input type="number" class="form-control" name="original_price" value="{{ old('original_price') }}" min="0" step="0.01" placeholder="30000">
                </div>
                <div class="hint">Para mostrar descuentos</div>
                @error('original_price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label fw-medium">Precio oferta</label>
                <div class="input-group">
                  <span class="input-group-text">€</span>
                  <input type="number" class="form-control" name="offer_price" value="{{ old('offer_price') }}" min="0" step="0.01" placeholder="23000">
                </div>
                <div class="hint">Precio promocional</div>
                @error('offer_price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
            </div>
            
            <!-- Offer Details -->
            <div class="row g-2 g-md-3 mt-2">
              <div class="col-12 col-md-6">
                <label class="form-label fw-medium">Tipo de oferta</label>
                <select class="form-select" name="offer_type">
                  <option value="">Sin oferta</option>
                  <option value="flash_sale" {{ old('offer_type') == 'flash_sale' ? 'selected' : '' }}>Oferta flash</option>
                  <option value="seasonal" {{ old('offer_type') == 'seasonal' ? 'selected' : '' }}>Oferta estacional</option>
                  <option value="clearance" {{ old('offer_type') == 'clearance' ? 'selected' : '' }}>Liquidación</option>
                  <option value="negotiable" {{ old('offer_type') == 'negotiable' ? 'selected' : '' }}>Negociable</option>
                  <option value="promotion" {{ old('offer_type') == 'promotion' ? 'selected' : '' }}>Promoción</option>
                </select>
                @error('offer_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label fw-medium">Oferta expira</label>
                <input type="date" class="form-control" name="offer_expires_at" value="{{ old('offer_expires_at') }}" min="{{ date('Y-m-d') }}">
                @error('offer_expires_at')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
            </div>
            
            <div class="mt-2">
              <label class="form-label fw-medium">Descripción de la oferta</label>
              <textarea class="form-control" name="offer_description" rows="2" placeholder="Describe la oferta especial...">{{ old('offer_description') }}</textarea>
              @error('offer_description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>

        <!-- Technical Specifications -->
        <div class="card admin-card">
          <div class="card-body p-3 p-md-4">
            <div class="form-section-title">
              <i class="bi bi-gear me-2"></i>Especificaciones técnicas
            </div>
            <div class="row g-2 g-md-3">
              <div class="col-12 col-md-4">
                <label class="form-label fw-medium">Kilometraje</label>
                <div class="input-group">
                  <input type="number" class="form-control" name="mileage" value="{{ old('mileage') }}" min="0" placeholder="50000">
                  <span class="input-group-text">km</span>
                </div>
                @error('mileage')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label fw-medium">Combustible</label>
                <select class="form-select" name="fuel">
                  <option value="">Seleccionar</option>
                  <option value="Gasolina" {{ old('fuel') == 'Gasolina' ? 'selected' : '' }}>Gasolina</option>
                  <option value="Diésel" {{ old('fuel') == 'Diésel' ? 'selected' : '' }}>Diésel</option>
                  <option value="Híbrido" {{ old('fuel') == 'Híbrido' ? 'selected' : '' }}>Híbrido</option>
                  <option value="Eléctrico" {{ old('fuel') == 'Eléctrico' ? 'selected' : '' }}>Eléctrico</option>
                  <option value="GLP" {{ old('fuel') == 'GLP' ? 'selected' : '' }}>GLP</option>
                </select>
                @error('fuel')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label fw-medium">Transmisión</label>
                <select class="form-select" name="transmission">
                  <option value="">Seleccionar</option>
                  <option value="Manual" {{ old('transmission') == 'Manual' ? 'selected' : '' }}>Manual</option>
                  <option value="Automática" {{ old('transmission') == 'Automática' ? 'selected' : '' }}>Automática</option>
                  <option value="Semiautomática" {{ old('transmission') == 'Semiautomática' ? 'selected' : '' }}>Semiautomática</option>
                </select>
                @error('transmission')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
            </div>
            
            <div class="row g-2 g-md-3 mt-2">
              <div class="col-12 col-md-3">
                <label class="form-label fw-medium">Motor</label>
                <input class="form-control" name="engine" value="{{ old('engine') }}" placeholder="2.0 TDI">
                @error('engine')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label fw-medium">Potencia</label>
                <input class="form-control" name="power" value="{{ old('power') }}" placeholder="150 CV">
                @error('power')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label fw-medium">Tracción</label>
                <select class="form-select" name="drivetrain">
                  <option value="">Seleccionar</option>
                  <option value="Delantera" {{ old('drivetrain') == 'Delantera' ? 'selected' : '' }}>Delantera</option>
                  <option value="Trasera" {{ old('drivetrain') == 'Trasera' ? 'selected' : '' }}>Trasera</option>
                  <option value="Integral" {{ old('drivetrain') == 'Integral' ? 'selected' : '' }}>Integral</option>
                </select>
                @error('drivetrain')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label fw-medium">Color</label>
                <input class="form-control" name="color" value="{{ old('color') }}" placeholder="Negro, Blanco, Azul...">
                @error('color')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
            </div>
            
            <div class="row g-2 g-md-3 mt-2">
              <div class="col-12 col-md-6">
                <label class="form-label fw-medium">Etiqueta medioambiental</label>
                <input class="form-control" name="vin" value="{{ old('vin') }}" placeholder="Etiqueta medioambiental">
                @error('vin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label fw-medium">Estado</label>
                <select class="form-select" name="condition">
                  <option value="">Seleccionar</option>
                  <option value="Nuevo" {{ old('condition') == 'Nuevo' ? 'selected' : '' }}>Nuevo</option>
                  <option value="Seminuevo" {{ old('condition') == 'Seminuevo' ? 'selected' : '' }}>Seminuevo</option>
                  <option value="Usado" {{ old('condition') == 'Usado' ? 'selected' : '' }}>Usado</option>
                  <option value="Km 0" {{ old('condition') == 'Km 0' ? 'selected' : '' }}>Km 0</option>
                </select>
                @error('condition')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
        </div>

        <!-- Description and Features -->
        <div class="card admin-card">
          <div class="card-body p-3 p-md-4">
            <div class="form-section-title">
              <i class="bi bi-card-text me-2"></i>Descripción y características
            </div>
            
            <div class="mb-3">
              <label class="form-label fw-medium">Descripción</label>
              <div class="form-floating-custom">
                <textarea id="description" class="form-control" name="description" rows="8" placeholder="Describe el vehículo en detalle...">{{ old('description') }}</textarea>
              </div>
              <div class="hint">Usa el editor para formatear el texto (negrita, cursiva, listas, etc.)</div>
              @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            
            <div class="mb-3">
              <label class="form-label fw-medium">Características</label>
              <textarea class="form-control" name="features" rows="3" placeholder="Aire acondicionado, GPS, Llantas de aleación, Asientos de cuero...">{{ old('features') }}</textarea>
              <div class="hint">Separa las características con comas</div>
              @error('features')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            
            <div class="mb-3">
              <label class="form-label fw-medium">Etiquetas</label>
              <input class="form-control" name="badges" value="{{ old('badges') }}" placeholder="Oferta, Garantía, Financiación...">
              <div class="hint">Etiquetas promocionales separadas por comas</div>
              @error('badges')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            
            <div>
              <label class="form-label fw-medium">URL del vídeo</label>
              <input type="url" class="form-control" name="video_url" value="{{ old('video_url') }}" placeholder="https://youtube.com/watch?v=...">
              <div class="hint">YouTube, Vimeo u otra plataforma</div>
              @error('video_url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>

        <!-- Images -->
        <div class="card admin-card">
          <div class="card-body p-3 p-md-4">
            <div class="form-section-title">
              <i class="bi bi-images me-2"></i>Imágenes
            </div>
            
            <div class="mb-3">
              <label class="form-label fw-medium">Imagen principal</label>
              <div class="image-upload-area" onclick="document.getElementById('cover_image').click()">
                <i class="bi bi-cloud-upload fs-2 text-muted d-block mb-2"></i>
                <p class="mb-2">Haz clic para seleccionar la imagen principal</p>
                <small class="text-muted">JPG, PNG, GIF</small>
                <input type="file" id="cover_image" name="cover_image" accept="image/*" class="d-none" onchange="previewImage(this, 'cover-preview')">
              </div>
              <div id="cover-preview" class="mt-2"></div>
              @error('cover_image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            
            <div>
              <label class="form-label fw-medium">Galería de imágenes</label>
              <div class="image-upload-area" onclick="document.getElementById('gallery_images').click()">
                <i class="bi bi-images fs-2 text-muted d-block mb-2"></i>
                <p class="mb-2">Haz clic para seleccionar múltiples imágenes</p>
                <small class="text-muted">Selecciona varias imágenes para la galería</small>
                <input type="file" id="gallery_images" name="gallery_images[]" accept="image/*" multiple class="d-none" onchange="previewImages(this, 'gallery-preview')">
              </div>
              <div id="gallery-preview" class="mt-2 row g-2"></div>
              @error('gallery_images.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-12 col-lg-4">
        <!-- Publication Settings -->
        <div class="card admin-card">
          <div class="card-body p-3 p-md-4">
            <div class="form-section-title">
              <i class="bi bi-gear-fill me-2"></i>Configuración
            </div>
            
            <div class="mb-3">
              <label class="form-label fw-medium">Estado de publicación</label>
              <select class="form-select" name="status">
                <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Disponible</option>
                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                <option value="reserved" {{ old('status') == 'reserved' ? 'selected' : '' }}>Reservado</option>
                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Mantenimiento</option>
              </select>
              @error('status')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="featured" id="featured" {{ old('featured') ? 'checked' : '' }}>
                <label class="form-check-label fw-medium" for="featured">
                  <i class="bi bi-star me-1"></i>Vehículo destacado
                </label>
              </div>
              <div class="hint">Se mostrará en posición preferente</div>
            </div>
            
            <div class="mb-3">
              <label class="form-label fw-medium">Prioridad</label>
              <input type="number" class="form-control" name="priority" value="{{ old('priority', 0) }}" min="0" max="999" placeholder="0">
              <div class="hint">Mayor número = mayor prioridad</div>
              @error('priority')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            
            <div>
              <label class="form-label fw-medium">Ubicación</label>
              <input class="form-control" name="location" value="{{ old('location') }}" placeholder="Madrid, Barcelona...">
              @error('location')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>

        <!-- SEO Settings -->
        <div class="card admin-card">
          <div class="card-body p-3 p-md-4">
            <div class="form-section-title">
              <i class="bi bi-search me-2"></i>SEO
            </div>
            
            <div class="mb-3">
              <label class="form-label fw-medium">Título SEO</label>
              <input class="form-control" name="meta_title" value="{{ old('meta_title') }}" maxlength="60" placeholder="Título para buscadores">
              <div class="hint">Máximo 60 caracteres</div>
              @error('meta_title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            
            <div class="mb-3">
              <label class="form-label fw-medium">Descripción SEO</label>
              <textarea class="form-control" name="meta_description" rows="3" maxlength="160" placeholder="Descripción para buscadores">{{ old('meta_description') }}</textarea>
              <div class="hint">Máximo 160 caracteres</div>
              @error('meta_description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            
            <div>
              <label class="form-label fw-medium">Etiquetas SEO</label>
              <input class="form-control" name="tags" value="{{ old('tags') }}" placeholder="bmw, serie3, diesel, automático">
              <div class="hint">Palabras clave separadas por comas</div>
              @error('tags')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>

        <!-- Internal Notes -->
        <div class="card admin-card">
          <div class="card-body p-3 p-md-4">
            <div class="form-section-title">
              <i class="bi bi-sticky me-2"></i>Notas internas
            </div>
            
            <textarea class="form-control" name="internal_notes" rows="4" placeholder="Notas privadas para el equipo...">{{ old('internal_notes') }}</textarea>
            <div class="hint">Solo visible para administradores</div>
            @error('internal_notes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
          </div>
        </div>

        <!-- Actions -->
        <div class="card admin-card">
          <div class="card-body p-3 p-md-4">
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-plus-circle me-2"></i>Crear Vehículo
              </button>
              <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Cancelar
              </a>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>

@push('scripts')
<script>
// Mobile-friendly image preview functions
// Fast client-side compression using createImageBitmap/OffscreenCanvas when available
async function compressImage(file, maxWidth = 1600, maxHeight = 1600, quality = 0.85) {
  // Skip compression for already-small files (< 600KB) to be instant
  if (file.size && file.size <= 600 * 1024) {
    return file;
  }
  return new Promise((resolve) => {
    try {
      const useImageBitmap = 'createImageBitmap' in window;
      const work = (bitmap) => {
        let width = bitmap.width;
        let height = bitmap.height;
        const ratio = Math.min(maxWidth / width, maxHeight / height, 1);
        const targetW = Math.round(width * ratio);
        const targetH = Math.round(height * ratio);
        if (ratio >= 1) {
          // No resize needed; keep original to save CPU
          return file;
        }

        if (typeof OffscreenCanvas !== 'undefined') {
          const canvas = new OffscreenCanvas(targetW, targetH);
          const ctx = canvas.getContext('2d');
          ctx.drawImage(bitmap, 0, 0, targetW, targetH);
          canvas.convertToBlob({ type: 'image/jpeg', quality }).then((blob) => {
            const outName = file.name.replace(/\.[^.]+$/, '') + '.jpg';
            resolve(new File([blob], outName, { type: 'image/jpeg', lastModified: Date.now() }));
          }).catch(() => resolve(file));
          return null;
        } else {
          const canvas = document.createElement('canvas');
          canvas.width = targetW; canvas.height = targetH;
          const ctx = canvas.getContext('2d');
          ctx.drawImage(bitmap, 0, 0, targetW, targetH);
          canvas.toBlob((blob) => {
            const outName = file.name.replace(/\.[^.]+$/, '') + '.jpg';
            resolve(new File([blob], outName, { type: 'image/jpeg', lastModified: Date.now() }));
          }, 'image/jpeg', quality);
          return null;
        }
      };

      if (useImageBitmap) {
        const blob = file;
        createImageBitmap(blob).then((bitmap) => {
          const result = work(bitmap);
          if (result) resolve(result);
        }).catch(() => resolve(file));
      } else {
        const img = new Image();
        const reader = new FileReader();
        reader.onload = function(e) {
          img.onload = function() {
            const canvas = document.createElement('canvas');
            const ratio = Math.min(maxWidth / img.width, maxHeight / img.height, 1);
            const targetW = Math.round(img.width * ratio);
            const targetH = Math.round(img.height * ratio);
            if (ratio >= 1) { resolve(file); return; }
            canvas.width = targetW; canvas.height = targetH;
            canvas.getContext('2d').drawImage(img, 0, 0, targetW, targetH);
            canvas.toBlob((blob) => {
              const outName = file.name.replace(/\.[^.]+$/, '') + '.jpg';
              resolve(new File([blob], outName, { type: 'image/jpeg', lastModified: Date.now() }));
            }, 'image/jpeg', quality);
          };
          img.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    } catch (err) {
      resolve(file); // fallback without compression
    }
  });
}

function previewImage(input, previewId) {
  const preview = document.getElementById(previewId);
  preview.innerHTML = '';
  
  if (input.files && input.files[0]) {
    const original = input.files[0];
    compressImage(original).then((compressed) => {
      // replace input file with compressed
      const dt = new DataTransfer();
      dt.items.add(compressed);
      input.files = dt.files;
      const reader = new FileReader();
      reader.onload = function(e) {
        const img = document.createElement('img');
        img.src = e.target.result;
        img.className = 'img-fluid rounded';
        img.style.maxHeight = '200px';
        img.style.objectFit = 'cover';
        preview.appendChild(img);
      };
      reader.readAsDataURL(compressed);
    });
  }
}

// Manage newly selected gallery files with removable thumbnails
let newGalleryFilesCreate = [];
const galleryInputCreate = document.getElementById('gallery_images');

function rebuildGalleryInputFilesCreate() {
  const dt = new DataTransfer();
  newGalleryFilesCreate.forEach(f => dt.items.add(f));
  if (galleryInputCreate) galleryInputCreate.files = dt.files;
}

function renderNewGalleryPreviewCreate(previewId) {
  const preview = document.getElementById(previewId);
  if (!preview) return;
  preview.innerHTML = '';
  newGalleryFilesCreate.forEach((file, index) => {
    if (!file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = function(e) {
      const col = document.createElement('div');
      col.className = 'col-6 col-md-4 position-relative draggable-thumb';
      col.dataset.idx = String(index);
      col.style.touchAction = 'none';
      col.innerHTML = `
        <img src="${e.target.result}" class="img-fluid rounded" style="aspect-ratio: 4/3; object-fit: cover; cursor: grab;">
        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeNewGalleryItemCreate(${index}, '${previewId}')">
          <i class="bi bi-x"></i>
        </button>
      `;
      preview.appendChild(col);
    };
    reader.readAsDataURL(file);
  });

  enableDragSort(preview, '.draggable-thumb', () => {
    // Recompute order based on DOM
    const orderedIdx = Array.from(preview.querySelectorAll('.draggable-thumb')).map(n => parseInt(n.dataset.idx || '0', 10));
    const newOrder = [];
    orderedIdx.forEach(i => { if (newGalleryFilesCreate[i]) newOrder.push(newGalleryFilesCreate[i]); });
    newGalleryFilesCreate = newOrder;
    rebuildGalleryInputFilesCreate();
    // Re-render to refresh dataset idx
    renderNewGalleryPreviewCreate(previewId);
  });
}

// Simple progress while preparing many images
function showCompressionProgress(count) {
  const preview = document.getElementById('gallery-preview');
  if (!preview) return;
  const barId = 'compress-progress';
  let bar = document.getElementById(barId);
  if (!bar) {
    bar = document.createElement('div');
    bar.id = barId;
    bar.className = 'w-100 mb-2';
    bar.innerHTML = '<div class="progress" style="height:8px;"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%"></div></div>';
    preview.parentElement.insertBefore(bar, preview);
  }
  const inner = bar.querySelector('.progress-bar');
  inner.style.width = Math.min(100, Math.round(count)) + '%';
  if (count >= 100) setTimeout(() => bar.remove(), 600);
}

async function previewImages(input, previewId) {
  const files = input.files ? Array.from(input.files) : [];
  const compressed = [];
  let done = 0;
  for (const f of files) {
    if (f.type && f.type.startsWith('image/')) {
      // Compress sequentially to avoid UI freeze on mobile
      /* eslint-disable no-await-in-loop */
      const c = await compressImage(f);
      compressed.push(c);
    } else {
      compressed.push(f);
    }
    done += 100 / Math.max(1, files.length);
    showCompressionProgress(done);
  }
  // Append to any previously selected files so multiple selections accumulate (with dedupe)
  const combined = newGalleryFilesCreate.concat(compressed);
  const seen = new Set();
  newGalleryFilesCreate = combined.filter(f => {
    const key = [f.name, f.size, f.lastModified].join(':');
    if (seen.has(key)) return false;
    seen.add(key);
    return true;
  });
  rebuildGalleryInputFilesCreate();
  renderNewGalleryPreviewCreate(previewId);
  // DO NOT RESET input.value - it clears the files after rebuild!
  // if (input) input.value = '';
}

function removeNewGalleryItemCreate(index, previewId) {
  newGalleryFilesCreate.splice(index, 1);
  rebuildGalleryInputFilesCreate();
  renderNewGalleryPreviewCreate(previewId);
}

// Generic drag-sort helper (pointer events) that works on mobile and desktop
function enableDragSort(container, itemSelector, onReorder) {
  let dragged = null;
  let startY = 0;
  let placeholder = null;

  function pointerMove(e) {
    if (!dragged) return;
    const pointY = e.clientY || (e.touches && e.touches[0] && e.touches[0].clientY) || startY;
    const el = document.elementFromPoint((e.clientX || (e.touches && e.touches[0] && e.touches[0].clientX) || 0), pointY);
    const target = el ? el.closest(itemSelector) : null;
    if (target && target !== placeholder) {
      if (target.compareDocumentPosition(placeholder) & Node.DOCUMENT_POSITION_FOLLOWING) {
        target.parentNode.insertBefore(placeholder, target);
      } else {
        target.parentNode.insertBefore(placeholder, target.nextSibling);
      }
    }
    e.preventDefault();
  }

  function pointerUp() {
    if (!dragged) return;
    placeholder.replaceWith(dragged);
    dragged.style.opacity = '';
    container.releasePointerCapture && container.releasePointerCapture;
    container.removeEventListener('pointermove', pointerMove);
    container.removeEventListener('pointerup', pointerUp);
    dragged = null;
    placeholder = null;
    onReorder && onReorder();
  }

  container.addEventListener('pointerdown', function(e) {
    const item = e.target.closest(itemSelector);
    if (!item) return;
    dragged = item;
    startY = e.clientY || (e.touches && e.touches[0] && e.touches[0].clientY) || 0;
    placeholder = document.createElement('div');
    placeholder.className = item.className;
    placeholder.style.height = item.offsetHeight + 'px';
    placeholder.style.width = item.offsetWidth + 'px';
    item.parentNode.insertBefore(placeholder, item.nextSibling);
    item.style.opacity = '0.4';
    container.addEventListener('pointermove', pointerMove);
    container.addEventListener('pointerup', pointerUp);
    e.preventDefault();
  });
}

// Drag and drop functionality for mobile
document.querySelectorAll('.image-upload-area').forEach(area => {
  area.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.classList.add('dragover');
  });
  
  area.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
  });
  
  area.addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
    
    const input = this.querySelector('input[type="file"]');
    if (input && e.dataTransfer.files.length > 0) {
      input.files = e.dataTransfer.files;
      input.dispatchEvent(new Event('change'));
    }
  });
});

// Form validation enhancement for mobile
document.querySelector('form').addEventListener('submit', function(e) {
  const requiredFields = this.querySelectorAll('[required]');
  let firstError = null;
  
  requiredFields.forEach(field => {
    if (!field.value.trim()) {
      field.classList.add('is-invalid');
      if (!firstError) {
        firstError = field;
      }
    } else {
      field.classList.remove('is-invalid');
    }
  });
  
  if (firstError) {
    e.preventDefault();
    firstError.focus();
    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
});

// TinyMCE Rich Text Editor for Description
document.addEventListener('DOMContentLoaded', function() {
  if (typeof tinymce !== 'undefined') {
    tinymce.init({
      selector: '#description',
      height: 350,
      menubar: false,
      language: 'es',
      plugins: [
        'advlist', 'autolink', 'lists', 'link', 'charmap', 'preview',
        'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'table', 'help', 'wordcount'
      ],
      toolbar: 'undo redo | formatselect | bold italic underline | ' +
        'alignleft aligncenter alignright alignjustify | ' +
        'bullist numlist outdent indent | removeformat | help',
      content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px }',
      branding: false,
      promotion: false
    });
  }
});
</script>

<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

@endpush
@endsection