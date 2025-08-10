@extends('layouts.app')
@section('title','Catalog - APTECH Auto')
@push('styles')
  <link rel="stylesheet" href="{{ asset('css/pages/catalog.css') }}">
@endpush

@php
  $extractPrice = function($priceStr) {
      if (is_numeric($priceStr)) return (float) $priceStr;
      preg_match_all('/\d+/', $priceStr ?? '0', $matches);
      return $matches[0] ? (float) implode('', $matches[0]) : 0;
  };
@endphp

@section('content')
<section class="py-5 bg-light catalog-page">
  <!-- Hero Banner -->
  <div class="catalog-hero text-light mb-4" data-anim="reveal">
    <div class="container py-5">
      <h1 class="display-6 fw-bold mb-2">Catalog Vehicule</h1>
      <p class="lead mb-0 text-light-emphasis">Descoperă selecția noastră premium – verificată tehnic, istoric clar și garanție.</p>
    </div>
  </div>
  <div class="container">
    <!-- Mobile toolbar: Filters toggle + quick search -->
    <div class="d-lg-none mb-3 catalog-mobile-bar d-flex justify-content-between align-items-center">
      <button class="btn btn-outline-primary d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#filtersOffcanvas" aria-controls="filtersOffcanvas">
        <i class="bi bi-sliders"></i>
        Filtre
      </button>
      <form class="d-flex" method="get" role="search" aria-label="Căutare rapidă mobil">
        <input type="text" class="form-control form-control-sm me-2" name="q" placeholder="Caută..." value="{{ request('q') }}" />
        <button class="btn btn-sm btn-primary">Caută</button>
      </form>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 mb-0">Catalog Vehicule</h1>
      <form class="d-none d-lg-flex" method="get" role="search" aria-label="Căutare rapidă">
        <input type="text" class="form-control me-2" name="q" placeholder="Caută marcă, model..." value="{{ request('q') }}" />
        <button class="btn btn-outline-primary">Caută</button>
      </form>
    </div>

    <div class="row g-4">
      <!-- Sidebar Filtre -->
      <aside class="col-lg-3 catalog-sidebar d-none d-lg-block">
        <div class="card shadow-sm catalog-filters sticky-lg-top" data-anim="reveal">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="card-title mb-0">Filtre Avansate</h5>
              <button type="button" class="btn btn-sm btn-outline-secondary js-clear-filters" title="Șterge toate filtrele">Șterge</button>
            </div>
            <form class="filters-form" method="get">
              <div class="mb-3">
                <label class="form-label">Marcă</label>
                <select class="form-select" name="brand">
                  <option value="">Alege...</option>
                  @foreach($brands as $brand)
                    <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>{{ $brand }}</option>
                  @endforeach
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Model</label>
                <input type="text" class="form-control" name="model" placeholder="Ex: X5" value="{{ request('model') }}" />
              </div>
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label class="form-label">Preț min (€)</label>
                  <input type="number" class="form-control" name="price_min" min="0" step="500" value="{{ request('price_min') }}" />
                </div>
                <div class="col-6">
                  <label class="form-label">Preț max (€)</label>
                  <input type="number" class="form-control" name="price_max" min="0" step="500" value="{{ request('price_max') }}" />
                </div>
              </div>
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label class="form-label">An de la</label>
                  <input type="number" class="form-control" name="year_min" min="1990" max="{{ date('Y') }}" value="{{ request('year_min') }}" />
                </div>
                <div class="col-6">
                  <label class="form-label">An până la</label>
                  <input type="number" class="form-control" name="year_max" min="1990" max="{{ date('Y') }}" value="{{ request('year_max') }}" />
                </div>
              </div>
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label class="form-label">Km min</label>
                  <input type="number" class="form-control" name="mileage_min" min="0" step="1000" value="{{ request('mileage_min') }}" />
                </div>
                <div class="col-6">
                  <label class="form-label">Km max</label>
                  <input type="number" class="form-control" name="mileage_max" min="0" step="1000" value="{{ request('mileage_max') }}" />
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Combustibil</label>
                <select class="form-select" name="fuel">
                  <option value="">Toate</option>
                  @foreach($fuelTypes as $fuel)
                    <option value="{{ $fuel }}" {{ request('fuel') == $fuel ? 'selected' : '' }}>{{ $fuel }}</option>
                  @endforeach
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Transmisie</label>
                <select class="form-select" name="transmission">
                  <option value="">Toate</option>
                  @foreach($transmissions as $transmission)
                    <option value="{{ $transmission }}" {{ request('transmission') == $transmission ? 'selected' : '' }}>{{ $transmission }}</option>
                  @endforeach
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Caroserie</label>
                <select class="form-select" name="body_type">
                  <option value="">Toate</option>
                  <option value="SUV" {{ request('body_type') == 'SUV' ? 'selected' : '' }}>SUV</option>
                  <option value="Sedan" {{ request('body_type') == 'Sedan' ? 'selected' : '' }}>Sedan</option>
                  <option value="Hatchback" {{ request('body_type') == 'Hatchback' ? 'selected' : '' }}>Hatchback</option>
                  <option value="Break" {{ request('body_type') == 'Break' ? 'selected' : '' }}>Break</option>
                  <option value="Coupe" {{ request('body_type') == 'Coupe' ? 'selected' : '' }}>Coupe</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Culoare</label>
                <select class="form-select" name="color">
                  <option value="">Toate</option>
                  @foreach($colors as $color)
                    <option value="{{ $color }}" {{ request('color') == $color ? 'selected' : '' }}>{{ $color }}</option>
                  @endforeach
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Cuvinte cheie</label>
                <input type="text" class="form-control" name="keywords" placeholder="Ex: pachet M, panoramic" value="{{ request('keywords') }}" />
              </div>
              <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Aplică filtre</button>
                <button type="button" class="btn btn-outline-secondary js-clear-filters">Resetează</button>
              </div>
            </form>
          </div>
        </div>
      </aside>

      <!-- Offcanvas Filters (mobile) -->
      <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filtre</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Închide"></button>
        </div>
        <div class="offcanvas-body">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Filtre Avansate</h6>
                <button type="button" class="btn btn-sm btn-outline-secondary js-clear-filters" title="Șterge toate filtrele">Șterge</button>
              </div>
              <form class="filters-form" method="get">
                <div class="mb-3">
                  <label class="form-label">Marcă</label>
                  <select class="form-select" name="brand">
                    <option value="">Alege...</option>
                    @foreach($brands as $brand)
                      <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>{{ $brand }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Model</label>
                  <input type="text" class="form-control" name="model" placeholder="Ex: X5" value="{{ request('model') }}" />
                </div>
                <div class="row g-2 mb-3">
                  <div class="col-6">
                    <label class="form-label">Preț min (€)</label>
                    <input type="number" class="form-control" name="price_min" min="0" step="500" value="{{ request('price_min') }}" />
                  </div>
                  <div class="col-6">
                    <label class="form-label">Preț max (€)</label>
                    <input type="number" class="form-control" name="price_max" min="0" step="500" value="{{ request('price_max') }}" />
                  </div>
                </div>
                <div class="row g-2 mb-3">
                  <div class="col-6">
                    <label class="form-label">An de la</label>
                    <input type="number" class="form-control" name="year_min" min="1990" max="{{ date('Y') }}" value="{{ request('year_min') }}" />
                  </div>
                  <div class="col-6">
                    <label class="form-label">An până la</label>
                    <input type="number" class="form-control" name="year_max" min="1990" max="{{ date('Y') }}" value="{{ request('year_max') }}" />
                  </div>
                </div>
                <div class="row g-2 mb-3">
                  <div class="col-6">
                    <label class="form-label">Km min</label>
                    <input type="number" class="form-control" name="mileage_min" min="0" step="1000" value="{{ request('mileage_min') }}" />
                  </div>
                  <div class="col-6">
                    <label class="form-label">Km max</label>
                    <input type="number" class="form-control" name="mileage_max" min="0" step="1000" value="{{ request('mileage_max') }}" />
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Combustibil</label>
                  <select class="form-select" name="fuel">
                    <option value="">Toate</option>
                    @foreach($fuelTypes as $fuel)
                      <option value="{{ $fuel }}" {{ request('fuel') == $fuel ? 'selected' : '' }}>{{ $fuel }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Transmisie</label>
                  <select class="form-select" name="transmission">
                    <option value="">Toate</option>
                    @foreach($transmissions as $transmission)
                      <option value="{{ $transmission }}" {{ request('transmission') == $transmission ? 'selected' : '' }}>{{ $transmission }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Caroserie</label>
                  <select class="form-select" name="body_type">
                    <option value="">Toate</option>
                    <option value="SUV" {{ request('body_type') == 'SUV' ? 'selected' : '' }}>SUV</option>
                    <option value="Sedan" {{ request('body_type') == 'Sedan' ? 'selected' : '' }}>Sedan</option>
                    <option value="Hatchback" {{ request('body_type') == 'Hatchback' ? 'selected' : '' }}>Hatchback</option>
                    <option value="Break" {{ request('body_type') == 'Break' ? 'selected' : '' }}>Break</option>
                    <option value="Coupe" {{ request('body_type') == 'Coupe' ? 'selected' : '' }}>Coupe</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Culoare</label>
                  <select class="form-select" name="color">
                    <option value="">Toate</option>
                    @foreach($colors as $color)
                      <option value="{{ $color }}" {{ request('color') == $color ? 'selected' : '' }}>{{ $color }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Cuvinte cheie</label>
                  <input type="text" class="form-control" name="keywords" placeholder="Ex: pachet M, panoramic" value="{{ request('keywords') }}" />
                </div>
                <div class="d-grid gap-2 mt-3">
                  <button type="submit" class="btn btn-primary" data-bs-dismiss="offcanvas">Aplică filtre</button>
                  <button type="button" class="btn btn-outline-secondary js-clear-filters">Resetează</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <!-- Grid Vehicule -->
      <div class="col-lg-9">
        @if($vehicles->count() > 0)
          <!-- Results info and sorting -->
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="text-muted small">
              Găsite {{ $vehicles->total() }} vehicule
              @if(request()->hasAny(['q', 'brand', 'model', 'price_min', 'price_max', 'year_min', 'year_max', 'mileage_min', 'mileage_max', 'fuel', 'transmission', 'body_type', 'color', 'keywords']))
                <span class="badge bg-primary ms-1">Filtrate</span>
              @endif
            </div>
            <div class="d-flex align-items-center gap-2">
              <label for="sort" class="form-label mb-0 small">Sortare:</label>
              <select name="sort" id="sort" class="form-select form-select-sm" style="width: auto;" onchange="updateSort(this.value)">
                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Cele mai noi</option>
                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Preț crescător</option>
                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Preț descrescător</option>
                <option value="year_desc" {{ request('sort') == 'year_desc' ? 'selected' : '' }}>An descrescător</option>
                <option value="year_asc" {{ request('sort') == 'year_asc' ? 'selected' : '' }}>An crescător</option>
                <option value="mileage_asc" {{ request('sort') == 'mileage_asc' ? 'selected' : '' }}>Km crescător</option>
                <option value="featured" {{ request('sort') == 'featured' ? 'selected' : '' }}>Recomandate</option>
              </select>
            </div>
          </div>

          <div class="row g-4">
            @foreach($vehicles as $vehicle)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
              <div class="card h-100 shadow-sm vehicle-card">
                @if(!empty($vehicle->cover_image))
                  <img 
                    src="{{ $vehicle->cover_image }}" 
                    class="card-img-top" 
                    alt="{{ $vehicle->title ?? ($vehicle->brand . ' ' . $vehicle->model . ' ' . $vehicle->year) }}" 
                    loading="lazy" 
                    style="height: 200px; object-fit: cover;"
                    onerror="this.src='https://via.placeholder.com/480x320/f8f9fa/6c757d?text=Fără+imagine'"
                  />
                @else
                  <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                    <i class="bi bi-car-front text-muted" style="font-size: 3rem;"></i>
                  </div>
                @endif
                
                <div class="card-body d-flex flex-column">
                  @if($vehicle->featured)
                    <div class="mb-1">
                      <span class="badge bg-warning text-dark">
                        <i class="bi bi-star-fill"></i> Recomandat
                      </span>
                    </div>
                  @endif
                  
                  <h6 class="card-title mb-1">
                    {{ $vehicle->title ?? ($vehicle->brand . ' ' . $vehicle->model . ' ' . $vehicle->year) }}
                  </h6>
                  
                  <div class="small text-secondary mb-2">
                    {{ $vehicle->fuel }} • {{ number_format($extractPrice($vehicle->mileage)) }} km • {{ $vehicle->transmission }}
                  </div>
                  
                  @if(!empty($vehicle->badges))
                    <div class="mb-2">
                      @foreach($vehicle->badges as $badge)
                        <span class="badge bg-secondary me-1 small">{{ $badge }}</span>
                      @endforeach
                    </div>
                  @endif
                  
                  <div class="mt-auto d-flex justify-content-between align-items-center">
                    <div>
                      @if($vehicle->has_offer && $vehicle->offer_price)
                        <div class="small text-decoration-line-through text-muted">€ {{ number_format($extractPrice($vehicle->price)) }}</div>
                        <span class="fw-bold text-danger">€ {{ number_format($extractPrice($vehicle->offer_price)) }}</span>
                      @else
                        <span class="fw-bold text-primary">€ {{ number_format($extractPrice($vehicle->price)) }}</span>
                      @endif
                    </div>
                    <a href="{{ url('/vehicles/' . $vehicle->slug) }}" class="btn btn-sm btn-primary">Detalii</a>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        @else
          <!-- No results -->
          <div class="text-center py-5">
            <i class="bi bi-search text-muted mb-3" style="font-size: 4rem;"></i>
            <h4 class="text-muted">Nu am găsit vehicule</h4>
            <p class="text-muted mb-4">
              @if(request()->hasAny(['q', 'brand', 'model', 'price_min', 'price_max', 'year_min', 'year_max', 'mileage_min', 'mileage_max', 'fuel', 'transmission', 'body_type', 'color', 'keywords']))
                Încearcă să modifici filtrele pentru a obține mai multe rezultate.
              @else
                În curând vom adăuga vehicule noi în catalog.
              @endif
            </p>
            @if(request()->hasAny(['q', 'brand', 'model', 'price_min', 'price_max', 'year_min', 'year_max', 'mileage_min', 'mileage_max', 'fuel', 'transmission', 'body_type', 'color', 'keywords']))
              <a href="{{ route('catalog') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-clockwise me-2"></i>Resetează filtrele
              </a>
            @endif
          </div>
        @endif
       </div>
       <!-- Pagination -->
       @if($vehicles->hasPages())
         <div class="mt-5">
           {{ $vehicles->appends(request()->query())->links('custom-pagination') }}
         </div>
       @endif
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
// Clear filters and clean URL (no-op safe)
(function(){
  const clearButtons = document.querySelectorAll('.js-clear-filters');
  function clearUrlParams(){
    if (!window.history.replaceState) return;
    const url = new URL(window.location.href);
    url.search = '';
    window.history.replaceState({}, document.title, url.toString());
    location.reload();
  }
  clearButtons.forEach(btn => {
    btn.addEventListener('click', function(){
      const form = btn.closest('.card, .offcanvas, .catalog-filters')?.querySelector('form');
      if (form) form.reset();
      clearUrlParams();
    });
  });
})();

// Update sort functionality
function updateSort(sortValue) {
  const url = new URL(window.location.href);
  if (sortValue && sortValue !== 'newest') {
    url.searchParams.set('sort', sortValue);
  } else {
    url.searchParams.delete('sort');
  }
  window.location.href = url.toString();
}
</script>
@endpush

