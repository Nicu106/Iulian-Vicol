@extends('layouts.admin')

@section('title', 'Admin • Lista vehicule')

@push('styles')
<style>
  .admin-card { border-radius: 16px; }
  .status-badge { display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
  .status-available { background: #dcfce7; color: #166534; }
  .status-reserved { background: #fef3c7; color: #92400e; }
  .status-sold { background: #fee2e2; color: #991b1b; }
  .status-maintenance { background: #ddd6fe; color: #5b21b6; }
  .status-draft { background: #f3f4f6; color: #374151; }
  .featured-star { color: #f59e0b; cursor: pointer; }
  .featured-star:hover { color: #d97706; }
  .vehicle-thumb { width: 60px; height: 40px; object-fit: cover; border-radius: 8px; transition: transform 0.2s; }
  .vehicle-thumb:hover { transform: scale(1.1); }
  .vehicle-thumb-placeholder { width: 60px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: 2px dashed #e2e8f0; }
  .image-error { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
  .price-display { font-weight: 600; }
  .price-offer { color: #dc2626; }
  .price-original { text-decoration: line-through; color: #6b7280; font-size: 0.875rem; }
  .bulk-actions { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; }
  .stats-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
  .stats-number { font-size: 2rem; font-weight: 700; }
</style>
@endpush

@section('content')
<section class="py-4">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h4 mb-0">Gestionare vehicule</h1>
      <div>
        <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary">+ Adaugă vehicul</a>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary ms-2">Vezi site</a>
      </div>
    </div>

    @if(session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card stats-card text-center">
          <div class="card-body">
            <div class="stats-number">{{ collect($items)->count() }}</div>
            <div>Total vehicule</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card text-center">
          <div class="card-body">
            <div class="stats-number">{{ collect($items)->where('status', 'available')->count() }}</div>
            <div>Disponibile</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card text-center">
          <div class="card-body">
            <div class="stats-number">{{ collect($items)->where('featured', true)->count() }}</div>
            <div>Recomandate</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card text-center">
          <div class="card-body">
            <div class="stats-number">{{ collect($items)->where('status', 'sold')->count() }}</div>
            <div>Vândute</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters & Search -->
    <div class="card admin-card shadow-sm mb-4">
      <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label">Caută vehicule</label>
            <input type="text" name="q" class="form-control" placeholder="Marcă, model, VIN..." value="{{ $q }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
              <option value="">Toate</option>
              <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Disponibile</option>
              <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Rezervate</option>
              <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Vândute</option>
              <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Service</option>
              <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Tip</label>
            <select name="featured" class="form-control">
              <option value="">Toate</option>
              <option value="1" {{ request('featured') == '1' ? 'selected' : '' }}>Recomandate</option>
              <option value="0" {{ request('featured') == '0' ? 'selected' : '' }}>Normale</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Sortare</label>
            <select name="sort" class="form-control">
              <option value="">Recente</option>
              <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Preț crescător</option>
              <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Preț descrescător</option>
              <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>Vizualizări</option>
            </select>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Caută</button>
            @if($q || request('status') || request('featured') || request('sort'))
              <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary ms-1">Reset</a>
            @endif
          </div>
        </form>
      </div>
    </div>

    <!-- Bulk Actions -->
    <div class="bulk-actions mb-3" style="display: none;" id="bulk-actions">
      <div class="d-flex align-items-center gap-3">
        <span class="fw-bold">Acțiuni în masă:</span>
        <button class="btn btn-sm btn-warning" onclick="bulkAction('feature')">Marchează recomandate</button>
        <button class="btn btn-sm btn-outline-warning" onclick="bulkAction('unfeature')">Șterge recomandare</button>
        <button class="btn btn-sm btn-success" onclick="bulkAction('available')">Marchează disponibile</button>
        <button class="btn btn-sm btn-secondary" onclick="bulkAction('draft')">Marchează draft</button>
        <button class="btn btn-sm btn-danger" onclick="bulkAction('delete')">Șterge</button>
      </div>
    </div>

    <!-- Vehicles Table -->
    <div class="card admin-card shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th width="50">
                  <input type="checkbox" id="select-all" onchange="toggleSelectAll()">
                </th>
                <th width="80">Imagine</th>
                <th>Vehicul</th>
                <th width="120">Preț</th>
                <th width="100">Status</th>
                <th width="80">Views</th>
                <th width="100">Recomandat</th>
                <th width="120">Data</th>
                <th width="150">Acțiuni</th>
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
                    <input type="checkbox" class="vehicle-checkbox" value="{{ $v['slug'] }}" onchange="updateBulkActions()">
                  </td>
                  <td>
                    @if(!empty($v['cover_image']))
                      <div class="position-relative">
                        <img src="{{ $v['cover_image'] }}" 
                             alt="{{ $v['title'] ?? ($v['brand'] . ' ' . $v['model']) }}" 
                             class="vehicle-thumb"
                             loading="lazy"
                             onerror="handleImageError(this)">
                        <div class="vehicle-thumb-placeholder image-error position-absolute top-0 start-0" style="display: none;">
                          <i class="bi bi-image" title="Imagine nu se poate încărca"></i>
                        </div>
                      </div>
                    @else
                      <div class="vehicle-thumb-placeholder">
                        <i class="bi bi-car-front text-muted" title="Fără imagine"></i>
                      </div>
                    @endif
                  </td>
                  <td>
                    <div class="fw-bold">{{ $v['title'] ?? ($v['brand'] . ' ' . $v['model'] . ' ' . $v['year']) }}</div>
                    <div class="small text-muted">
                      {{ $v['year'] }} • {{ $v['mileage'] ?? 'N/A' }} • {{ $v['fuel'] ?? 'N/A' }}
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
                        @case('available') Disponibil @break
                        @case('reserved') Rezervat @break
                        @case('sold') Vândut @break
                        @case('maintenance') Service @break
                        @case('draft') Draft @break
                        @default Necunoscut
                      @endswitch
                    </span>
                  </td>
                  <td>
                    <span class="fw-bold">{{ $v['views_count'] ?? 0 }}</span>
                    @if(($v['inquiries_count'] ?? 0) > 0)
                      <br><small class="text-success">{{ $v['inquiries_count'] }} întrebări</small>
                    @endif
                  </td>
                  <td class="text-center">
                    <i class="bi bi-star{{ ($v['featured'] ?? false) ? '-fill featured-star' : '' }} featured-star" 
                       onclick="toggleFeatured('{{ $v['slug'] }}')" 
                       title="Toggle recomandat"></i>
                    @if(($v['priority'] ?? 0) > 0)
                      <br><small class="text-warning">Prioritate: {{ $v['priority'] }}</small>
                    @endif
                  </td>
                  <td>
                    <div class="small">{{ isset($v['created_at']) ? date('d.m.Y', strtotime($v['created_at'])) : 'N/A' }}</div>
                    @if(isset($v['sold_date']))
                      <div class="small text-success">Vândut: {{ date('d.m.Y', strtotime($v['sold_date'])) }}</div>
                    @endif
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="{{ route('admin.vehicles.show', $v['slug']) }}" class="btn btn-outline-primary" title="Vezi detalii">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a href="{{ route('admin.vehicles.edit', $v['slug']) }}" class="btn btn-outline-warning" title="Editează">
                        <i class="bi bi-pencil"></i>
                      </a>
                      <button class="btn btn-outline-danger" onclick="confirmDelete('{{ $v['slug'] }}', '{{ $v['title'] ?? ($v['brand'] . ' ' . $v['model']) }}')" title="Șterge">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                    <div class="mt-1">
                      <a href="{{ route('vehicle.show', $v['slug']) }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="Vezi pe site">
                        <i class="bi bi-box-arrow-up-right"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center py-4">
                    <div class="text-muted">
                      <i class="bi bi-car-front" style="font-size: 3rem;"></i>
                      <div class="mt-2">Nu s-au găsit vehicule</div>
                      @if($q)
                        <div class="small">pentru căutarea "{{ $q }}"</div>
                      @endif
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    @if(count($items) > 0)
      <div class="mt-3 text-muted text-center">
        Afișate {{ count($items) }} vehicule
      </div>
    @endif
  </div>
</section>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmare ștergere</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Ești sigur că vrei să ștergi vehiculul <strong id="delete-vehicle-name"></strong>?</p>
        <p class="text-danger small">Această acțiune nu poate fi anulată!</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulează</button>
        <form id="delete-form" method="POST" style="display: inline;">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Șterge vehicul</button>
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
  const bulkActions = document.getElementById('bulk-actions');
  bulkActions.style.display = checked.length > 0 ? 'block' : 'none';
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
  if (selected.length === 0) return;
  
  if (action === 'delete' && !confirm(`Ștergi ${selected.length} vehicule?`)) return;
  
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
  const placeholder = img.parentElement.querySelector('.image-error');
  if (placeholder) {
    placeholder.style.display = 'flex';
  }
}

// Image error handling
document.addEventListener('DOMContentLoaded', function() {
  const images = document.querySelectorAll('.vehicle-thumb');
  images.forEach(img => {
    img.addEventListener('error', function() {
      handleImageError(this);
    });
  });
});
</script>
@endpush
@endsection