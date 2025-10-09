@extends('layouts.admin')

@section('title', 'Admin • Dashboard')

@push('styles')
<style>
  /* Mobile-first responsive dashboard styles */
  .dashboard-card { 
    border-radius: 12px; 
    box-shadow: 0 2px 8px rgba(0,0,0,0.1); 
    border: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    margin-bottom: 1rem;
  }
  
  .dashboard-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
  }
  
  .stats-number { 
    font-size: 1.75rem; 
    font-weight: 700; 
    line-height: 1;
  }
  
  .stats-label {
    font-size: 0.85rem;
    font-weight: 500;
    opacity: 0.9;
    margin-top: 0.25rem;
  }
  
  .stats-icon {
    font-size: 1.5rem;
    opacity: 0.8;
  }
  
  /* Gradient backgrounds */
  .card-gradient-1 { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
  .card-gradient-2 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
  .card-gradient-3 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
  .card-gradient-4 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; }
  .card-gradient-5 { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; }
  .card-gradient-6 { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; }
  
  .quick-action-card {
    background: white;
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  
  .quick-action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    border-color: #007bff;
  }
  
  .quick-action-icon {
    font-size: 2rem;
    margin-bottom: 0.75rem;
    opacity: 0.8;
  }
  
  .recent-activity {
    background: white;
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    height: 100%;
  }
  
  .activity-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f4;
  }
  
  .activity-item:last-child {
    border-bottom: none;
  }
  
  .activity-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
  }
  
  /* Mobile responsive adjustments */
  @media (max-width: 767.98px) {
    .stats-number { 
      font-size: 1.5rem; 
    }
    
    .stats-label {
      font-size: 0.8rem;
    }
    
    .stats-icon {
      font-size: 1.25rem;
    }
    
    .quick-action-card {
      padding: 0.75rem;
      margin-bottom: 1rem;
    }
    
    .quick-action-icon {
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
    }
    
    .recent-activity {
      padding: 0.75rem;
    }
    
    .activity-icon {
      width: 32px;
      height: 32px;
      font-size: 0.9rem;
    }
    
    .dashboard-card .card-body {
      padding: 1rem;
    }
  }
  
  /* Tablet adjustments */
  @media (min-width: 768px) and (max-width: 991.98px) {
    .stats-number { 
      font-size: 1.6rem; 
    }
    
    .quick-action-icon {
      font-size: 1.75rem;
    }
  }
  
  /* Desktop adjustments */
  @media (min-width: 992px) {
    .stats-number { 
      font-size: 2rem; 
    }
    
    .quick-action-icon {
      font-size: 2.25rem;
    }
    
    .recent-activity {
      padding: 1.5rem;
    }
  }
</style>
@endpush

@section('content')
<section class="py-2 py-md-4">
  <div class="container-fluid px-2 px-md-3">
    <!-- Welcome Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mb-md-4">
      <div class="mb-2 mb-md-0">
        <h1 class="h4 h-md-3 mb-1 fw-bold">¡Bienvenido al Panel de Administración!</h1>
        <p class="text-muted mb-0 small">Gestiona tu concesionario de forma eficiente</p>
      </div>
      <div class="text-start text-md-end">
        <div class="small text-muted">Última actualización</div>
        <div class="fw-medium">{{ now()->format('d/m/Y H:i') }}</div>
      </div>
    </div>

    <!-- Main Statistics -->
    <div class="row g-2 g-md-4 mb-3 mb-md-4">
      <div class="col-6 col-md-3">
        <div class="card dashboard-card card-gradient-1">
          <div class="card-body p-2 p-md-3">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="stats-number">{{ $totalVehicles ?? 0 }}</div>
                <div class="stats-label">Total Vehículos</div>
              </div>
              <div class="stats-icon">
                <i class="bi bi-car-front"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card dashboard-card card-gradient-2">
          <div class="card-body p-2 p-md-3">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="stats-number">{{ $availableVehicles ?? 0 }}</div>
                <div class="stats-label">Disponibles</div>
              </div>
              <div class="stats-icon">
                <i class="bi bi-check-circle"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card dashboard-card card-gradient-3">
          <div class="card-body p-2 p-md-3">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="stats-number">{{ $totalInquiries ?? 0 }}</div>
                <div class="stats-label">Consultas</div>
              </div>
              <div class="stats-icon">
                <i class="bi bi-chat-dots"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card dashboard-card card-gradient-4">
          <div class="card-body p-2 p-md-3">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="stats-number">{{ $soldVehicles ?? 0 }}</div>
                <div class="stats-label">Vendidos</div>
              </div>
              <div class="stats-icon">
                <i class="bi bi-currency-euro"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Secondary Statistics -->
    <div class="row g-2 g-md-4 mb-3 mb-md-4">
      <div class="col-12 col-md-4">
        <div class="card dashboard-card card-gradient-5">
          <div class="card-body p-2 p-md-3">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="stats-number">{{ $featuredVehicles ?? 0 }}</div>
                <div class="stats-label">Vehículos Destacados</div>
              </div>
              <div class="stats-icon">
                <i class="bi bi-star-fill"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="card dashboard-card card-gradient-6">
          <div class="card-body p-2 p-md-3">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="stats-number">{{ $pendingSellCars ?? 0 }}</div>
                <div class="stats-label">Coches para Vender</div>
              </div>
              <div class="stats-icon">
                <i class="bi bi-car-front-fill"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="card dashboard-card card-gradient-1">
          <div class="card-body p-2 p-md-3">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="stats-number">{{ $totalMessages ?? 0 }}</div>
                <div class="stats-label">Mensajes de Contacto</div>
              </div>
              <div class="stats-icon">
                <i class="bi bi-envelope"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-2 g-md-4 mb-3 mb-md-4">
      <div class="col-12">
        <h5 class="h6 h-md-4 fw-bold mb-2 mb-md-3">Acciones Rápidas</h5>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.vehicles.create') }}" class="text-decoration-none">
          <div class="quick-action-card">
            <div class="quick-action-icon text-primary">
              <i class="bi bi-plus-circle"></i>
            </div>
            <h6 class="fw-bold small">Añadir Vehículo</h6>
            <p class="small text-muted mb-0 d-none d-md-block">Agregar nuevo vehículo al inventario</p>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.vehicles.index') }}" class="text-decoration-none">
          <div class="quick-action-card">
            <div class="quick-action-icon text-info">
              <i class="bi bi-list-ul"></i>
            </div>
            <h6 class="fw-bold small">Gestionar Vehículos</h6>
            <p class="small text-muted mb-0 d-none d-md-block">Ver y editar inventario</p>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.sell-cars.index') }}" class="text-decoration-none">
          <div class="quick-action-card">
            <div class="quick-action-icon text-warning">
              <i class="bi bi-car-front"></i>
            </div>
            <h6 class="fw-bold small">Coches para Vender</h6>
            <p class="small text-muted mb-0 d-none d-md-block">Revisar solicitudes de venta</p>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.inquiries.index') }}" class="text-decoration-none">
          <div class="quick-action-card">
            <div class="quick-action-icon text-success">
              <i class="bi bi-chat-dots"></i>
            </div>
            <h6 class="fw-bold small">Consultas</h6>
            <p class="small text-muted mb-0 d-none d-md-block">Ver solicitudes de prueba</p>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.contacts.index') }}" class="text-decoration-none">
          <div class="quick-action-card">
            <div class="quick-action-icon text-danger">
              <i class="bi bi-envelope-open"></i>
            </div>
            <h6 class="fw-bold small">Mensajes</h6>
            <p class="small text-muted mb-0 d-none d-md-block">Revisar mensajes de contacto</p>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('home') }}" class="text-decoration-none" target="_blank">
          <div class="quick-action-card">
            <div class="quick-action-icon text-secondary">
              <i class="bi bi-eye"></i>
            </div>
            <h6 class="fw-bold small">Ver Sitio</h6>
            <p class="small text-muted mb-0 d-none d-md-block">Abrir sitio web</p>
          </div>
        </a>
      </div>
    </div>

    <!-- Recent Activity & Quick Stats -->
    <div class="row g-4">
      <div class="col-md-8">
        <div class="recent-activity">
          <h5 class="fw-bold mb-3">Actividad Reciente</h5>
          <div class="activity-item">
            <div class="d-flex align-items-center">
              <div class="activity-icon bg-primary text-white me-3">
                <i class="bi bi-plus-circle"></i>
              </div>
              <div class="flex-grow-1">
                <div class="fw-medium">Nuevo vehículo añadido</div>
                <div class="small text-muted">BMW X5 2023 - hace 2 horas</div>
              </div>
            </div>
          </div>
          <div class="activity-item">
            <div class="d-flex align-items-center">
              <div class="activity-icon bg-success text-white me-3">
                <i class="bi bi-chat-dots"></i>
              </div>
              <div class="flex-grow-1">
                <div class="fw-medium">Nueva consulta recibida</div>
                <div class="small text-muted">Solicitud de prueba para Audi A4 - hace 4 horas</div>
              </div>
            </div>
          </div>
          <div class="activity-item">
            <div class="d-flex align-items-center">
              <div class="activity-icon bg-warning text-white me-3">
                <i class="bi bi-car-front"></i>
              </div>
              <div class="flex-grow-1">
                <div class="fw-medium">Nueva solicitud de venta</div>
                <div class="small text-muted">Mercedes C-Class 2020 - hace 6 horas</div>
              </div>
            </div>
          </div>
          <div class="activity-item">
            <div class="d-flex align-items-center">
              <div class="activity-icon bg-info text-white me-3">
                <i class="bi bi-envelope"></i>
              </div>
              <div class="flex-grow-1">
                <div class="fw-medium">Mensaje de contacto</div>
                <div class="small text-muted">Consulta sobre financiación - hace 8 horas</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="recent-activity">
          <h5 class="fw-bold mb-3">Resumen del Mes</h5>
          <div class="row g-3">
            <div class="col-6">
              <div class="text-center">
                <div class="h4 fw-bold text-primary">{{ $monthlyVehicles ?? 0 }}</div>
                <div class="small text-muted">Vehículos añadidos</div>
              </div>
            </div>
            <div class="col-6">
              <div class="text-center">
                <div class="h4 fw-bold text-success">{{ $monthlySales ?? 0 }}</div>
                <div class="small text-muted">Vehículos vendidos</div>
              </div>
            </div>
            <div class="col-6">
              <div class="text-center">
                <div class="h4 fw-bold text-info">{{ $monthlyInquiries ?? 0 }}</div>
                <div class="small text-muted">Consultas recibidas</div>
              </div>
            </div>
            <div class="col-6">
              <div class="text-center">
                <div class="h4 fw-bold text-warning">{{ $monthlyViews ?? 0 }}</div>
                <div class="small text-muted">Vistas totales</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
