@extends('layouts.admin')

@section('title', 'Admin • Gestión de Vehículos')

@push('styles')
<style>
  .admin-card { border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
  .status-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
  .status-available { background: #dcfce7; color: #166534; }
  .status-reserved { background: #fef3c7; color: #92400e; }
  .status-sold { background: #fee2e2; color: #991b1b; }
  .status-maintenance { background: #ddd6fe; color: #5b21b6; }
  .status-draft { background: #f3f4f6; color: #374151; }
  .featured-star { color: #f59e0b; cursor: pointer; font-size: 1.2rem; }
  .featured-star:hover { color: #d97706; }
  .vehicle-thumb { width: 80px; height: 60px; object-fit: cover; border-radius: 8px; transition: transform 0.2s; }
  .vehicle-thumb:hover { transform: scale(1.05); }
  .vehicle-thumb-placeholder { width: 80px; height: 60px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: 2px dashed #e2e8f0; }
  .price-display { font-weight: 600; font-size: 1.1rem; }
  .price-offer { color: #dc2626; }
  .price-original { text-decoration: line-through; color: #6b7280; font-size: 0.9rem; }
  .stats-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
  .stats-number { font-size: 2.5rem; font-weight: 700; }
  .quick-actions { background: #f8fafc; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; }
  .filter-section { background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
  .table-container { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
</style>
@endpush

@section('content')
<section class="py-4">
  <div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h3 mb-1 fw-bold">Gestión de Vehículos</h1>
        <p class="text-muted mb-0">Administra tu inventario de vehículos de forma eficiente</p>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary">
          <i class="bi bi-plus-circle me-2"></i>Añadir Vehículo
        </a>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
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
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card stats-card text-center">
          <div class="card-body">
            <div class="stats-number">{{ collect($items)->count() }}</div>
            <div class="fw-medium">Total Vehículos</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card text-center">
          <div class="card-body">
            <div class="stats-number">{{ collect($items)->where('status', 'available')->count() }}</div>
            <div class="fw-medium">Disponibles</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card text-center">
          <div class="card-body">
            <div class="stats-number">{{ collect($items)->where('featured', true)->count() }}</div>
            <div class="fw-medium">Destacados</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card text-center">
          <div class="card-body">
            <div class="stats-number">{{ collect($items)->where('status', 'sold')->count() }}</div>
            <div class="fw-medium">Vendidos</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold">Acciones Rápidas</h5>
        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-primary" onclick="exportData()">
            <i class="bi bi-download me-1"></i>Exportar
          </button>
          <button class="btn btn-sm btn-outline-info" onclick="showAnalytics()">
            <i class="bi bi-graph-up me-1"></i>Análisis
          </button>
        </div>
      </div>
      <div class="row g-2">
        <div class="col-auto">
          <button class="btn btn-sm btn-success" onclick="bulkAction('available')">
            <i class="bi bi-check-circle me-1"></i>Marcar Disponibles
          </button>
        </div>
        <div class="col-auto">
          <button class="btn btn-sm btn-warning" onclick="bulkAction('feature')">
            <i class="bi bi-star me-1"></i>Destacar Seleccionados
          </button>
        </div>
        <div class="col-auto">
          <button class="btn btn-sm btn-secondary" onclick="bulkAction('draft')">
            <i class="bi bi-file-earmark me-1"></i>Marcar como Borrador
          </button>
        </div>
      </div>
    </div>

    <!-- Filters Section -->
    <div class="filter-section">
      <h5 class="mb-3 fw-bold">Filtros y Búsqueda</h5>
      <form method="GET" class="row g-3">
        <div class="col-md-4">
          <label class="form-label fw-medium">Buscar vehículos</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control" placeholder="Marca, modelo, VIN..." value="{{ $q }}">
          </div>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-medium">Estado</label>
          <select name="status" class="form-select">
            <option value="">Todos</option>
            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Disponibles</option>
            <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reservados</option>
            <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Vendidos</option>
              <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>En Servicio</option>
              <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
              <option value="clearance" {{ request('status') == 'clearance' ? 'selected' : '' }}>En Liquidación</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-medium">Tipo</label>
          <select name="featured" class="form-select">
            <option value="">Todos</option>
            <option value="1" {{ request('featured') == '1' ? 'selected' : '' }}>Destacados</option>
            <option value="0" {{ request('featured') == '0' ? 'selected' : '' }}>Normales</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-medium">Ordenar por</label>
          <select name="sort" class="form-select">
            <option value="">Más recientes</option>
            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Precio: Menor a Mayor</option>
            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Precio: Mayor a Menor</option>
            <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>Más Vistos</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-medium">Rango de Precio</label>
          <select name="price_range" class="form-select">
            <option value="">Todos los precios</option>
            <option value="0-10000" {{ request('price_range') == '0-10000' ? 'selected' : '' }}>€0 - €10,000</option>
            <option value="10000-25000" {{ request('price_range') == '10000-25000' ? 'selected' : '' }}>€10,000 - €25,000</option>
            <option value="25000-50000" {{ request('price_range') == '25000-50000' ? 'selected' : '' }}>€25,000 - €50,000</option>
            <option value="50000-100000" {{ request('price_range') == '50000-100000' ? 'selected' : '' }}>€50,000 - €100,000</option>
            <option value="100000+" {{ request('price_range') == '100000+' ? 'selected' : '' }}>€100,000+</option>
          </select>
        </div>
        <div class="col-12">
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-search me-2"></i>Buscar
            </button>
            @if($q || request('status') || request('featured') || request('sort') || request('price_range'))
              <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-clockwise me-2"></i>Limpiar Filtros
              </a>
            @endif
          </div>
        </div>
      </form>
    </div>

    <!-- Vehicles Table -->
    <div class="table-container">
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
                      @if(!empty($v['original_price']))
                        <div class="price-original">€{{ number_format($extractPrice($v['original_price']), 0, ',', '.') }}</div>
                      @endif
                    @else
                      €{{ number_format($extractPrice($v['price']), 0, ',', '.') }}
                    @endif
                  </div>
                </td>
                <td>
                  <span class="status-badge status-{{ $v['status'] ?? 'available' }}">
                    @switch($v['status'] ?? 'available')
                      @case('available') Disponible @break
                      @case('reserved') Reservado @break
                      @case('sold') Vendido @break
                        @case('maintenance') En Servicio @break
                        @case('clearance') En Liquidación @break
                      @case('draft') Borrador @break
                      @default Desconocido
                    @endswitch
                  </span>
                </td>
                <td>
                  <span class="fw-bold">{{ $v['views_count'] ?? 0 }}</span>
                  @if(($v['inquiries_count'] ?? 0) > 0)
                    <br><small class="text-success">{{ $v['inquiries_count'] }} consultas</small>
                  @endif
                </td>
                <td class="text-center">
                  <i class="bi bi-star{{ ($v['featured'] ?? false) ? '-fill featured-star' : '' }} featured-star" 
                     onclick="toggleFeatured('{{ $v['slug'] }}')" 
                     title="Toggle destacado"></i>
                </td>
                <td>
                  <div class="small">{{ isset($v['created_at']) ? date('d.m.Y', strtotime($v['created_at'])) : 'N/A' }}</div>
                  @if(isset($v['sold_date']))
                    <div class="small text-success">Vendido: {{ date('d.m.Y', strtotime($v['sold_date'])) }}</div>
                  @endif
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('admin.vehicles.show', $v['slug']) }}" class="btn btn-outline-primary" title="Ver detalles">
                      <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('admin.vehicles.edit', $v['slug']) }}" class="btn btn-outline-warning" title="Editar">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <button class="btn btn-outline-danger" onclick="confirmDelete('{{ $v['slug'] }}', '{{ $v['title'] ?? ($v['brand'] . ' ' . $v['model']) }}')" title="Eliminar">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                  <div class="mt-1">
                    <a href="{{ route('vehicle.show', $v['slug']) }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="Ver en sitio">
                      <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center py-5">
                  <div class="text-muted">
                    <i class="bi bi-car-front" style="font-size: 4rem; opacity: 0.3;"></i>
                    <div class="mt-3 h5">No se encontraron vehículos</div>
                    @if($q)
                      <div class="small">para la búsqueda "{{ $q }}"</div>
                    @endif
                    <div class="mt-3">
                      <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Añadir Primer Vehículo
                      </a>
                    </div>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    @if(count($items) > 0)
      <div class="mt-3 text-muted text-center">
        <i class="bi bi-info-circle me-2"></i>Mostrando {{ count($items) }} vehículos
      </div>
    @endif
  </div>
</section>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmar Eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>¿Estás seguro de que quieres eliminar el vehículo <strong id="delete-vehicle-name"></strong>?</p>
        <p class="text-danger small">¡Esta acción no se puede deshacer!</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <form id="delete-form" method="POST" style="display: inline;">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Eliminar Vehículo</button>
        </form>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function toggleSelectAll() {
  const selectAll = document.getElementById('select-all');
  const checkboxes = document.querySelectorAll('.vehicle-checkbox');
  checkboxes.forEach(cb => cb.checked = selectAll.checked);
  updateBulkActions();
}

function updateBulkActions() {
  const checked = document.querySelectorAll('.vehicle-checkbox:checked');
  // Show/hide bulk action buttons based on selection
  const bulkButtons = document.querySelectorAll('.quick-actions button[onclick*="bulkAction"]');
  bulkButtons.forEach(btn => {
    btn.style.opacity = checked.length > 0 ? '1' : '0.5';
    btn.disabled = checked.length === 0;
  });
}

function confirmDelete(slug, name) {
  document.getElementById('delete-vehicle-name').textContent = name;
  document.getElementById('delete-form').action = `/admin/vehicles/${slug}`;
  new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function toggleFeatured(slug) {
  fetch(`/admin/vehicles/${slug}/toggle-featured`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      'Content-Type': 'application/json'
    }
  }).then(response => {
    if (response.ok) {
      location.reload();
    }
  });
}

function bulkAction(action) {
  const selected = Array.from(document.querySelectorAll('.vehicle-checkbox:checked')).map(cb => cb.value);
  if (selected.length === 0) {
    alert('Por favor selecciona al menos un vehículo');
    return;
  }
  
  if (action === 'delete' && !confirm(`¿Eliminar ${selected.length} vehículos?`)) return;
  
  fetch('/admin/vehicles/bulk-action', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ action, slugs: selected })
  }).then(response => {
    if (response.ok) {
      location.reload();
    }
  });
}

function handleImageError(img) {
  console.log('Image failed to load:', img.src);
  img.style.display = 'none';
  const placeholder = img.parentElement.querySelector('.vehicle-thumb-placeholder');
  if (placeholder) {
    placeholder.style.display = 'flex';
  }
}

function exportData() {
  // Implement export functionality
  alert('Función de exportación en desarrollo');
}

function showAnalytics() {
  // Implement analytics functionality
  alert('Función de análisis en desarrollo');
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  const images = document.querySelectorAll('.vehicle-thumb');
  images.forEach(img => {
    img.addEventListener('error', function() {
      handleImageError(this);
    });
  });
  
  updateBulkActions();
});
</script>
@endpush
@endsection