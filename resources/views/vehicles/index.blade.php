@extends('layouts.app')

@section('title', 'Catalog Auto - Auto Premium')
@section('description', 'Descoperă catalogul nostru complet de vehicule. Filtrează după marcă, preț, an și multe altele.')

@section('content')
    <!-- Catalog Banner -->
    <section class="catalog-banner">
        <div class="container">
            <div class="banner-content">
                <h1 class="banner-title">
                    La Noi Găsești Orice
                </h1>
                <p class="banner-subtitle">
                    De la mașini de oraș la SUV-uri de lux, avem exact ce cauți
                </p>
            </div>
        </div>
    </section>

    <!-- Main Catalog Section -->
    <section class="catalog-main">
        <div class="container">
            <div class="catalog-layout">
                <!-- Left Sidebar - Filters -->
                <aside class="filters-sidebar">
                    <div class="filters-header">
                        <h3 class="filters-title">Filtre</h3>
                        <button id="clear-filters" class="clear-filters">Resetează</button>
                    </div>

                    <form id="vehicle-filters" class="filters-form">
                        <!-- Brand Filter -->
                        <div class="filter-section">
                            <label class="filter-label">Marcă</label>
                            <div class="filter-options">
                                @foreach($brands as $brand)
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="brands[]" value="{{ $brand }}" 
                                               {{ in_array($brand, request('brands', [])) ? 'checked' : '' }}>
                                        <span class="checkbox-custom"></span>
                                        <span class="checkbox-text">{{ $brand }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div class="filter-section">
                            <label class="filter-label">Preț (€)</label>
                            <div class="price-inputs">
                                <input type="number" name="min_price" placeholder="Min" 
                                       value="{{ request('min_price') }}" class="price-input">
                                <span class="price-separator">-</span>
                                <input type="number" name="max_price" placeholder="Max" 
                                       value="{{ request('max_price') }}" class="price-input">
                            </div>
                        </div>

                        <!-- Year Range -->
                        <div class="filter-section">
                            <label class="filter-label">An</label>
                            <div class="year-inputs">
                                <select name="min_year" class="year-select">
                                    <option value="">De la</option>
                                    @for($year = date('Y'); $year >= 1990; $year--)
                                        <option value="{{ $year }}" {{ request('min_year') == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                                <select name="max_year" class="year-select">
                                    <option value="">Până la</option>
                                    @for($year = date('Y'); $year >= 1990; $year--)
                                        <option value="{{ $year }}" {{ request('max_year') == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Fuel Type -->
                        <div class="filter-section">
                            <label class="filter-label">Combustibil</label>
                            <div class="filter-options">
                                <label class="checkbox-item">
                                    <input type="checkbox" name="fuel_types[]" value="Benzină" 
                                           {{ in_array('Benzină', request('fuel_types', [])) ? 'checked' : '' }}>
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-text">Benzină</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="fuel_types[]" value="Diesel" 
                                           {{ in_array('Diesel', request('fuel_types', [])) ? 'checked' : '' }}>
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-text">Diesel</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="fuel_types[]" value="Hibrid" 
                                           {{ in_array('Hibrid', request('fuel_types', [])) ? 'checked' : '' }}>
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-text">Hibrid</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="fuel_types[]" value="Electric" 
                                           {{ in_array('Electric', request('fuel_types', [])) ? 'checked' : '' }}>
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-text">Electric</span>
                                </label>
                            </div>
                        </div>

                        <!-- Vehicle Type -->
                        <div class="filter-section">
                            <label class="filter-label">Tip Vehicul</label>
                            <div class="filter-options">
                                <label class="checkbox-item">
                                    <input type="checkbox" name="types[]" value="SUV" 
                                           {{ in_array('SUV', request('types', [])) ? 'checked' : '' }}>
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-text">SUV</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="types[]" value="Sedan" 
                                           {{ in_array('Sedan', request('types', [])) ? 'checked' : '' }}>
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-text">Sedan</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="types[]" value="Hatchback" 
                                           {{ in_array('Hatchback', request('types', [])) ? 'checked' : '' }}>
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-text">Hatchback</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="types[]" value="Sport" 
                                           {{ in_array('Sport', request('types', [])) ? 'checked' : '' }}>
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-text">Sport</span>
                                </label>
                            </div>
                        </div>

                        <!-- Mileage Range -->
                        <div class="filter-section">
                            <label class="filter-label">Kilometraj (km)</label>
                            <div class="mileage-inputs">
                                <input type="number" name="min_mileage" placeholder="Min" 
                                       value="{{ request('min_mileage') }}" class="mileage-input">
                                <span class="mileage-separator">-</span>
                                <input type="number" name="max_mileage" placeholder="Max" 
                                       value="{{ request('max_mileage') }}" class="mileage-input">
                            </div>
                        </div>

                        <!-- Transmission -->
                        <div class="filter-section">
                            <label class="filter-label">Transmisie</label>
                            <div class="filter-options">
                                <label class="checkbox-item">
                                    <input type="checkbox" name="transmissions[]" value="Manual" 
                                           {{ in_array('Manual', request('transmissions', [])) ? 'checked' : '' }}>
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-text">Manual</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="transmissions[]" value="Automat" 
                                           {{ in_array('Automat', request('transmissions', [])) ? 'checked' : '' }}>
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-text">Automat</span>
                                </label>
                            </div>
                        </div>

                        <!-- Apply Filters Button -->
                        <button type="submit" class="apply-filters">
                            Aplică Filtrele
                        </button>
                    </form>
                </aside>

                <!-- Main Content - Vehicles -->
                <main class="main-content">
                    <!-- Results Header -->
                    <div class="results-header">
                        <div class="results-info">
                            <h2 class="results-title">
                                {{ $vehicles->total() }} vehicule găsite
                            </h2>
                            <p class="results-subtitle">
                                Rezultate pentru criteriile tale de căutare
                            </p>
                        </div>

                        <!-- Sort Options -->
                        <div class="sort-options">
                            <label for="sort" class="sort-label">Sortează după:</label>
                            <select id="sort" name="sort" class="sort-select">
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Cele mai noi</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Preț crescător</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Preț descrescător</option>
                                <option value="year_desc" {{ request('sort') == 'year_desc' ? 'selected' : '' }}>An descrescător</option>
                                <option value="mileage_asc" {{ request('sort') == 'mileage_asc' ? 'selected' : '' }}>Kilometri crescători</option>
                            </select>
                        </div>
                    </div>

                    <!-- Vehicles Grid -->
                    <div class="cards-grid">
                        @forelse($vehicles as $vehicle)
                            @include('components.vehicle-card', ['vehicle' => $vehicle])
                        @empty
                            <div class="no-results">
                                <div class="no-results-icon">🚗</div>
                                <h3 class="no-results-title">Nu s-au găsit vehicule</h3>
                                <p class="no-results-text">
                                    Încearcă să modifici criteriile de căutare pentru a găsi mai multe rezultate.
                                </p>
                                <a href="{{ route('vehicles.index') }}" class="no-results-button">
                                    Vezi Toate Vehiculele
                                </a>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if($vehicles->hasPages())
                        <div class="pagination">
                            {{ $vehicles->links() }}
                        </div>
                    @endif
                </main>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile filters toggle
    const filtersHeader = document.querySelector('.filters-header');
    const filtersForm = document.querySelector('.filters-form');
    
    if (filtersHeader && filtersForm) {
        filtersHeader.addEventListener('click', function() {
            if (window.innerWidth <= 1023) {
                filtersForm.classList.toggle('active');
            }
        });
    }

    // Auto-submit form when filters change
    const filterForm = document.getElementById('vehicle-filters');
    const filterInputs = filterForm.querySelectorAll('input, select');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            setTimeout(() => {
                filterForm.submit();
            }, 500);
        });
    });

    // Clear filters functionality
    const clearFiltersBtn = document.getElementById('clear-filters');
    clearFiltersBtn.addEventListener('click', function() {
        window.location.href = '{{ route("vehicles.index") }}';
    });

    // Sort functionality
    const sortSelect = document.getElementById('sort');
    sortSelect.addEventListener('change', function() {
        const url = new URL(window.location);
        url.searchParams.set('sort', this.value);
        window.location = url;
    });

    // Close filters on outside click (mobile)
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 1023) {
            const filtersSidebar = document.querySelector('.filters-sidebar');
            if (filtersSidebar && !filtersSidebar.contains(event.target)) {
                filtersForm.classList.remove('active');
            }
        }
    });
});
});
</script>
@endpush 