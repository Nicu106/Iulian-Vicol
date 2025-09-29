@extends('layouts.admin')

@section('title', 'Admin • Analiză prețuri și oferte')

@push('styles')
<style>
  .admin-card { border-radius: 16px; }
  .stats-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
  .stats-number { font-size: 2rem; font-weight: 700; }
  .chart-container { height: 300px; }
  .price-trend { font-size: 0.875rem; }
  .price-trend.up { color: #dc2626; }
  .price-trend.down { color: #16a34a; }
  .price-trend.stable { color: #6b7280; }
  .offer-badge { display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
  .offer-flash { background: #fef3c7; color: #92400e; }
  .offer-seasonal { background: #dbeafe; color: #1e40af; }
  .offer-clearance { background: #fee2e2; color: #991b1b; }
  .offer-negotiable { background: #dcfce7; color: #166534; }
</style>
@endpush

@section('content')
<section class="py-4">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h4 mb-0">Analiză prețuri și oferte</h1>
      <div>
        <a href="{{ route('admin.vehicles.export-pricing') }}" class="btn btn-info me-2">
          <i class="bi bi-download"></i> Export raport
        </a>
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary">Înapoi la lista vehicule</a>
      </div>
    </div>

    @if(session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <!-- Key Metrics -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card stats-card text-center">
          <div class="card-body">
            <div class="stats-number">{{ $totalVehicles }}</div>
            <div>Total vehicule</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card text-center">
          <div class="card-body">
            <div class="stats-number">€{{ number_format($avgPrice, 0, ',', '.') }}</div>
            <div>Preț mediu</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card text-center">
          <div class="card-body">
            <div class="stats-number">{{ $vehiclesWithOffers }}</div>
            <div>Cu oferte active</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card text-center">
          <div class="card-body">
            <div class="stats-number">{{ number_format($totalValue, 0, ',', '.') }}</div>
            <div>Valoare totală (mii €)</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Price Distribution -->
    <div class="row g-4 mb-4">
      <div class="col-lg-6">
        <div class="card admin-card shadow-sm">
          <div class="card-header">
            <h6 class="mb-0">Distribuția prețurilor</h6>
          </div>
          <div class="card-body">
            <div class="chart-container">
              <canvas id="priceDistributionChart"></canvas>
            </div>
            <div class="row g-2 mt-3">
              @foreach($priceRanges as $range => $count)
              <div class="col-6">
                <div class="d-flex justify-content-between">
                  <span>{{ $range }}</span>
                  <span class="fw-bold">{{ $count }}</span>
                </div>
                <div class="progress" style="height: 6px;">
                  <div class="progress-bar" style="width: {{ ($count / $totalVehicles) * 100 }}%"></div>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card admin-card shadow-sm">
          <div class="card-header">
            <h6 class="mb-0">Tipuri de oferte</h6>
          </div>
          <div class="card-body">
            <div class="chart-container">
              <canvas id="offerTypesChart"></canvas>
            </div>
            <div class="row g-2 mt-3">
              @foreach($offerTypes as $type => $count)
              <div class="col-6">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="offer-badge offer-{{ $type }}">{{ ucfirst($type) }}</span>
                  <span class="fw-bold">{{ $count }}</span>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Offers -->
    <div class="card admin-card shadow-sm mb-4">
      <div class="card-header">
        <h6 class="mb-0">Top oferte active</h6>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Vehicul</th>
                <th>Preț original</th>
                <th>Preț ofertă</th>
                <th>Discount</th>
                <th>Tip ofertă</th>
                <th>Expiră la</th>
                <th>Acțiuni</th>
              </tr>
            </thead>
            <tbody>
              @forelse($topOffers as $offer)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    @if(!empty($offer['cover_image']))
                      <img src="{{ $offer['cover_image'] }}" alt="{{ $offer['title'] }}" class="vehicle-thumb me-2">
                    @endif
                    <div>
                      <div class="fw-bold">{{ $offer['title'] }}</div>
                      <div class="small text-muted">{{ $offer['year'] }} • {{ $offer['mileage'] }}</div>
                    </div>
                  </div>
                </td>
                <td>€{{ number_format($offer['original_price'] ?? $offer['price'], 0, ',', '.') }}</td>
                <td class="text-success fw-bold">€{{ number_format($offer['offer_price'], 0, ',', '.') }}</td>
                <td>
                  @php
                    $originalPrice = $offer['original_price'] ?? $offer['price'];
                    $discount = $originalPrice > 0 ? (($originalPrice - $offer['offer_price']) / $originalPrice) * 100 : 0;
                  @endphp
                  <span class="badge bg-success">-{{ number_format($discount, 1) }}%</span>
                </td>
                <td>
                  <span class="offer-badge offer-{{ $offer['offer_type'] ?? 'standard' }}">
                    {{ ucfirst($offer['offer_type'] ?? 'standard') }}
                  </span>
                </td>
                <td>
                  @if($offer['offer_expires_at'])
                    {{ \Carbon\Carbon::parse($offer['offer_expires_at'])->format('d.m.Y') }}
                  @else
                    <span class="text-muted">Fără expirare</span>
                  @endif
                </td>
                <td>
                  <a href="{{ route('admin.vehicles.edit', $offer['slug']) }}" class="btn btn-sm btn-outline-primary">Editează</a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">Nu există oferte active</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Price Trends -->
    <div class="card admin-card shadow-sm mb-4">
      <div class="card-header">
        <h6 class="mb-0">Evoluția prețurilor (ultimele 30 zile)</h6>
      </div>
      <div class="card-body">
        <div class="chart-container">
          <canvas id="priceTrendsChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Market Analysis -->
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="card admin-card shadow-sm">
          <div class="card-header">
            <h6 class="mb-0">Analiză piață</h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span>Prețuri în creștere</span>
                <span class="badge bg-success">{{ $priceTrends['increasing'] }}</span>
              </div>
              <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-success" style="width: {{ ($priceTrends['increasing'] / $totalVehicles) * 100 }}%"></div>
              </div>
            </div>
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span>Prețuri în scădere</span>
                <span class="badge bg-danger">{{ $priceTrends['decreasing'] }}</span>
              </div>
              <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-danger" style="width: {{ ($priceTrends['decreasing'] / $totalVehicles) * 100 }}%"></div>
              </div>
            </div>
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span>Prețuri stabile</span>
                <span class="badge bg-secondary">{{ $priceTrends['stable'] }}</span>
              </div>
              <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-secondary" style="width: {{ ($priceTrends['stable'] / $totalVehicles) * 100 }}%"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card admin-card shadow-sm">
          <div class="card-header">
            <h6 class="mb-0">Recomandări</h6>
          </div>
          <div class="card-body">
            @if($recommendations)
              @foreach($recommendations as $rec)
              <div class="alert alert-info mb-2">
                <i class="bi bi-lightbulb me-2"></i>
                {{ $rec }}
              </div>
              @endforeach
            @else
              <div class="text-muted text-center py-3">
                <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                <div class="mt-2">Toate prețurile sunt optimizate!</div>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Price Distribution Chart
  const priceCtx = document.getElementById('priceDistributionChart').getContext('2d');
  new Chart(priceCtx, {
    type: 'doughnut',
    data: {
      labels: {!! json_encode(array_keys($priceRanges)) !!},
      datasets: [{
        data: {!! json_encode(array_values($priceRanges)) !!},
        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  });

  // Offer Types Chart
  const offerCtx = document.getElementById('offerTypesChart').getContext('2d');
  new Chart(offerCtx, {
    type: 'bar',
    data: {
      labels: {!! json_encode(array_keys($offerTypes)) !!},
      datasets: [{
        label: 'Număr vehicule',
        data: {!! json_encode(array_values($offerTypes)) !!},
        backgroundColor: '#3b82f6'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });

  // Price Trends Chart
  const trendCtx = document.getElementById('priceTrendsChart').getContext('2d');
  new Chart(trendCtx, {
    type: 'line',
    data: {
      labels: {!! json_encode(array_keys($priceTrendsData)) !!},
      datasets: [{
        label: 'Preț mediu',
        data: {!! json_encode(array_values($priceTrendsData)) !!},
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: false
        }
      }
    }
  });
});
</script>
@endpush
@endsection
