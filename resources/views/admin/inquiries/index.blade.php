@extends('layouts.admin')

@section('title','Admin • Solicitudes')

@push('styles')
<style>
  /* Mobile-first responsive styles for inquiries */
  .inquiry-card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 1rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  
  .inquiry-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
  }
  
  .inquiry-header {
    border-bottom: 1px solid #e9ecef;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 12px 12px 0 0;
  }
  
  .inquiry-body {
    padding: 1rem;
  }
  
  .vehicle-info {
    background: #e3f2fd;
    border-left: 4px solid #2196f3;
    padding: 0.75rem;
    border-radius: 0 6px 6px 0;
    margin-bottom: 0.75rem;
  }
  
  .customer-info {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
  }
  
  .customer-info-item {
    background: #e9ecef;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.8rem;
    color: #495057;
  }
  
  .inquiry-message {
    background: #f8f9fa;
    border-left: 4px solid #28a745;
    padding: 0.75rem;
    border-radius: 0 6px 6px 0;
    font-size: 0.9rem;
    line-height: 1.4;
    margin-bottom: 0.75rem;
  }
  
  .status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
  }
  
  .status-new { background: #e3f2fd; color: #1976d2; }
  .status-contacted { background: #fff3e0; color: #f57c00; }
  .status-completed { background: #e8f5e8; color: #2e7d32; }
  .status-cancelled { background: #ffebee; color: #d32f2f; }
  
  .table-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }
  
  /* Mobile responsive adjustments */
  @media (max-width: 767.98px) {
    .inquiry-header {
      padding: 0.75rem;
    }
    
    .inquiry-body {
      padding: 0.75rem;
    }
    
    .vehicle-info {
      padding: 0.5rem;
      font-size: 0.85rem;
    }
    
    .customer-info {
      gap: 0.25rem;
    }
    
    .customer-info-item {
      font-size: 0.75rem;
      padding: 0.2rem 0.4rem;
    }
    
    .inquiry-message {
      padding: 0.5rem;
      font-size: 0.85rem;
    }
  }
</style>
@endpush

@section('content')
<section class="py-2 py-md-4">
  <div class="container-fluid px-2 px-md-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mb-md-4">
      <div class="mb-2 mb-md-0">
        <h1 class="h4 h-md-3 mb-1 fw-bold">Solicitudes de oferta / test drive</h1>
        <p class="text-muted mb-0 small">Gestiona las consultas de clientes interesados en vehículos</p>
      </div>
      <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
        <div class="text-muted small">
          Total: {{ $inquiries->total() }} solicitudes
        </div>
      </div>
    </div>

    @if($inquiries->count() > 0)
      <!-- Desktop Table View -->
      <div class="table-container d-none d-lg-block mb-4">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th width="120">Fecha</th>
                <th width="250">Vehículo</th>
                <th width="150">Cliente</th>
                <th width="120">Teléfono</th>
                <th>Mensaje</th>
                <th width="100">Estado</th>
                <th width="120">Acciones</th>
              </tr>
            </thead>
            <tbody>
              @foreach($inquiries as $inq)
              <tr>
                <td>
                  <div class="fw-medium">{{ $inq->created_at->format('d/m/Y') }}</div>
                  <div class="small text-muted">{{ $inq->created_at->format('H:i') }}</div>
                </td>
                <td>
                  <div class="fw-medium">{{ $inq->vehicle_title }}</div>
                  @if($inq->vehicle_link)
                    <a href="{{ $inq->vehicle_link }}" target="_blank" class="small text-primary">
                      <i class="bi bi-box-arrow-up-right me-1"></i>Ver página
                    </a>
                  @endif
                </td>
                <td>
                  <div class="fw-medium">{{ $inq->name }}</div>
                </td>
                <td>
                  <a href="tel:{{ $inq->phone }}" class="text-decoration-none">
                    <i class="bi bi-telephone me-1"></i>{{ $inq->phone }}
                  </a>
                </td>
                <td>
                  <div class="text-truncate" style="max-width: 300px;" title="{{ $inq->message }}">
                    {{ $inq->message }}
                  </div>
                </td>
                <td>
                  <span class="status-badge status-{{ $inq->status ?? 'new' }}">
                    {{ ucfirst($inq->status ?? 'new') }}
                  </span>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewInquiry({{ $inq->id }})" title="Ver completo">
                      <i class="bi bi-eye"></i>
                    </button>
                    <a href="tel:{{ $inq->phone }}" class="btn btn-outline-success" title="Llamar">
                      <i class="bi bi-telephone"></i>
                    </a>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <!-- Mobile Card View -->
      <div class="d-lg-none mb-4">
        @foreach($inquiries as $inq)
        <div class="card inquiry-card">
          <div class="inquiry-header">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="mb-1 fw-bold">{{ $inq->name }}</h6>
                <div class="small text-muted">
                  <i class="bi bi-calendar me-1"></i>{{ $inq->created_at->format('d/m/Y H:i') }}
                </div>
              </div>
              <span class="status-badge status-{{ $inq->status ?? 'new' }}">
                {{ ucfirst($inq->status ?? 'new') }}
              </span>
            </div>
          </div>
          
          <div class="inquiry-body">
            <!-- Vehicle Info -->
            <div class="vehicle-info">
              <div class="fw-medium mb-1">
                <i class="bi bi-car-front me-2"></i>{{ $inq->vehicle_title }}
              </div>
              @if($inq->vehicle_link)
                <a href="{{ $inq->vehicle_link }}" target="_blank" class="small text-primary">
                  <i class="bi bi-box-arrow-up-right me-1"></i>Ver página del vehículo
                </a>
              @endif
            </div>
            
            <!-- Customer Info -->
            <div class="customer-info">
              <a href="tel:{{ $inq->phone }}" class="customer-info-item text-decoration-none">
                <i class="bi bi-telephone me-1"></i>{{ $inq->phone }}
              </a>
            </div>
            
            <!-- Message -->
            <div class="inquiry-message">
              <strong>Mensaje:</strong><br>
              {{ $inq->message }}
            </div>
            
            <!-- Actions -->
            <div class="d-flex gap-2">
              <button class="btn btn-outline-primary btn-sm flex-fill" onclick="viewInquiry({{ $inq->id }})">
                <i class="bi bi-eye me-1"></i>Ver completo
              </button>
              <a href="tel:{{ $inq->phone }}" class="btn btn-outline-success btn-sm flex-fill">
                <i class="bi bi-telephone me-1"></i>Llamar
              </a>
            </div>
          </div>
        </div>
        @endforeach
      </div>

      <!-- Pagination -->
      <div class="d-flex justify-content-center mb-4">
        {{ $inquiries->links('custom-pagination') }}
      </div>
    @else
      <!-- Empty State -->
      <div class="text-center py-5 mb-4">
        <div class="text-muted">
          <i class="bi bi-chat-dots fs-1 d-block mb-3"></i>
          <h5>No hay solicitudes</h5>
          <p>No se han recibido solicitudes de oferta o test drive aún.</p>
        </div>
      </div>
    @endif

    <!-- Statistics Card -->
    <div class="card">
      <div class="card-body p-3 p-md-4">
        <h5 class="card-title fw-bold mb-3">
          <i class="bi bi-graph-up me-2"></i>Estadísticas de solicitudes
        </h5>
        <div class="row g-2 g-md-4">
          <div class="col-6 col-md-3">
            <div class="text-center p-2 p-md-3 bg-primary bg-opacity-10 rounded">
              <div class="h4 h-md-3 fw-bold text-primary mb-1">{{ $inquiries->where('status', 'new')->count() }}</div>
              <div class="small text-muted">Nuevas</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="text-center p-2 p-md-3 bg-warning bg-opacity-10 rounded">
              <div class="h4 h-md-3 fw-bold text-warning mb-1">{{ $inquiries->where('status', 'contacted')->count() }}</div>
              <div class="small text-muted">Contactadas</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="text-center p-2 p-md-3 bg-success bg-opacity-10 rounded">
              <div class="h4 h-md-3 fw-bold text-success mb-1">{{ $inquiries->where('status', 'completed')->count() }}</div>
              <div class="small text-muted">Completadas</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="text-center p-2 p-md-3 bg-danger bg-opacity-10 rounded">
              <div class="h4 h-md-3 fw-bold text-danger mb-1">{{ $inquiries->where('status', 'cancelled')->count() }}</div>
              <div class="small text-muted">Canceladas</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Inquiry Modal -->
<div class="modal fade" id="inquiryModal" tabindex="-1" aria-labelledby="inquiryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="inquiryModalLabel">Solicitud de oferta / test drive</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="inquiryModalBody">
        <!-- Content will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" id="callButton">
          <i class="bi bi-telephone me-2"></i>Llamar
        </button>
        <button type="button" class="btn btn-primary" id="updateStatusButton">
          <i class="bi bi-check-circle me-2"></i>Marcar como contactado
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
// Mobile-friendly inquiry viewing
function viewInquiry(inquiryId) {
  // Debug: Check if Bootstrap is loaded
  if (typeof bootstrap === 'undefined') {
    console.error('Bootstrap is not loaded!');
    alert('Bootstrap nu este încărcat. Te rog reîmprospătează pagina.');
    return;
  }
  // Find the inquiry data from the current page
  const inquiries = @json($inquiries->items());
  const inquiry = inquiries.find(i => i.id === inquiryId);
  
  if (inquiry) {
    const modalBody = document.getElementById('inquiryModalBody');
    const callButton = document.getElementById('callButton');
    const updateStatusButton = document.getElementById('updateStatusButton');
    
    modalBody.innerHTML = `
      <div class="row g-3">
        <div class="col-md-6">
          <strong>Cliente:</strong><br>
          ${inquiry.name}
        </div>
        <div class="col-md-6">
          <strong>Fecha:</strong><br>
          ${new Date(inquiry.created_at).toLocaleString('es-ES')}
        </div>
        <div class="col-md-6">
          <strong>Teléfono:</strong><br>
          <a href="tel:${inquiry.phone}">${inquiry.phone}</a>
        </div>
        <div class="col-md-6">
          <strong>Estado:</strong><br>
          <span class="status-badge status-${inquiry.status || 'new'}">${inquiry.status || 'new'}</span>
        </div>
        <div class="col-12">
          <strong>Vehículo de interés:</strong><br>
          <div class="p-3 bg-light rounded">
            <div class="fw-medium">${inquiry.vehicle_title}</div>
            ${inquiry.vehicle_link ? `<a href="${inquiry.vehicle_link}" target="_blank" class="small text-primary"><i class="bi bi-box-arrow-up-right me-1"></i>Ver página del vehículo</a>` : ''}
          </div>
        </div>
        <div class="col-12">
          <strong>Mensaje:</strong><br>
          <div class="p-3 bg-light rounded">${inquiry.message}</div>
        </div>
      </div>
    `;
    
    callButton.onclick = () => {
      window.location.href = `tel:${inquiry.phone}`;
    };
    
    updateStatusButton.onclick = () => {
      updateInquiryStatus(inquiry.id, 'contacted');
    };
    
    const modal = new bootstrap.Modal(document.getElementById('inquiryModal'));
    modal.show();
    
    // Debug: Log modal events
    document.getElementById('inquiryModal').addEventListener('shown.bs.modal', function () {
      console.log('Inquiry modal shown');
    });
    
    document.getElementById('inquiryModal').addEventListener('hidden.bs.modal', function () {
      console.log('Inquiry modal hidden');
    });
  }
}

function updateInquiryStatus(inquiryId, status) {
  // This would typically make an AJAX call to update the status
  // For now, we'll just show a success message
  alert(`Estado actualizado a: ${status}`);
  
  // Close modal and refresh page
  const modal = bootstrap.Modal.getInstance(document.getElementById('inquiryModal'));
  modal.hide();
  
  // In a real implementation, you would make an AJAX call here
  // and update the UI accordingly
  setTimeout(() => {
    window.location.reload();
  }, 500);
}

// Auto-refresh functionality for new inquiries
let lastInquiryCount = {{ $inquiries->total() }};

function checkForNewInquiries() {
  fetch(window.location.href, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.total > lastInquiryCount) {
      // Show notification for new inquiries
      const notification = document.createElement('div');
      notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
      notification.style.cssText = 'top: 70px; right: 20px; z-index: 1050; max-width: 300px;';
      notification.innerHTML = `
        <i class="bi bi-chat-dots me-2"></i>
        ${data.total - lastInquiryCount} nueva(s) solicitud(es) recibida(s)
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      document.body.appendChild(notification);
      
      lastInquiryCount = data.total;
      
      // Auto-remove notification after 5 seconds
      setTimeout(() => {
        if (notification.parentNode) {
          notification.remove();
        }
      }, 5000);
    }
  })
  .catch(error => console.log('Error checking for new inquiries:', error));
}

// Check for new inquiries every 30 seconds
setInterval(checkForNewInquiries, 30000);
</script>
@endpush
@endsection