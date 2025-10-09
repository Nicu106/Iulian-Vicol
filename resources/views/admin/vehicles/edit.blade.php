@extends('layouts.admin')

@section('title', 'Admin • Editează vehicul')

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
      <h1 class="h4 mb-0">Editează: {{ $vehicle['title'] ?? ($vehicle['brand'] . ' ' . $vehicle['model']) }}</h1>
      <div>
        <a href="{{ route('admin.vehicles.show', $vehicle['slug']) }}" class="btn btn-outline-info me-2">Vezi detalii</a>
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary me-2">Lista vehicule</a>
        <a href="{{ route('vehicle.show', $vehicle['slug']) }}" class="btn btn-outline-success" target="_blank">Vezi pe site</a>
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
                <label class="form-label">Kilometraj</label>
                <input type="number" class="form-control" name="mileage" placeholder="35000" value="{{ old('mileage', $vehicle['mileage']) }}">
                <div class="hint">Doar numărul de km (fără separatori)</div>
              </div>
              <div class="col-md-4">
                <label class="form-label">Culoare</label>
                <input class="form-control" name="color" value="{{ old('color', $vehicle['color']) }}">
              </div>
            </div>
          </div>
        </div>

        <!-- Pricing & Offers -->
        <div class="card admin-card shadow-sm mb-3">
          <div class="card-body">
            <div class="form-section-title">Prețuri și oferte</div>
            
            <!-- Current Pricing Display -->
            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <div class="card bg-light">
                  <div class="card-body text-center">
                    <div class="h6 text-muted">Preț curent</div>
                    <div class="h4 text-primary">€{{ number_format($vehicle['price'] ?? 0, 0, ',', '.') }}</div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card bg-light">
                  <div class="card-body text-center">
                    <div class="h6 text-muted">Preț original</div>
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
                  <label class="form-label">Preț original (pentru reduceri)</label>
                  <input type="number" step="0.01" class="form-control" name="original_price" value="{{ old('original_price', $vehicle['original_price']) }}">
                  <div class="hint">Dacă este diferit de prețul curent</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Preț ofertă</label>
                  <input type="number" step="0.01" class="form-control" name="offer_price" value="{{ old('offer_price', $vehicle['offer_price']) }}">
                  <div class="hint">Prețul redus (dacă există)</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Data expirării ofertei</label>
                  <input type="date" class="form-control" name="offer_expires_at" min="{{ date('Y-m-d', strtotime('+1 day')) }}" value="{{ old('offer_expires_at', $vehicle['offer_expires_at']) }}">
                  <div class="hint">Lasă gol pentru ofertă permanentă</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Tip ofertă</label>
                  <select class="form-control" name="offer_type">
                    <option value="">Fără ofertă</option>
                    <option value="flash_sale" {{ old('offer_type', $vehicle['offer_type'] ?? '') == 'flash_sale' ? 'selected' : '' }}>Flash Sale</option>
                    <option value="seasonal" {{ old('offer_type', $vehicle['offer_type'] ?? '') == 'seasonal' ? 'selected' : '' }}>Ofertă sezonieră</option>
                    <option value="clearance" {{ old('offer_type', $vehicle['offer_type'] ?? '') == 'clearance' ? 'selected' : '' }}>Lichidare stoc</option>
                    <option value="negotiable" {{ old('offer_type', $vehicle['offer_type'] ?? '') == 'negotiable' ? 'selected' : '' }}>Preț negociabil</option>
                    <option value="promotion" {{ old('offer_type', $vehicle['offer_type'] ?? '') == 'promotion' ? 'selected' : '' }}>Promoție</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label">Descriere ofertă</label>
                  <textarea class="form-control" name="offer_description" rows="2" placeholder="Descrie oferta (opțional)">{{ old('offer_description', $vehicle['offer_description'] ?? '') }}</textarea>
                  <div class="hint">Ex: "Reducere 15% pentru achiziții în această lună"</div>
                </div>
              </div>
            </div>

            <!-- Pricing History -->
            @if(isset($vehicle['pricing_history']) && is_array($vehicle['pricing_history']))
            <div class="mt-3">
              <div class="h6">Istoric prețuri:</div>
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>Data</th>
                      <th>Preț</th>
                      <th>Tip</th>
                      <th>Motiv</th>
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
            <div class="form-section-title">Detalii tehnice</div>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Combustibil</label>
                <select class="form-control" name="fuel">
                  <option value="">Selectează</option>
                  <option value="Benzină" {{ old('fuel', $vehicle['fuel']) == 'Benzină' ? 'selected' : '' }}>Benzină</option>
                  <option value="Diesel" {{ old('fuel', $vehicle['fuel']) == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                  <option value="Hibrid" {{ old('fuel', $vehicle['fuel']) == 'Hibrid' ? 'selected' : '' }}>Hibrid</option>
                  <option value="Electric" {{ old('fuel', $vehicle['fuel']) == 'Electric' ? 'selected' : '' }}>Electric</option>
                  <option value="GPL" {{ old('fuel', $vehicle['fuel']) == 'GPL' ? 'selected' : '' }}>GPL</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Transmisie</label>
                <select class="form-control" name="transmission">
                  <option value="">Selectează</option>
                  <option value="Manuală" {{ old('transmission', $vehicle['transmission']) == 'Manuală' ? 'selected' : '' }}>Manuală</option>
                  <option value="Automată" {{ old('transmission', $vehicle['transmission']) == 'Automată' ? 'selected' : '' }}>Automată</option>
                  <option value="CVT" {{ old('transmission', $vehicle['transmission']) == 'CVT' ? 'selected' : '' }}>CVT</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Motor</label>
                <input class="form-control" name="engine" placeholder="3.0L" value="{{ old('engine', $vehicle['engine']) }}">
              </div>
              <div class="col-md-4">
                <label class="form-label">Putere</label>
                <input class="form-control" name="power" placeholder="286 CP" value="{{ old('power', $vehicle['power']) }}">
              </div>
              <div class="col-md-4">
                <label class="form-label">Tracțiune</label>
                <select class="form-control" name="drivetrain">
                  <option value="">Selectează</option>
                  <option value="FWD" {{ old('drivetrain', $vehicle['drivetrain']) == 'FWD' ? 'selected' : '' }}>FWD (Față)</option>
                  <option value="RWD" {{ old('drivetrain', $vehicle['drivetrain']) == 'RWD' ? 'selected' : '' }}>RWD (Spate)</option>
                  <option value="AWD" {{ old('drivetrain', $vehicle['drivetrain']) == 'AWD' ? 'selected' : '' }}>AWD (Integrală)</option>
                  <option value="4WD" {{ old('drivetrain', $vehicle['drivetrain']) == '4WD' ? 'selected' : '' }}>4WD (4x4)</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">VIN</label>
                <input class="form-control" name="vin" value="{{ old('vin', $vehicle['vin']) }}">
              </div>
              <div class="col-12">
                <label class="form-label">Stare</label>
                <select class="form-control" name="condition">
                  <option value="">Selectează</option>
                  <option value="Nouă" {{ old('condition', $vehicle['condition']) == 'Nouă' ? 'selected' : '' }}>Nouă</option>
                  <option value="Excelentă" {{ old('condition', $vehicle['condition']) == 'Excelentă' ? 'selected' : '' }}>Excelentă</option>
                  <option value="Foarte bună" {{ old('condition', $vehicle['condition']) == 'Foarte bună' ? 'selected' : '' }}>Foarte bună</option>
                  <option value="Bună" {{ old('condition', $vehicle['condition']) == 'Bună' ? 'selected' : '' }}>Bună</option>
                  <option value="Acceptabilă" {{ old('condition', $vehicle['condition']) == 'Acceptabilă' ? 'selected' : '' }}>Acceptabilă</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Content & Media -->
        <div class="card admin-card shadow-sm mb-3">
          <div class="card-body">
            <div class="form-section-title">Conținut și media</div>
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Descriere</label>
                <textarea class="form-control" name="description" rows="4" placeholder="Detalii cheie și beneficii">{{ old('description', $vehicle['description']) }}</textarea>
              </div>
              <div class="col-12">
                <label class="form-label">Dotări (separate prin virgulă)</label>
                <input class="form-control" name="features" placeholder="LED Matrix, Pachet M, Panoramic, Senzori parcare" value="{{ old('features', is_array($vehicle['features'] ?? null) ? implode(', ', $vehicle['features']) : $vehicle['features']) }}">
              </div>

              <!-- Current Images Display -->
              @if(!empty($vehicle['cover_image']))
                <div class="col-12">
                  <label class="form-label">Imagine actuală (cover)</label>
                  <div class="mb-2">
                    <img src="{{ $vehicle['cover_image'] }}" alt="Cover actual" class="current-image">
                  </div>
                </div>
              @endif

              @if(!empty($vehicle['gallery_images']))
              <div class="col-12">
                <label class="form-label">Imagini existente în galerie</label>
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

              <div class="col-md-6">
                <label class="form-label">{{ !empty($vehicle['cover_image']) ? 'Înlocuiește' : 'Adaugă' }} imagine principală</label>
                <input type="file" name="cover_image" accept="image/*" class="form-control" id="cover_image" onchange="previewImage(this, 'cover_preview')">
                <div class="hint">PNG/JPG orice dimensiune</div>
                <div id="cover_preview" class="mt-2" style="display: none;">
                  <img style="max-width: 150px; max-height: 100px; border-radius: 8px; border: 2px solid #e5e7eb;">
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Adaugă imagini la galerie</label>
                <input type="file" name="gallery_images[]" accept="image/*" class="form-control" multiple id="gallery_images" onchange="previewGallery(this, 'gallery_preview')">
                <div class="hint">Poți încărca mai multe imagini</div>
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
            <div class="form-section-title">SEO și marketing</div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Titlu META (SEO)</label>
                <input class="form-control" name="meta_title" maxlength="60" value="{{ old('meta_title', $vehicle['meta_title']) }}">
                <div class="hint">Maxim 60 caractere</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Locație</label>
                <input class="form-control" name="location" placeholder="București, Sector 1" value="{{ old('location', $vehicle['location']) }}">
              </div>
              <div class="col-12">
                <label class="form-label">Descriere META (SEO)</label>
                <textarea class="form-control" name="meta_description" rows="2" maxlength="160">{{ old('meta_description', $vehicle['meta_description']) }}</textarea>
                <div class="hint">Maxim 160 caractere</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Badge-uri (separate prin virgulă)</label>
                <input class="form-control" name="badges" placeholder="Nou, Garanție, Finanțare" value="{{ old('badges', is_array($vehicle['badges'] ?? null) ? implode(', ', $vehicle['badges']) : $vehicle['badges']) }}">
              </div>
              <div class="col-md-6">
                <label class="form-label">Tag-uri (separate prin virgulă)</label>
                <input class="form-control" name="tags" placeholder="sport, family, luxury" value="{{ old('tags', is_array($vehicle['tags'] ?? null) ? implode(', ', $vehicle['tags']) : $vehicle['tags']) }}">
              </div>
              <div class="col-12">
                <label class="form-label">Note interne</label>
                <textarea class="form-control" name="internal_notes" rows="2" placeholder="Note private pentru echipă">{{ old('internal_notes', $vehicle['internal_notes']) }}</textarea>
                <div class="hint">Aceste note nu vor fi vizibile pe site</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <!-- Actions & Status -->
        <div class="card admin-card shadow-sm mb-3">
          <div class="card-body">
            <div class="form-section-title">Acțiuni și status</div>
            <div class="d-grid gap-2 mb-3">
              <button type="submit" class="btn btn-primary btn-lg">Actualizează vehicul</button>
              <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary">Anulează</a>
            </div>

            <div class="mb-3">
              <label class="form-label">Status vehicul</label>
              <select class="form-control" name="status">
                <option value="available" {{ old('status', $vehicle['status']) == 'available' ? 'selected' : '' }}>Disponibil</option>
                <option value="draft" {{ old('status', $vehicle['status']) == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="reserved" {{ old('status', $vehicle['status']) == 'reserved' ? 'selected' : '' }}>Rezervat</option>
                <option value="sold" {{ old('status', $vehicle['status']) == 'sold' ? 'selected' : '' }}>Vândut</option>
                <option value="maintenance" {{ old('status', $vehicle['status']) == 'maintenance' ? 'selected' : '' }}>În service</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Prioritate afișare</label>
              <input type="number" class="form-control" name="priority" min="0" max="999" value="{{ old('priority', $vehicle['priority'] ?? 0) }}">
              <div class="hint">0 = normal, mai mare = mai important</div>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="featured" id="featured" {{ old('featured', $vehicle['featured']) ? 'checked' : '' }}>
              <label class="form-check-label" for="featured">
                <strong>Vehicul recomandat</strong>
              </label>
              <div class="hint">Va apărea în secțiunea specială</div>
            </div>
          </div>
        </div>

        <!-- Current Info -->
        <div class="card admin-card shadow-sm">
          <div class="card-body">
            <div class="form-section-title">Informații curente</div>
            <div class="small text-muted">
              <p><strong>Creat:</strong> {{ isset($vehicle['created_at']) ? date('d.m.Y H:i', strtotime($vehicle['created_at'])) : 'N/A' }}</p>
              <p><strong>Actualizat:</strong> {{ isset($vehicle['updated_at']) ? date('d.m.Y H:i', strtotime($vehicle['updated_at'])) : 'N/A' }}</p>
              <p><strong>Vizualizări:</strong> {{ $vehicle['views_count'] ?? 0 }}</p>
              <p><strong>Întrebări:</strong> {{ $vehicle['inquiries_count'] ?? 0 }}</p>
              @if(!empty($vehicle['sold_date']))
                <p><strong>Vândut la:</strong> {{ date('d.m.Y', strtotime($vehicle['sold_date'])) }}</p>
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
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Se salvează...';
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

function previewGallery(input, previewId) {
  const preview = document.getElementById(previewId);
  preview.innerHTML = '';

  if (input.files) {
    Array.from(input.files).forEach((file, index) => {
      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const div = document.createElement('div');
          div.innerHTML = `<img src="${e.target.result}" style="width: 80px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;">`;
          preview.appendChild(div);
        }
        reader.readAsDataURL(file);
      }
    });
  }
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
</script>
@endpush

@endsection
