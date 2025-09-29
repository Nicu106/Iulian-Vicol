@extends('layouts.admin')

@section('title', 'Admin • Dashboard')

@push('styles')
<style>
  .dashboard-card { 
    border-radius: 16px; 
    box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
    border: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .dashboard-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  }
  .stats-number { 
    font-size: 2.5rem; 
    font-weight: 700; 
    line-height: 1;
  }
  .stats-label {
    font-size: 0.9rem;
    font-weight: 500;
    opacity: 0.8;
  }
  .stats-icon {
    font-size: 2rem;
    opacity: 0.7;
  }
  .card-gradient-1 { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
  .card-gradient-2 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
  .card-gradient-3 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
  .card-gradient-4 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; }
  .card-gradient-5 { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; }
  .card-gradient-6 { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; }
  
  .quick-action-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
  }
  .quick-action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    border-color: #007bff;
  }
  .quick-action-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    opacity: 0.8;
  }
  
  .recent-activity {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }
  
  .activity-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f4;
  }
  .activity-item:last-child {
    border-bottom: none;
  }
  
  .activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
  }
</style>
@endpush

@section('content')
<section class="py-4">
  <div class="container-fluid">
    <!-- Welcome Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h3 mb-1 fw-bold">¡Bienvenido al Panel de Administración!</h1>
        <p class="text-muted mb-0">Gestiona tu concesionario de forma eficiente</p>
      </div>
      <div class="text-end">
        <div class="small text-muted">Última actualización</div>
        <div class="fw-medium">{{ now()->format('d/m/Y H:i') }}</div>
      </div>
    </div>

    <!-- Main Statistics -->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="card dashboard-card card-gradient-1">
          <div class="card-body">
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
      <div class="col-md-3">
        <div class="card dashboard-card card-gradient-2">
          <div class="card-body">
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
      <div class="col-md-3">
        <div class="card dashboard-card card-gradient-3">
          <div class="card-body">
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
      <div class="col-md-3">
        <div class="card dashboard-card card-gradient-4">
          <div class="card-body">
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
    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="card dashboard-card card-gradient-5">
          <div class="card-body">
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
      <div class="col-md-4">
        <div class="card dashboard-card card-gradient-6">
          <div class="card-body">
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
      <div class="col-md-4">
        <div class="card dashboard-card card-gradient-1">
          <div class="card-body">
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
    <div class="row g-4 mb-4">
      <div class="col-12">
        <h4 class="fw-bold mb-3">Acciones Rápidas</h4>
      </div>
      <div class="col-md-2">
        <a href="{{ route('admin.vehicles.create') }}" class="text-decoration-none">
          <div class="quick-action-card">
            <div class="quick-action-icon text-primary">
              <i class="bi bi-plus-circle"></i>
            </div>
            <h6 class="fw-bold">Añadir Vehículo</h6>
            <p class="small text-muted mb-0">Agregar nuevo vehículo al inventario</p>
          </div>
        </a>
      </div>
      <div class="col-md-2">
        <a href="{{ route('admin.vehicles.index') }}" class="text-decoration-none">
          <div class="quick-action-card">
            <div class="quick-action-icon text-info">
              <i class="bi bi-list-ul"></i>
            </div>
            <h6 class="fw-bold">Gestionar Vehículos</h6>
            <p class="small text-muted mb-0">Ver y editar inventario</p>
          </div>
        </a>
      </div>
      <div class="col-md-2">
        <a href="{{ route('admin.sell-cars.index') }}" class="text-decoration-none">
          <div class="quick-action-card">
            <div class="quick-action-icon text-warning">
              <i class="bi bi-car-front"></i>
            </div>
            <h6 class="fw-bold">Coches para Vender</h6>
            <p class="small text-muted mb-0">Revisar solicitudes de venta</p>
          </div>
        </a>
      </div>
      <div class="col-md-2">
        <a href="{{ route('admin.inquiries.index') }}" class="text-decoration-none">
          <div class="quick-action-card">
            <div class="quick-action-icon text-success">
              <i class="bi bi-chat-dots"></i>
            </div>
            <h6 class="fw-bold">Consultas</h6>
            <p class="small text-muted mb-0">Ver solicitudes de prueba</p>
          </div>
        </a>
      </div>
      <div class="col-md-2">
        <a href="{{ route('admin.contacts.index') }}" class="text-decoration-none">
          <div class="quick-action-card">
            <div class="quick-action-icon text-danger">
              <i class="bi bi-envelope-open"></i>
            </div>
            <h6 class="fw-bold">Mensajes</h6>
            <p class="small text-muted mb-0">Revisar mensajes de contacto</p>
          </div>
        </a>
      </div>
      <div class="col-md-2">
        <a href="{{ route('home') }}" class="text-decoration-none" target="_blank">
          <div class="quick-action-card">
            <div class="quick-action-icon text-secondary">
              <i class="bi bi-eye"></i>
            </div>
            <h6 class="fw-bold">Ver Sitio</h6>
            <p class="small text-muted mb-0">Abrir sitio web</p>
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
