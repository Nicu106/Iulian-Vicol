@extends('layouts.admin')

@section('title', 'Admin • Editar vehículo')

@push('styles')
  <style>
    .admin-card { border-radius: 16px; }
    .form-section-title { font-weight: 700; margin-bottom: .5rem; color: #2563eb; }
    .hint { color: #6b7280; font-size: .875rem; }
    .offer-section { border: 2px dashed #f59e0b; border-radius: 12px; padding: 1rem; background: #fffbeb; }
    .status-badge { display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
    .status-available { background: #dcfce7; color: #166534; }
    .status-draft { background: #f3f4f6; color: #374151; }
    .current-image { max-width: 150px; max-height: 100px; border-radius: 8px; border: 2px solid #e5e7eb; }
  </style>
@endpush

@section('content')
<section class="py-4">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h4 mb-0">Editar: {{ $vehicle['title'] ?? ($vehicle['brand'] . ' ' . $vehicle['model']) }}</h1>
      <div>
        <a href="{{ route('admin.vehicles.show', $vehicle['slug']) }}" class="btn btn-outline-info me-2">Ver detalles</a>
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary me-2">Lista de vehículos</a>
        <a href="{{ route('vehicle.show', $vehicle['slug']) }}" class="btn btn-outline-success" target="_blank">Ver en sitio</a>
      </div>
    </div>

    @if(session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form action="{{ route('admin.vehicles.update', $vehicle['slug']) }}" method="post" enctype="multipart/form-data" class="row g-4">
      @csrf
      <input type="hidden" name="redirect_to" value="{{ route('admin.vehicles.index') }}">

      <div class="col-lg-8">
        <!-- Basic Information -->
        <div class="card admin-card shadow-sm mb-3">
          <div class="card-body">
            <div class="form-section-title">Información básica</div>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Marca *</label>
                <input class="form-control" name="brand" required value="{{ old('brand', $vehicle['brand']) }}">
                @error('brand')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-5">
                <label class="form-label">Modelo *</label>
                <input class="form-control" name="model" required value="{{ old('model', $vehicle['model']) }}">
                @error('model')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-3">
                <label class="form-label">Año *</label>
                <input type="number" class="form-control" name="year" required min="1900" max="{{ date('Y') }}" value="{{ old('year', $vehicle['year']) }}">
                @error('year')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Precio *</label>
                <input type="number" step="0.01" class="form-control" name="price" placeholder="49900" required value="{{ old('price', $vehicle['price']) }}">
                <div class="hint">En EUR (sin símbolo €)</div>
                @error('price')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Kilometraje</label>
                <input type="number" class="form-control" name="mileage" placeholder="35000" value="{{ old('mileage', $vehicle['mileage']) }}">
                <div class="hint">Solo el número de km (sin separadores)</div>
              </div>
              <div class="col-md-4">
                <label class="form-label">Color</label>
                <input class="form-control" name="color" value="{{ old('color', $vehicle['color']) }}">
              </div>
            </div>
          </div>
        </div>

        <!-- Pricing & Offers -->
        <div class="card admin-card shadow-sm mb-3">
          <div class="card-body">
            <div class="form-section-title">Precios y ofertas</div>

            <!-- Current Pricing Display -->
            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <div class="card bg-light">
                  <div class="card-body text-center">
                    <div class="h6 text-muted">Precio actual</div>
                    <div class="h4 text-primary">€{{ number_format($vehicle['price'] ?? 0, 0, ',', '.') }}</div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card bg-light">
                  <div class="card-body text-center">
                    <div class="h6 text-muted">Precio original</div>
                    <div class="h4 text-secondary">€{{ number_format($vehicle['original_price'] ?? $vehicle['price'] ?? 0, 0, ',', '.') }}</div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card bg-light">
                  <div class="card-body text-center">
                    <div class="h6 text-muted">Discount</div>
                    @php
                      $originalPrice = $vehicle['original_price'] ?? $vehicle['price'] ?? 0;
                      $currentPrice = $vehicle['price'] ?? 0;
                      $discount = $originalPrice > 0 ? (($originalPrice - $currentPrice) / $originalPrice) * 100 : 0;
                    @endphp
                    <div class="h4 {{ $discount > 0 ? 'text-success' : 'text-muted' }}">
                      {{ $discount > 0 ? '-' . number_format($discount, 1) . '%' : '0%' }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="offer-section">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Precio original (para descuentos)</label>
                  <input type="number" step="0.01" class="form-control" name="original_price" value="{{ old('original_price', $vehicle['original_price']) }}">
                  <div class="hint">Si es diferente del precio actual</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Precio oferta</label>
                  <input type="number" step="0.01" class="form-control" name="offer_price" value="{{ old('offer_price', $vehicle['offer_price']) }}">
                  <div class="hint">Precio rebajado (si existe)</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Fecha de expiración de la oferta</label>
                  <input type="date" class="form-control" name="offer_expires_at" min="{{ date('Y-m-d', strtotime('+1 day')) }}" value="{{ old('offer_expires_at', $vehicle['offer_expires_at']) }}">
                  <div class="hint">Dejar vacío para oferta permanente</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Tipo de oferta</label>
                  <select class="form-control" name="offer_type">
                    <option value="">Sin oferta</option>
                    <option value="flash_sale" {{ old('offer_type', $vehicle['offer_type'] ?? '') == 'flash_sale' ? 'selected' : '' }}>Flash Sale</option>
                    <option value="seasonal" {{ old('offer_type', $vehicle['offer_type'] ?? '') == 'seasonal' ? 'selected' : '' }}>Oferta de temporada</option>
                    <option value="clearance" {{ old('offer_type', $vehicle['offer_type'] ?? '') == 'clearance' ? 'selected' : '' }}>Liquidación</option>
                    <option value="negotiable" {{ old('offer_type', $vehicle['offer_type'] ?? '') == 'negotiable' ? 'selected' : '' }}>Precio negociable</option>
                    <option value="promotion" {{ old('offer_type', $vehicle['offer_type'] ?? '') == 'promotion' ? 'selected' : '' }}>Promoción</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label">Descripción de la oferta</label>
                  <textarea class="form-control" name="offer_description" rows="2" placeholder="Describe la oferta (opcional)">{{ old('offer_description', $vehicle['offer_description'] ?? '') }}</textarea>
                  <div class="hint">Ej: "Descuento 15% para compras este mes"</div>
                </div>
              </div>
            </div>

            <!-- Pricing History -->
            @if(isset($vehicle['pricing_history']) && is_array($vehicle['pricing_history']))
            <div class="mt-3">
              <div class="h6">Historial de precios:</div>
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>Fecha</th>
                      <th>Precio</th>
                      <th>Tipo</th>
                      <th>Motivo</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($vehicle['pricing_history'] as $history)
                    <tr>
                      <td>{{ \Carbon\Carbon::parse($history['date'])->format('d.m.Y') }}</td>
                      <td>€{{ number_format($history['price'], 0, ',', '.') }}</td>
                      <td>{{ $history['type'] }}</td>
                      <td>{{ $history['reason'] }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
            @endif
          </div>
        </div>

        <!-- Technical Details -->
        <div class="card admin-card shadow-sm mb-3">
          <div class="card-body">
            <div class="form-section-title">Detalles técnicos</div>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Combustible</label>
                <select class="form-control" name="fuel">
                  <option value="">Seleccionar</option>
                  <option value="Gasolina" {{ old('fuel', $vehicle['fuel']) == 'Gasolina' ? 'selected' : '' }}>Gasolina</option>
                  <option value="Diesel" {{ old('fuel', $vehicle['fuel']) == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                  <option value="Híbrido" {{ old('fuel', $vehicle['fuel']) == 'Híbrido' ? 'selected' : '' }}>Híbrido</option>
                  <option value="Eléctrico" {{ old('fuel', $vehicle['fuel']) == 'Eléctrico' ? 'selected' : '' }}>Eléctrico</option>
                  <option value="GLP" {{ old('fuel', $vehicle['fuel']) == 'GLP' ? 'selected' : '' }}>GLP</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Transmisión</label>
                <select class="form-control" name="transmission">
                  <option value="">Seleccionar</option>
                  <option value="Manual" {{ old('transmission', $vehicle['transmission']) == 'Manual' ? 'selected' : '' }}>Manual</option>
                  <option value="Automático" {{ old('transmission', $vehicle['transmission']) == 'Automático' ? 'selected' : '' }}>Automático</option>
                  <option value="CVT" {{ old('transmission', $vehicle['transmission']) == 'CVT' ? 'selected' : '' }}>CVT</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Carrocería</label>
                <select class="form-control" name="body_type">
                  <option value="">Seleccionar</option>
                  <option value="SUV" {{ old('body_type', $vehicle['body_type'] ?? '') == 'SUV' ? 'selected' : '' }}>SUV</option>
                  <option value="Sedan" {{ old('body_type', $vehicle['body_type'] ?? '') == 'Sedan' ? 'selected' : '' }}>Berlina</option>
                  <option value="Hatchback" {{ old('body_type', $vehicle['body_type'] ?? '') == 'Hatchback' ? 'selected' : '' }}>Hatchback</option>
                  <option value="Break" {{ old('body_type', $vehicle['body_type'] ?? '') == 'Break' ? 'selected' : '' }}>Familiar</option>
                  <option value="Coupe" {{ old('body_type', $vehicle['body_type'] ?? '') == 'Coupe' ? 'selected' : '' }}>Coupé</option>
                  <option value="Convertible" {{ old('body_type', $vehicle['body_type'] ?? '') == 'Convertible' ? 'selected' : '' }}>Descapotable</option>
                  <option value="Monovolumen" {{ old('body_type', $vehicle['body_type'] ?? '') == 'Monovolumen' ? 'selected' : '' }}>Monovolumen</option>
                  <option value="Pickup" {{ old('body_type', $vehicle['body_type'] ?? '') == 'Pickup' ? 'selected' : '' }}>Pickup</option>
                  <option value="Furgoneta" {{ old('body_type', $vehicle['body_type'] ?? '') == 'Furgoneta' ? 'selected' : '' }}>Furgoneta</option>
                  <option value="Todoterreno" {{ old('body_type', $vehicle['body_type'] ?? '') == 'Todoterreno' ? 'selected' : '' }}>Todoterreno</option>
                  <option value="Otro" {{ old('body_type', $vehicle['body_type'] ?? '') == 'Otro' ? 'selected' : '' }}>Otro</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Motor</label>
                <input class="form-control" name="engine" placeholder="3.0L" value="{{ old('engine', $vehicle['engine']) }}">
              </div>
              <div class="col-md-4">
                <label class="form-label">Potencia</label>
                <input class="form-control" name="power" placeholder="286 CV" value="{{ old('power', $vehicle['power']) }}">
              </div>
              <div class="col-md-4">
                <label class="form-label">Tracción</label>
                <select class="form-control" name="drivetrain">
                  <option value="">Seleccionar</option>
                  <option value="FWD" {{ old('drivetrain', $vehicle['drivetrain']) == 'FWD' ? 'selected' : '' }}>FWD (Delantera)</option>
                  <option value="RWD" {{ old('drivetrain', $vehicle['drivetrain']) == 'RWD' ? 'selected' : '' }}>RWD (Trasera)</option>
                  <option value="AWD" {{ old('drivetrain', $vehicle['drivetrain']) == 'AWD' ? 'selected' : '' }}>AWD (Integral)</option>
                  <option value="4WD" {{ old('drivetrain', $vehicle['drivetrain']) == '4WD' ? 'selected' : '' }}>4WD (4x4)</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Etiqueta medioambiental</label>
                <input class="form-control" name="vin" value="{{ old('vin', $vehicle['vin']) }}" placeholder="Etiqueta medioambiental">
              </div>
              <div class="col-12">
                <label class="form-label">Estado</label>
                <select class="form-control" name="condition">
                  <option value="">Seleccionar</option>
                  <option value="Nuevo" {{ old('condition', $vehicle['condition']) == 'Nuevo' ? 'selected' : '' }}>Nuevo</option>
                  <option value="Excelente" {{ old('condition', $vehicle['condition']) == 'Excelente' ? 'selected' : '' }}>Excelente</option>
                  <option value="Muy bueno" {{ old('condition', $vehicle['condition']) == 'Muy bueno' ? 'selected' : '' }}>Muy bueno</option>
                  <option value="Bueno" {{ old('condition', $vehicle['condition']) == 'Bueno' ? 'selected' : '' }}>Bueno</option>
                  <option value="Aceptable" {{ old('condition', $vehicle['condition']) == 'Aceptable' ? 'selected' : '' }}>Aceptable</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Content & Media -->
        <div class="card admin-card shadow-sm mb-3">
          <div class="card-body">
            <div class="form-section-title">Contenido y medios</div>
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Descripción</label>
                <textarea id="description" class="form-control" name="description" rows="8" placeholder="Detalles clave y beneficios">{{ old('description', $vehicle['description']) }}</textarea>
                <div class="hint">Usa el editor para formatear el texto (negrita, cursiva, listas, etc.)</div>
              </div>
              <div class="col-12">
                <label class="form-label">Equipamiento (separado por comas)</label>
                <input class="form-control" name="features" placeholder="LED Matrix, Paquete M, Techo panorámico, Sensores de aparcamiento" value="{{ old('features', is_array($vehicle['features'] ?? null) ? implode(', ', $vehicle['features']) : $vehicle['features']) }}">
              </div>

              <!-- Current Images Display -->
              @if(!empty($vehicle['cover_image']))
                <div class="col-12">
                  <label class="form-label">Imagen actual (portada)</label>
                  <div class="mb-2">
                    <img src="{{ $vehicle['cover_image'] }}" alt="Portada actual" class="current-image">
                  </div>
                </div>
              @endif

              @if(!empty($vehicle['gallery_images']))
              <div class="col-12">
                <label class="form-label">Imágenes existentes en galería</label>
                <div id="existing_gallery" class="d-flex flex-wrap gap-2">
                  @foreach($vehicle['gallery_images'] as $img)
                    <div class="position-relative" data-url="{{ $img }}">
                      <img src="{{ $img }}" style="width: 100px; height: 75px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;">
                      <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeExistingGalleryItem(this, '{{ $img }}')">
                        <i class="bi bi-x"></i>
                      </button>
                    </div>
                  @endforeach
                </div>
                <input type="hidden" name="removed_gallery_images" id="removed_gallery_images" />
              </div>
              @endif

              @if(!empty($vehicle['images']))
              <div class="col-12">
                <label class="form-label">Imágenes cargadas (Vende tu coche)</label>
                <div class="d-flex flex-wrap gap-2">
                  @php
                    $imagesField = is_string($vehicle['images']) ? json_decode($vehicle['images'], true) : $vehicle['images'];
                  @endphp
                  @if(is_array($imagesField))
                    @foreach($imagesField as $img)
                      @php
                        $isAbs = is_string($img) && preg_match('/^https?:\/\//i', $img);
                        $imgUrl = $isAbs ? $img : (is_string($img) ? Storage::url($img) : null);
                      @endphp
                      @if($imgUrl)
                        <img src="{{ $imgUrl }}" style="width: 100px; height: 75px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;">
                      @endif
                    @endforeach
                  @endif
                </div>
              </div>
              @endif

              <div class="col-md-6">
                <label class="form-label">{{ !empty($vehicle['cover_image']) ? 'Reemplazar' : 'Añadir' }} imagen principal</label>
                <input type="file" name="cover_image" accept="image/*" class="form-control" id="cover_image" onchange="previewImage(this, 'cover_preview')">
                <div class="hint">PNG/JPG cualquier tamaño</div>
                <div id="cover_preview" class="mt-2" style="display: none;">
                  <img style="max-width: 150px; max-height: 100px; border-radius: 8px; border: 2px solid #e5e7eb;">
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Añadir imágenes a la galería</label>
                <input type="file" name="gallery_images[]" accept="image/*" class="form-control" multiple id="gallery_images">
                <div class="hint">Puedes subir varias imágenes</div>
                <div id="gallery_preview" class="mt-2 d-flex flex-wrap gap-2"></div>
              </div>
              <div class="col-12">
                <label class="form-label">Link video (YouTube/Vimeo)</label>
                <input type="url" class="form-control" name="video_url" placeholder="https://youtu.be/..." value="{{ old('video_url', $vehicle['video_url']) }}">
              </div>
            </div>
          </div>
        </div>

        <!-- SEO & Marketing -->
        <div class="card admin-card shadow-sm">
          <div class="card-body">
            <div class="form-section-title">SEO y marketing</div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Título META (SEO)</label>
                <input class="form-control" name="meta_title" maxlength="60" value="{{ old('meta_title', $vehicle['meta_title']) }}">
                <div class="hint">Máximo 60 caracteres</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Ubicación</label>
                <input class="form-control" name="location" placeholder="Santander, Cantabria" value="{{ old('location', $vehicle['location']) }}">
              </div>
              <div class="col-12">
                <label class="form-label">Descripción META (SEO)</label>
                <textarea class="form-control" name="meta_description" rows="2" maxlength="160">{{ old('meta_description', $vehicle['meta_description']) }}</textarea>
                <div class="hint">Máximo 160 caracteres</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Insignias (separadas por comas)</label>
                <input class="form-control" name="badges" placeholder="Nuevo, Garantía, Financiación" value="{{ old('badges', is_array($vehicle['badges'] ?? null) ? implode(', ', $vehicle['badges']) : $vehicle['badges']) }}">
              </div>
              <div class="col-md-6">
                <label class="form-label">Etiquetas (separadas por comas)</label>
                <input class="form-control" name="tags" placeholder="sport, family, luxury" value="{{ old('tags', is_array($vehicle['tags'] ?? null) ? implode(', ', $vehicle['tags']) : $vehicle['tags']) }}">
              </div>
              <div class="col-12">
                <label class="form-label">Notas internas</label>
                <textarea class="form-control" name="internal_notes" rows="2" placeholder="Notas privadas para el equipo">{{ old('internal_notes', $vehicle['internal_notes']) }}</textarea>
                <div class="hint">Estas notas no serán visibles en el sitio</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <!-- Actions & Status -->
        <div class="card admin-card shadow-sm mb-3">
          <div class="card-body">
            <div class="form-section-title">Acciones y estado</div>
            <div class="d-grid gap-2 mb-3">
              <button type="submit" class="btn btn-primary btn-lg">Actualizar vehículo</button>
              <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>

            <div class="mb-3">
              <label class="form-label">Estado del vehículo</label>
              <select class="form-control" name="status">
                <option value="available" {{ old('status', $vehicle['status']) == 'available' ? 'selected' : '' }}>Disponible</option>
                <option value="draft" {{ old('status', $vehicle['status']) == 'draft' ? 'selected' : '' }}>Borrador</option>
                <option value="reserved" {{ old('status', $vehicle['status']) == 'reserved' ? 'selected' : '' }}>Reservado</option>
                <option value="sold" {{ old('status', $vehicle['status']) == 'sold' ? 'selected' : '' }}>Vendido</option>
                <option value="maintenance" {{ old('status', $vehicle['status']) == 'maintenance' ? 'selected' : '' }}>En servicio</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Prioridad de visualización</label>
              <input type="number" class="form-control" name="priority" min="0" max="999" value="{{ old('priority', $vehicle['priority'] ?? 0) }}">
              <div class="hint">0 = normal, mayor = más importante</div>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="featured" id="featured" {{ old('featured', $vehicle['featured']) ? 'checked' : '' }}>
              <label class="form-check-label" for="featured">
                <strong>Vehículo recomendado</strong>
              </label>
              <div class="hint">Aparecerá en la sección especial</div>
            </div>
          </div>
        </div>

        <!-- Current Info -->
        <div class="card admin-card shadow-sm">
          <div class="card-body">
            <div class="form-section-title">Información actual</div>
            <div class="small text-muted">
              <p><strong>Creado:</strong> {{ isset($vehicle['created_at']) ? date('d.m.Y H:i', strtotime($vehicle['created_at'])) : 'N/A' }}</p>
              <p><strong>Actualizado:</strong> {{ isset($vehicle['updated_at']) ? date('d.m.Y H:i', strtotime($vehicle['updated_at'])) : 'N/A' }}</p>
              <p><strong>Visualizaciones:</strong> {{ $vehicle['views_count'] ?? 0 }}</p>
              <p><strong>Consultas:</strong> {{ $vehicle['inquiries_count'] ?? 0 }}</p>
              @if(!empty($vehicle['sold_date']))
                <p><strong>Vendido el:</strong> {{ date('d.m.Y', strtotime($vehicle['sold_date'])) }}</p>
              @endif
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>

@push('scripts')
<script>
// Prevent double submit for better performance
let isSubmitting = false;
document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('form[method="post"]');
  if (form) {
    form.addEventListener('submit', function(e) {
      if (isSubmitting) {
        e.preventDefault();
        return false;
      }
      isSubmitting = true;
      
      // Re-enable after 10 seconds (in case of validation errors)
      setTimeout(() => {
        isSubmitting = false;
      }, 10000);
      
      // Disable submit button
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
      }
    });
  }
});

function previewImage(input, previewId) {
  const preview = document.getElementById(previewId);
  const img = preview.querySelector('img');

  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      img.src = e.target.result;
      preview.style.display = 'block';
    }
    reader.readAsDataURL(input.files[0]);
  } else {
    preview.style.display = 'none';
  }
}

// Manage newly selected gallery files with removable thumbnails
let newGalleryFiles = [];
const galleryInput = document.getElementById('gallery_images');

function rebuildGalleryInputFiles() {
  const dt = new DataTransfer();
  newGalleryFiles.forEach(f => dt.items.add(f));
  if (galleryInput) galleryInput.files = dt.files;
}

function renderNewGalleryPreview(previewId) {
  const preview = document.getElementById(previewId);
  if (!preview) return;
  preview.innerHTML = '';
  newGalleryFiles.forEach((file, index) => {
    if (!file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = function(e) {
      const wrapper = document.createElement('div');
      wrapper.className = 'position-relative draggable-thumb';
      wrapper.dataset.idx = String(index);
      wrapper.style.width = '80px';
      wrapper.style.height = '60px';
      wrapper.style.touchAction = 'none';
      wrapper.innerHTML = `
        <img src="${e.target.result}" style="width: 80px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb; cursor: grab;">
        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeNewGalleryItem(${index}, '${previewId}')">
          <i class="bi bi-x"></i>
        </button>
      `;
      preview.appendChild(wrapper);
    };
    reader.readAsDataURL(file);
  });

  enableDragSort(document.getElementById(previewId), '.draggable-thumb', () => {
    const container = document.getElementById(previewId);
    const orderedIdx = Array.from(container.querySelectorAll('.draggable-thumb')).map(n => parseInt(n.dataset.idx || '0', 10));
    const newOrder = [];
    orderedIdx.forEach(i => { if (newGalleryFiles[i]) newOrder.push(newGalleryFiles[i]); });
    newGalleryFiles = newOrder;
    rebuildGalleryInputFiles();
    renderNewGalleryPreview(previewId);
  });
}

async function compressImage(file, maxWidth = 1600, maxHeight = 1600, quality = 0.85) {
  if (file.size && file.size <= 600 * 1024) return file;
  return new Promise((resolve) => {
    try {
      const useImageBitmap = 'createImageBitmap' in window;
      const run = (bitmap) => {
        const ratio = Math.min(maxWidth / bitmap.width, maxHeight / bitmap.height, 1);
        if (ratio >= 1) { resolve(file); return; }
        const targetW = Math.round(bitmap.width * ratio);
        const targetH = Math.round(bitmap.height * ratio);
        if (typeof OffscreenCanvas !== 'undefined') {
          const canvas = new OffscreenCanvas(targetW, targetH);
          const ctx = canvas.getContext('2d');
          ctx.drawImage(bitmap, 0, 0, targetW, targetH);
          canvas.convertToBlob({ type: 'image/jpeg', quality }).then((blob) => {
            const outName = file.name.replace(/\.[^.]+$/, '') + '.jpg';
            resolve(new File([blob], outName, { type: 'image/jpeg', lastModified: Date.now() }));
          }).catch(() => resolve(file));
        } else {
          const canvas = document.createElement('canvas');
          canvas.width = targetW; canvas.height = targetH;
          canvas.getContext('2d').drawImage(bitmap, 0, 0, targetW, targetH);
          canvas.toBlob((blob) => {
            const outName = file.name.replace(/\.[^.]+$/, '') + '.jpg';
            resolve(new File([blob], outName, { type: 'image/jpeg', lastModified: Date.now() }));
          }, 'image/jpeg', quality);
        }
      };
      if (useImageBitmap) {
        createImageBitmap(file).then(run).catch(() => resolve(file));
      } else {
        const img = new Image();
        const reader = new FileReader();
        reader.onload = function(e) {
          img.onload = function() { run(img); };
          img.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    } catch (err) {
      resolve(file);
    }
  });
}

async function previewGallery(input, previewId) {
  const files = input.files ? Array.from(input.files) : [];
  const compressed = [];
  let done = 0;
  const barId = 'compress-progress-edit';
  const container = document.getElementById('gallery_preview')?.parentElement;
  let bar = document.getElementById(barId);
  if (container && !bar) {
    bar = document.createElement('div');
    bar.id = barId;
    bar.className = 'w-100 mb-2';
    bar.innerHTML = '<div class="progress" style="height:8px;"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%"></div></div>';
    container.insertBefore(bar, document.getElementById('gallery_preview'));
  }
  for (const f of files) {
    if (f.type && f.type.startsWith('image/')) {
      /* eslint-disable no-await-in-loop */
      const c = await compressImage(f);
      compressed.push(c);
    } else {
      compressed.push(f);
    }
    done += 100 / Math.max(1, files.length);
    if (bar) bar.querySelector('.progress-bar').style.width = Math.min(100, Math.round(done)) + '%';
  }
  if (bar) setTimeout(() => bar.remove(), 600);
  // Append to existing pending files so previous selections are kept (with dedupe)
  const combined = newGalleryFiles.concat(compressed);
  const seen = new Set();
  newGalleryFiles = combined.filter(f => {
    const key = [f.name, f.size, f.lastModified].join(':');
    if (seen.has(key)) return false;
    seen.add(key);
    return true;
  });
  rebuildGalleryInputFiles();
  renderNewGalleryPreview(previewId);
  if (input) input.value = '';
}

function removeNewGalleryItem(index, previewId) {
  newGalleryFiles.splice(index, 1);
  rebuildGalleryInputFiles();
  renderNewGalleryPreview(previewId);
}

// Remove existing gallery item
let removedGalleryList = [];
function removeExistingGalleryItem(button, url) {
  if (!removedGalleryList.includes(url)) {
    removedGalleryList.push(url);
  }
  const hidden = document.getElementById('removed_gallery_images');
  if (hidden) {
    hidden.value = JSON.stringify(removedGalleryList);
  }
  const wrapper = button.closest('[data-url]');
  if (wrapper) {
    wrapper.remove();
  }
}

// Reuse the same helper from create page
function enableDragSort(container, itemSelector, onReorder) {
  let dragged = null;
  let startY = 0;
  let placeholder = null;
  function move(e) {
    if (!dragged) return;
    const y = e.clientY || (e.touches && e.touches[0] && e.touches[0].clientY) || startY;
    const x = e.clientX || (e.touches && e.touches[0] && e.touches[0].clientX) || 0;
    const el = document.elementFromPoint(x, y);
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
  function up() {
    if (!dragged) return;
    placeholder.replaceWith(dragged);
    dragged.style.opacity = '';
    container.removeEventListener('pointermove', move);
    container.removeEventListener('pointerup', up);
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
    container.addEventListener('pointermove', move);
    container.addEventListener('pointerup', up);
    e.preventDefault();
  });
}

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
