@extends('layouts.admin')

@section('title', 'Mașini de vânzare - Admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2"><i class="bi bi-car-front me-2"></i>Mașini de vânzare</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <div class="btn-group me-2">
      <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
    </div>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<div class="table-responsive">
  <table class="table table-striped table-hover">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Imagine</th>
        <th>Mașină</th>
        <th>Preț</th>
        <th>Vânzător</th>
        <th>Status</th>
        <th>Data</th>
        <th>Acțiuni</th>
      </tr>
    </thead>
    <tbody>
      @forelse($vehicles as $vehicle)
        <tr>
          <td>{{ $vehicle->id }}</td>
          <td>
            @if($vehicle->images && is_array($vehicle->images) && count($vehicle->images) > 0)
              <img src="{{ Storage::url($vehicle->images[0]) }}" 
                   alt="{{ $vehicle->title }}" 
                   class="img-thumbnail" 
                   style="width: 60px; height: 40px; object-fit: cover;">
            @else
              <div class="bg-light d-flex align-items-center justify-content-center" 
                   style="width: 60px; height: 40px;">
                <i class="bi bi-image text-muted"></i>
              </div>
            @endif
          </td>
          <td>
            <div>
              <strong>{{ $vehicle->title }}</strong><br>
              <small class="text-muted">{{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->year }})</small>
            </div>
          </td>
          <td>
            <strong class="text-success">{{ number_format($vehicle->price, 0, ',', '.') }} EUR</strong>
          </td>
          <td>
            <div>
              <strong>{{ $vehicle->seller_name }}</strong><br>
              <small class="text-muted">{{ $vehicle->seller_phone }}</small><br>
              <small class="text-muted">{{ $vehicle->seller_email }}</small>
            </div>
          </td>
          <td>
            @switch($vehicle->status)
              @case('pending')
                <span class="badge bg-warning">În așteptare</span>
                @break
              @case('available')
                <span class="badge bg-success">Publicat</span>
                @break
              @case('rejected')
                <span class="badge bg-danger">Respins</span>
                @break
              @default
                <span class="badge bg-secondary">{{ $vehicle->status }}</span>
            @endswitch
          </td>
          <td>
            <small>{{ $vehicle->created_at->format('d.m.Y H:i') }}</small>
          </td>
          <td>
            <div class="btn-group btn-group-sm" role="group">
              <a href="{{ route('admin.sell-cars.show', $vehicle) }}" 
                 class="btn btn-outline-primary" 
                 title="Vezi detalii">
                <i class="bi bi-eye"></i>
              </a>
              
              <a href="{{ route('admin.sell-cars.edit', $vehicle) }}" 
                 class="btn btn-outline-info" 
                 title="Editează">
                <i class="bi bi-pencil"></i>
              </a>
              
              @if($vehicle->status === 'pending')
                <form action="{{ route('admin.sell-cars.approve', $vehicle) }}" 
                      method="POST" 
                      class="d-inline"
                      onsubmit="return confirm('Ești sigur că vrei să aprobi acest anunț?')">
                  @csrf
                  <button type="submit" class="btn btn-outline-success" title="Aprobă">
                    <i class="bi bi-check"></i>
                  </button>
                </form>
                
                <form action="{{ route('admin.sell-cars.reject', $vehicle) }}" 
                      method="POST" 
                      class="d-inline"
                      onsubmit="return confirm('Ești sigur că vrei să respingi acest anunț?')">
                  @csrf
                  <button type="submit" class="btn btn-outline-warning" title="Respinge">
                    <i class="bi bi-x"></i>
                  </button>
                </form>
              @endif
              
              <form action="{{ route('admin.sell-cars.destroy', $vehicle) }}" 
                    method="POST" 
                    class="d-inline"
                    onsubmit="return confirm('Ești sigur că vrei să ștergi acest anunț? Această acțiune nu poate fi anulată!')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger" title="Șterge">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="text-center py-4">
            <i class="bi bi-inbox display-4 text-muted"></i>
            <p class="text-muted mt-2">Nu există mașini de vânzare în așteptare.</p>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if($vehicles->hasPages())
  <div class="d-flex justify-content-center">
    {{ $vehicles->links() }}
  </div>
@endif
@endsection
