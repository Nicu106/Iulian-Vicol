@extends('layouts.admin')

@section('title', 'Admin • Gestión de Vehículos')

@push('styles')
<style>
  /* Mobile-first responsive styles */
  .admin-card { border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
  .status-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
  .status-available { background: #dcfce7; color: #166534; }
  .status-reserved { background: #fef3c7; color: #92400e; }
  .status-sold { background: #fee2e2; color: #991b1b; }
  .status-maintenance { background: #ddd6fe; color: #5b21b6; }
  .status-draft { background: #f3f4f6; color: #374151; }
  .featured-star { color: #f59e0b; cursor: pointer; font-size: 1.2rem; }
  .featured-star:hover { color: #d97706; }
  .vehicle-thumb { width: 80px; height: 60px; object-fit: contain; border-radius: 8px; transition: transform 0.2s; background-color: #f8f9fa; }
  .vehicle-thumb:hover { transform: scale(1.05); }
  .vehicle-thumb-placeholder { width: 80px; height: 60px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: 2px dashed #e2e8f0; }
  .price-display { font-weight: 600; font-size: 1.1rem; }
  .price-offer { color: #dc2626; }
  .price-original { text-decoration: line-through; color: #6b7280; font-size: 0.9rem; }
  .stats-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
  .stats-number { font-size: 2rem; font-weight: 700; }
  .quick-actions { background: #f8fafc; border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; }
  .filter-section { background: white; border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
  .table-container { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
  
  /* Mobile vehicle cards */
  .vehicle-mobile-card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  
  .vehicle-mobile-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
  }
  
  /* Mobile responsive adjustments */
  @media (max-width: 767.98px) {
    .stats-number { font-size: 1.5rem; }
    .quick-actions { padding: 0.75rem; }
    .filter-section { padding: 0.75rem; }
    .admin-content { padding-top: 0.5rem; }
  }
  
  /* Tablet adjustments */
  @media (min-width: 768px) and (max-width: 991.98px) {
    .stats-number { font-size: 1.75rem; }
  }
</style>
@endpush

@section('content')
<section class="py-2 py-md-4">
  <div class="container-fluid px-2 px-md-3">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mb-md-4">
      <div class="mb-2 mb-md-0">
        <h1 class="h4 h-md-3 mb-1 fw-bold">Gestión de Vehículos</h1>
        <p class="text-muted mb-0 small">Administra tu inventario de vehículos de forma eficiente</p>
      </div>
      <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
        <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-circle me-2"></i>Añadir Vehículo
        </a>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-eye me-2"></i>Ver Sitio
        </a>
      </div>
    </div>

    @if(session('status'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <!-- Quick Stats -->
    <div class="row g-2 g-md-3 mb-3 mb-md-4">
      <div class="col-6 col-md-3">
        <div class="card stats-card text-center">
          <div class="card-body p-2 p-md-3">
            <div class="stats-number">{{ collect($items)->count() }}</div>
            <div class="fw-medium small">Total Vehículos</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card stats-card text-center">
          <div class="card-body p-2 p-md-3">
            <div class="stats-number">{{ collect($items)->where('status', 'available')->count() }}</div>
            <div class="fw-medium small">Disponibles</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card stats-card text-center">
          <div class="card-body p-2 p-md-3">
            <div class="stats-number">{{ collect($items)->where('featured', true)->count() }}</div>
            <div class="fw-medium small">Destacados</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card stats-card text-center">
          <div class="card-body p-2 p-md-3">
            <div class="stats-number">{{ collect($items)->where('status', 'sold')->count() }}</div>
            <div class="fw-medium small">Vendidos</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2 mb-md-3">
        <h6 class="mb-2 mb-md-0 fw-bold">Acciones Rápidas</h6>
        <div class="d-flex flex-wrap gap-2">
          <button class="btn btn-sm btn-outline-primary" onclick="exportData()">
            <i class="bi bi-download me-1"></i>Exportar
          </button>
          <button class="btn btn-sm btn-outline-info" onclick="showAnalytics()">
            <i class="bi bi-graph-up me-1"></i>Analíticas
          </button>
        </div>
      </div>
      
      <!-- Bulk Actions -->
      <div id="bulk-actions" class="d-none">
        <div class="alert alert-info d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
          <span class="mb-2 mb-md-0"><span id="selected-count">0</span> vehículos seleccionados</span>
          <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-sm btn-outline-primary" onclick="bulkAction('feature')">
              <i class="bi bi-star me-1"></i>Destacar
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="bulkAction('unfeature')">
              <i class="bi bi-star me-1"></i>Quitar destacado
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="bulkAction('available')">
              <i class="bi bi-check-circle me-1"></i>Disponible
            </button>
            <button class="btn btn-sm btn-outline-warning" onclick="bulkAction('draft')">
              <i class="bi bi-file-earmark me-1"></i>Borrador
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="bulkAction('delete')">
              <i class="bi bi-trash me-1"></i>Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filter-section">
      <form method="get" class="row g-2 g-md-3">
        <div class="col-12 col-md-3">
          <label class="form-label fw-medium small">Buscar</label>
          <input type="text" name="q" class="form-control form-control-sm" placeholder="Marca, modelo..." value="{{ $q }}">
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label fw-medium small">Estado</label>
          <select name="status" class="form-select form-select-sm">
            <option value="">Todos</option>
            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Disponible</option>
            <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reservado</option>
            <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Vendido</option>
            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label fw-medium small">Destacado</label>
          <select name="featured" class="form-select form-select-sm">
            <option value="">Todos</option>
            <option value="1" {{ request('featured') == '1' ? 'selected' : '' }}>Destacados</option>
            <option value="0" {{ request('featured') == '0' ? 'selected' : '' }}>Normales</option>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label fw-medium small">Ordenar</label>
          <select name="sort" class="form-select form-select-sm">
            <option value="">Más recientes</option>
            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Precio ↑</option>
            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Precio ↓</option>
            <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>Más Vistos</option>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label fw-medium small">Precio</label>
          <select name="price_range" class="form-select form-select-sm">
            <option value="">Todos</option>
            <option value="0-10000" {{ request('price_range') == '0-10000' ? 'selected' : '' }}>€0-10k</option>
            <option value="10000-25000" {{ request('price_range') == '10000-25000' ? 'selected' : '' }}>€10k-25k</option>
            <option value="25000-50000" {{ request('price_range') == '25000-50000' ? 'selected' : '' }}>€25k-50k</option>
            <option value="50000+" {{ request('price_range') == '50000+' ? 'selected' : '' }}>€50k+</option>
          </select>
        </div>
        <div class="col-12 col-md-1">
          <label class="form-label fw-medium small d-none d-md-block">&nbsp;</label>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-fill">
              <i class="bi bi-search me-1"></i><span class="d-none d-sm-inline">Buscar</span>
            </button>
            @if($q || request('status') || request('featured') || request('sort') || request('price_range'))
              <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-clockwise"></i>
              </a>
            @endif
          </div>
        </div>
      </form>
    </div>

    <!-- Desktop Table View -->
    <div class="table-container d-none d-lg-block">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th width="50">
                <input type="checkbox" id="select-all" onchange="toggleSelectAll()" class="form-check-input">
              </th>
              <th width="100">Imagen</th>
              <th>Vehículo</th>
              <th width="120">Precio</th>
              <th width="100">Estado</th>
              <th width="80">Vistas</th>
              <th width="80">Destacado</th>
              <th width="100">Fecha</th>
              <th width="150">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($items as $vehicle)
              @php
                $v = is_array($vehicle) ? $vehicle : $vehicle->toArray();
                $hasOffer = !empty($v['offer_price']) && (!isset($v['offer_expires_at']) || $v['offer_expires_at'] >= date('Y-m-d'));
                
                // Helper function to extract numeric price
                $extractPrice = function($priceStr) {
                  if (is_numeric($priceStr)) return (float) $priceStr;
                  preg_match_all('/\d+/', $priceStr ?? '0', $matches);
                  return $matches[0] ? (float) implode('', $matches[0]) : 0;
                };
              @endphp
              <tr>
                <td>
                  <input type="checkbox" class="vehicle-checkbox form-check-input" value="{{ $v['slug'] }}" onchange="updateBulkActions()">
                </td>
                <td>
                  @if(!empty($v['cover_image']))
                    <div class="position-relative">
                      <img src="{{ $v['cover_image'] }}" 
                           alt="{{ $v['title'] ?? ($v['brand'] . ' ' . $v['model']) }}" 
                           class="vehicle-thumb"
                           loading="lazy"
                           onerror="handleImageError(this)">
                    </div>
                  @else
                    <div class="vehicle-thumb-placeholder">
                      <i class="bi bi-car-front text-muted"></i>
                    </div>
                  @endif
                </td>
                <td>
                  <div class="fw-bold">{{ $v['title'] ?? ($v['brand'] . ' ' . $v['model'] . ' ' . $v['year']) }}</div>
                  <div class="small text-muted">
                    {{ $v['year'] }} • {{ $v['mileage'] ?? 'N/A' }} km • {{ $v['fuel'] ?? 'N/A' }}
                  </div>
                  @if(!empty($v['badges']) && is_array($v['badges']))
                    <div class="mt-1">
                      @foreach($v['badges'] as $badge)
                        <span class="badge bg-primary">{{ $badge }}</span>
                      @endforeach
                    </div>
                  @endif
                </td>
                <td>
                  <div class="price-display">
                    @if($hasOffer)
                      <div class="price-offer">€{{ number_format($extractPrice($v['offer_price']), 0, ',', '.') }}</div>
                      <div class="price-original">€{{ number_format($extractPrice($v['price']), 0, ',', '.') }}</div>
                    @else
                      €{{ number_format($extractPrice($v['price']), 0, ',', '.') }}
                    @endif
                  </div>
                </td>
                <td>
                  <span class="status-badge status-{{ $v['status'] ?? 'available' }}">
                    {{ ucfirst($v['status'] ?? 'available') }}
                  </span>
                </td>
                <td>
                  <span class="badge bg-info">{{ $v['views_count'] ?? 0 }}</span>
                </td>
                <td>
                  <i class="featured-star {{ ($v['featured'] ?? false) ? 'bi-star-fill' : 'bi-star' }}" 
                     onclick="toggleFeatured('{{ $v['slug'] }}')" 
                     title="Destacar vehículo"></i>
                </td>
                <td>
                  <small class="text-muted">
                    {{ isset($v['created_at']) ? \Carbon\Carbon::parse($v['created_at'])->format('d/m/Y') : 'N/A' }}
                  </small>
                </td>
                <td>
                  <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('admin.vehicles.show', $v['slug']) }}" class="btn btn-outline-primary btn-sm" title="Ver">
                      <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('admin.vehicles.edit', $v['slug']) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form method="POST" action="{{ route('admin.vehicles.destroy', $v['slug']) }}" class="d-inline" onsubmit="return confirm('¿Estás seguro?')">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center py-5">
                  <div class="text-muted">
                    <i class="bi bi-car-front fs-1 d-block mb-3"></i>
                    <h5>No hay vehículos</h5>
                    <p>No se encontraron vehículos con los filtros aplicados.</p>
                    <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary">
                      <i class="bi bi-plus-circle me-2"></i>Añadir Primer Vehículo
                    </a>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Mobile Card View -->
    <div class="d-lg-none">
      <!-- Mobile Bulk Actions -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
          <input type="checkbox" id="select-all-mobile" onchange="toggleSelectAllMobile()" class="form-check-input">
          <label class="form-check-label small" for="select-all-mobile">
            Seleccionar todos
          </label>
        </div>
        <div id="mobile-bulk-actions" class="d-none">
          <div class="dropdown">
            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-gear me-1"></i>Acciones
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#" onclick="bulkAction('feature')">
                <i class="bi bi-star me-2"></i>Destacar
              </a></li>
              <li><a class="dropdown-item" href="#" onclick="bulkAction('unfeature')">
                <i class="bi bi-star me-2"></i>Quitar destacado
              </a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="#" onclick="bulkAction('delete')">
                <i class="bi bi-trash me-2"></i>Eliminar
              </a></li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Mobile Vehicle Cards -->
      @forelse($items as $vehicle)
        @php
          $v = is_array($vehicle) ? $vehicle : $vehicle->toArray();
          $hasOffer = !empty($v['offer_price']) && (!isset($v['offer_expires_at']) || $v['offer_expires_at'] >= date('Y-m-d'));
          
          // Helper function to extract numeric price
          $extractPrice = function($priceStr) {
            if (is_numeric($priceStr)) return (float) $priceStr;
            preg_match_all('/\d+/', $priceStr ?? '0', $matches);
            return $matches[0] ? (float) implode('', $matches[0]) : 0;
          };
        @endphp
        <div class="card mb-3 vehicle-mobile-card">
          <div class="card-body p-3">
            <div class="row g-3">
              <!-- Checkbox and Image -->
              <div class="col-4">
                <div class="form-check mb-2">
                  <input type="checkbox" class="vehicle-checkbox-mobile form-check-input" value="{{ $v['slug'] }}" onchange="updateMobileBulkActions()">
                </div>
                @if(!empty($v['cover_image']))
                  <img src="{{ $v['cover_image'] }}" 
                       alt="{{ $v['title'] ?? ($v['brand'] . ' ' . $v['model']) }}" 
                       class="img-fluid rounded"
                       style="aspect-ratio: 4/3; object-fit: contain; background-color: #f8f9fa;"
                       loading="lazy"
                       onerror="handleImageError(this)">
                @else
                  <div class="bg-light rounded d-flex align-items-center justify-content-center" style="aspect-ratio: 4/3;">
                    <i class="bi bi-car-front text-muted fs-2"></i>
                  </div>
                @endif
              </div>
              
              <!-- Vehicle Info -->
              <div class="col-8">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h6 class="card-title mb-0 fw-bold lh-sm">
                    {{ $v['title'] ?? ($v['brand'] . ' ' . $v['model'] . ' ' . $v['year']) }}
                  </h6>
                  <i class="featured-star {{ ($v['featured'] ?? false) ? 'bi-star-fill text-warning' : 'bi-star text-muted' }}" 
                     onclick="toggleFeatured('{{ $v['slug'] }}')" 
                     title="Destacar vehículo"></i>
                </div>
                
                <div class="small text-muted mb-2">
                  {{ $v['year'] }} • {{ $v['mileage'] ?? 'N/A' }} km • {{ $v['fuel'] ?? 'N/A' }}
                </div>
                
                <!-- Price -->
                <div class="price-display mb-2">
                  @if($hasOffer)
                    <div class="price-offer fw-bold">€{{ number_format($extractPrice($v['offer_price']), 0, ',', '.') }}</div>
                    <div class="price-original small">€{{ number_format($extractPrice($v['price']), 0, ',', '.') }}</div>
                  @else
                    <div class="fw-bold">€{{ number_format($extractPrice($v['price']), 0, ',', '.') }}</div>
                  @endif
                </div>
                
                <!-- Status and Stats -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div>
                    <span class="status-badge status-{{ $v['status'] ?? 'available' }} me-2">
                      {{ ucfirst($v['status'] ?? 'available') }}
                    </span>
                    <span class="badge bg-info">{{ $v['views_count'] ?? 0 }}</span>
                  </div>
                  <small class="text-muted">
                    {{ isset($v['created_at']) ? \Carbon\Carbon::parse($v['created_at'])->format('d/m/Y') : 'N/A' }}
                  </small>
                </div>
                
                <!-- Badges -->
                @if(!empty($v['badges']) && is_array($v['badges']))
                  <div class="mb-3">
                    @foreach($v['badges'] as $badge)
                      <span class="badge bg-primary me-1">{{ $badge }}</span>
                    @endforeach
                  </div>
                @endif
                
                <!-- Actions -->
                <div class="d-flex gap-1">
                  <a href="{{ route('admin.vehicles.show', $v['slug']) }}" class="btn btn-outline-primary btn-sm flex-fill">
                    <i class="bi bi-eye me-1"></i><span class="d-none d-sm-inline">Ver</span>
                  </a>
                  <a href="{{ route('admin.vehicles.edit', $v['slug']) }}" class="btn btn-outline-warning btn-sm flex-fill">
                    <i class="bi bi-pencil me-1"></i><span class="d-none d-sm-inline">Editar</span>
                  </a>
                  <form method="POST" action="{{ route('admin.vehicles.destroy', $v['slug']) }}" class="flex-fill" onsubmit="return confirm('¿Estás seguro?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                      <i class="bi bi-trash me-1"></i><span class="d-none d-sm-inline">Eliminar</span>
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="text-center py-5">
          <div class="text-muted">
            <i class="bi bi-car-front fs-1 d-block mb-3"></i>
            <h5>No hay vehículos</h5>
            <p>No se encontraron vehículos con los filtros aplicados.</p>
            <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary">
              <i class="bi bi-plus-circle me-2"></i>Añadir Primer Vehículo
            </a>
          </div>
        </div>
      @endforelse
    </div>
  </div>
</section>

@push('scripts')
<script>
// Enhanced mobile-friendly JavaScript
function toggleSelectAll() {
  const selectAll = document.getElementById('select-all');
  const checkboxes = document.querySelectorAll('.vehicle-checkbox');
  checkboxes.forEach(cb => cb.checked = selectAll.checked);
  updateBulkActions();
}

function toggleSelectAllMobile() {
  const selectAll = document.getElementById('select-all-mobile');
  const checkboxes = document.querySelectorAll('.vehicle-checkbox-mobile');
  checkboxes.forEach(cb => cb.checked = selectAll.checked);
  updateMobileBulkActions();
}

function updateBulkActions() {
  const checkboxes = document.querySelectorAll('.vehicle-checkbox:checked');
  const bulkActions = document.getElementById('bulk-actions');
  const selectedCount = document.getElementById('selected-count');
  
  if (checkboxes.length > 0) {
    bulkActions.classList.remove('d-none');
    selectedCount.textContent = checkboxes.length;
  } else {
    bulkActions.classList.add('d-none');
  }
}

function updateMobileBulkActions() {
  const checkboxes = document.querySelectorAll('.vehicle-checkbox-mobile:checked');
  const bulkActions = document.getElementById('mobile-bulk-actions');
  
  if (checkboxes.length > 0) {
    bulkActions.classList.remove('d-none');
  } else {
    bulkActions.classList.add('d-none');
  }
}

function toggleFeatured(slug) {
  fetch(`/admin/vehicles/${slug}/toggle-featured`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Content-Type': 'application/json',
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const stars = document.querySelectorAll(`[onclick="toggleFeatured('${slug}')"]`);
      stars.forEach(star => {
        if (data.featured) {
          star.classList.remove('bi-star');
          star.classList.add('bi-star-fill', 'text-warning');
        } else {
          star.classList.remove('bi-star-fill', 'text-warning');
          star.classList.add('bi-star');
        }
      });
    }
  })
  .catch(error => console.error('Error:', error));
}

function bulkAction(action) {
  const checkboxes = document.querySelectorAll('.vehicle-checkbox:checked, .vehicle-checkbox-mobile:checked');
  const slugs = Array.from(checkboxes).map(cb => cb.value);
  
  if (slugs.length === 0) {
    alert('Selecciona al menos un vehículo');
    return;
  }
  
  if (action === 'delete' && !confirm(`¿Estás seguro de eliminar ${slugs.length} vehículo(s)?`)) {
    return;
  }
  
  fetch('/admin/vehicles/bulk-action', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ action, slugs })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      location.reload();
    } else {
      alert('Error al realizar la acción');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Error al realizar la acción');
  });
}

function handleImageError(img) {
  img.style.display = 'none';
  const placeholder = img.parentElement.querySelector('.vehicle-thumb-placeholder') || 
                     img.parentElement.parentElement.querySelector('.bg-light');
  if (placeholder) {
    placeholder.style.display = 'flex';
  }
}

function exportData() {
  window.location.href = '/admin/vehicles/export-pricing?format=csv';
}

function showAnalytics() {
  window.location.href = '/admin/vehicles/pricing-analytics';
}
</script>
@endpush
@endsection