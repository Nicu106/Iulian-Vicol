@extends('layouts.admin')

@section('title', 'Admin • Adaugă vehicul')

@push('styles')
  <style>
    .admin-card { border-radius: 16px; }
    .form-section-title { font-weight: 700; margin-bottom: .5rem; color: #2563eb; }
    .hint { color: #6b7280; font-size: .875rem; }
    .offer-section { border: 2px dashed #f59e0b; border-radius: 12px; padding: 1rem; background: #fffbeb; }
    .status-badge { display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
    .status-available { background: #dcfce7; color: #166534; }
    .status-draft { background: #f3f4f6; color: #374151; }
  </style>
@endpush

@section('content')
<section class="py-4">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h4 mb-0">Adaugă vehicul nou</h1>
      <div>
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary me-2">Lista vehicule</a>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary">Vezi site</a>
      </div>
    </div>

    @if(session('status'))
      <div class="alert alert-success">
        {{ session('status') }}
        @if(session('preview_url'))
          <a href="{{ session('preview_url') }}" class="ms-2">Vezi previzualizare</a>
        @endif
      </div>
    @endif

    <form action="{{ route('admin.vehicles.store') }}" method="post" enctype="multipart/form-data" class="row g-4">
      @csrf
      <div class="col-lg-8">
        <!-- Basic Information -->
        <div class="card admin-card shadow-sm mb-3">
          <div class="card-body">
            <div class="form-section-title">Información básica</div>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Marca *</label>
                <input class="form-control" name="brand" required value="{{ old('brand') }}">
                @error('brand')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-5">
                <label class="form-label">Modelo *</label>
                <input class="form-control" name="model" required value="{{ old('model') }}">
                @error('model')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-3">
                <label class="form-label">Año *</label>
                <input type="number" class="form-control" name="year" required min="1900" max="{{ date('Y') }}" value="{{ old('year', date('Y')) }}">
                @error('year')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Precio *</label>
                <input type="number" step="0.01" class="form-control" name="price" placeholder="49900" required value="{{ old('price') }}">
                <div class="hint">En EUR (sin símbolo €)</div>
                @error('price')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Kilometraj</label>
                <input type="number" class="form-control" name="mileage" placeholder="35000" min="0" step="1" value="{{ old('mileage') }}">
                @error('mileage')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Culoare</label>
                <input class="form-control" name="color" value="{{ old('color') }}">
              </div>
            </div>
          </div>
        </div>

        <!-- Pricing & Offers -->
        <div class="card admin-card shadow-sm mb-3">
          <div class="card-body">
            <div class="form-section-title">Prețuri și oferte</div>
            <div class="offer-section">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Preț original (pentru reduceri)</label>
                  <input type="number" step="0.01" class="form-control" name="original_price" value="{{ old('original_price') }}">
                  <div class="hint">Dacă este diferit de prețul curent</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Preț ofertă</label>
                  <input type="number" step="0.01" class="form-control" name="offer_price" value="{{ old('offer_price') }}">
                  <div class="hint">Prețul redus (dacă există)</div>
                </div>
                <div class="col-12">
                  <label class="form-label">Data expirării ofertei</label>
                  <input type="date" class="form-control" name="offer_expires_at" min="{{ date('Y-m-d') }}" value="{{ old('offer_expires_at') }}">
                  <div class="hint">Lasă gol pentru ofertă permanentă</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Tip ofertă</label>
                  <select class="form-control" name="offer_type">
                    <option value="">Fără ofertă</option>
                    <option value="flash_sale" {{ old('offer_type') == 'flash_sale' ? 'selected' : '' }}>Flash Sale</option>
                    <option value="seasonal" {{ old('offer_type') == 'seasonal' ? 'selected' : '' }}>Ofertă sezonieră</option>
                    <option value="clearance" {{ old('offer_type') == 'clearance' ? 'selected' : '' }}>Lichidare stoc</option>
                    <option value="negotiable" {{ old('offer_type') == 'negotiable' ? 'selected' : '' }}>Preț negociabil</option>
                    <option value="promotion" {{ old('offer_type') == 'promotion' ? 'selected' : '' }}>Promoție</option>
                  </select>
                  <div class="hint">Tipul ofertei pentru marketing</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Descriere ofertă</label>
                  <textarea class="form-control" name="offer_description" rows="2" placeholder="Descrie oferta (opțional)">{{ old('offer_description') }}</textarea>
                  <div class="hint">Ex: "Reducere 15% pentru achiziții în această lună"</div>
                </div>
              </div>
            </div>
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
                  <option value="Benzină" {{ old('fuel') == 'Benzină' ? 'selected' : '' }}>Benzină</option>
                  <option value="Diesel" {{ old('fuel') == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                  <option value="Hibrid" {{ old('fuel') == 'Hibrid' ? 'selected' : '' }}>Hibrid</option>
                  <option value="Electric" {{ old('fuel') == 'Electric' ? 'selected' : '' }}>Electric</option>
                  <option value="GPL" {{ old('fuel') == 'GPL' ? 'selected' : '' }}>GPL</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Transmisie</label>
                <select class="form-control" name="transmission">
                  <option value="">Selectează</option>
                  <option value="Manuală" {{ old('transmission') == 'Manuală' ? 'selected' : '' }}>Manuală</option>
                  <option value="Automată" {{ old('transmission') == 'Automată' ? 'selected' : '' }}>Automată</option>
                  <option value="CVT" {{ old('transmission') == 'CVT' ? 'selected' : '' }}>CVT</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Motor</label>
                <input class="form-control" name="engine" placeholder="3.0L" value="{{ old('engine') }}">
              </div>
              <div class="col-md-4">
                <label class="form-label">Putere</label>
                <input class="form-control" name="power" placeholder="286 CP" value="{{ old('power') }}">
              </div>
              <div class="col-md-4">
                <label class="form-label">Tracțiune</label>
                <select class="form-control" name="drivetrain">
                  <option value="">Selectează</option>
                  <option value="FWD" {{ old('drivetrain') == 'FWD' ? 'selected' : '' }}>FWD (Față)</option>
                  <option value="RWD" {{ old('drivetrain') == 'RWD' ? 'selected' : '' }}>RWD (Spate)</option>
                  <option value="AWD" {{ old('drivetrain') == 'AWD' ? 'selected' : '' }}>AWD (Integrală)</option>
                  <option value="4WD" {{ old('drivetrain') == '4WD' ? 'selected' : '' }}>4WD (4x4)</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">VIN</label>
                <input class="form-control" name="vin" value="{{ old('vin') }}">
              </div>
              <div class="col-12">
                <label class="form-label">Stare</label>
                <select class="form-control" name="condition">
                  <option value="">Selectează</option>
                  <option value="Nouă" {{ old('condition') == 'Nouă' ? 'selected' : '' }}>Nouă</option>
                  <option value="Excelentă" {{ old('condition') == 'Excelentă' ? 'selected' : '' }}>Excelentă</option>
                  <option value="Foarte bună" {{ old('condition') == 'Foarte bună' ? 'selected' : '' }}>Foarte bună</option>
                  <option value="Bună" {{ old('condition') == 'Bună' ? 'selected' : '' }}>Bună</option>
                  <option value="Acceptabilă" {{ old('condition') == 'Acceptabilă' ? 'selected' : '' }}>Acceptabilă</option>
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
                <textarea class="form-control" name="description" rows="4" placeholder="Detalii cheie și beneficii">{{ old('description') }}</textarea>
              </div>
              <div class="col-12">
                <label class="form-label">Dotări (separate prin virgulă)</label>
                <input class="form-control" name="features" placeholder="LED Matrix, Pachet M, Panoramic, Senzori parcare" value="{{ old('features') }}">
              </div>
              <div class="col-md-6">
                <label class="form-label">Imagine principală (cover)</label>
                <input type="file" name="cover_image" accept="image/*" class="form-control" id="cover_image" onchange="previewImage(this, 'cover_preview')">
                <div class="hint">PNG/JPG orice dimensiune</div>
                <div id="cover_preview" class="mt-2" style="display: none;">
                  <img style="max-width: 150px; max-height: 100px; border-radius: 8px; border: 2px solid #e5e7eb;">
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Galerie imagini</label>
                <input type="file" name="gallery_images[]" accept="image/*" class="form-control" multiple id="gallery_images" onchange="previewGallery(this, 'gallery_preview')">
                <div class="hint">Poți încărca mai multe imagini</div>
                <div id="gallery_preview" class="mt-2 d-flex flex-wrap gap-2"></div>
              </div>
              <div class="col-12">
                <label class="form-label">Link video (YouTube/Vimeo)</label>
                <input type="url" class="form-control" name="video_url" placeholder="https://youtu.be/..." value="{{ old('video_url') }}">
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
                <input class="form-control" name="meta_title" maxlength="60" value="{{ old('meta_title') }}">
                <div class="hint">Maxim 60 caractere</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Locație</label>
                <input class="form-control" name="location" placeholder="București, Sector 1" value="{{ old('location') }}">
              </div>
              <div class="col-12">
                <label class="form-label">Descriere META (SEO)</label>
                <textarea class="form-control" name="meta_description" rows="2" maxlength="160">{{ old('meta_description') }}</textarea>
                <div class="hint">Maxim 160 caractere</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Badge-uri (separate prin virgulă)</label>
                <input class="form-control" name="badges" placeholder="Nou, Garanție, Finanțare" value="{{ old('badges') }}">
              </div>
              <div class="col-md-6">
                <label class="form-label">Tag-uri (separate prin virgulă)</label>
                <input class="form-control" name="tags" placeholder="sport, family, luxury" value="{{ old('tags') }}">
              </div>
              <div class="col-12">
                <label class="form-label">Note interne</label>
                <textarea class="form-control" name="internal_notes" rows="2" placeholder="Note private pentru echipă">{{ old('internal_notes') }}</textarea>
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
              <button type="submit" class="btn btn-primary btn-lg">Salvează vehicul</button>
              <button type="reset" class="btn btn-outline-secondary">Resetează formular</button>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Status vehicul</label>
              <select class="form-control" name="status">
                <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Disponibil</option>
                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="reserved" {{ old('status') == 'reserved' ? 'selected' : '' }}>Rezervat</option>
                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>În service</option>
                <option value="clearance" {{ old('status') == 'clearance' ? 'selected' : '' }}>Reducere (Clearance)</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Prioritate afișare</label>
              <input type="number" class="form-control" name="priority" min="0" max="999" value="{{ old('priority', 0) }}">
              <div class="hint">0 = normal, mai mare = mai important</div>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="featured" id="featured" value="1" {{ old('featured') ? 'checked' : '' }}>
              <label class="form-check-label" for="featured">
                <strong>Vehicul recomandat</strong>
              </label>
              <div class="hint">Va apărea în secțiunea specială</div>
            </div>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="card admin-card shadow-sm">
          <div class="card-body">
            <div class="form-section-title">Tips pentru vânzări</div>
            <div class="small text-muted">
              <p><strong>Imagini:</strong> Adaugă 5-10 poze de calitate (orice dimensiune)</p>
              <p><strong>Descriere:</strong> Menționează istoric, service-uri</p>
              <p><strong>Prețuri:</strong> Verifică piața înainte</p>
              <p><strong>SEO:</strong> Folosește cuvinte cheie relevante</p>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>

@push('scripts')
<script>
// TEMPORARY: Disable double-submit prevention for debugging
console.log('Upload debugging mode - double submit prevention disabled');
/*
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
      
      // Re-enable after 5 seconds (in case of validation errors)
      setTimeout(() => {
        isSubmitting = false;
      }, 5000);
      
      // Disable submit button
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Se salvează...';
      }
    });
  }
});
*/

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
</script>
@endpush

@endsection