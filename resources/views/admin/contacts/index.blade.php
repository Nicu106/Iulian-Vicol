@extends('layouts.admin')

@section('title','Admin • Mensajes de Contacto')

@push('styles')
<style>
  /* Mobile-first responsive styles for contacts */
  .contact-card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 1rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  
  .contact-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
  }
  
  .contact-header {
    border-bottom: 1px solid #e9ecef;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 12px 12px 0 0;
  }
  
  .contact-body {
    padding: 1rem;
  }
  
  .contact-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
  }
  
  .contact-meta-item {
    background: #e9ecef;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.8rem;
    color: #495057;
  }
  
  .contact-message {
    background: #f8f9fa;
    border-left: 4px solid #007bff;
    padding: 0.75rem;
    border-radius: 0 6px 6px 0;
    font-size: 0.9rem;
    line-height: 1.4;
  }
  
  .table-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }
  
  /* Mobile responsive adjustments */
  @media (max-width: 767.98px) {
    .contact-header {
      padding: 0.75rem;
    }
    
    .contact-body {
      padding: 0.75rem;
    }
    
    .contact-meta {
      gap: 0.25rem;
    }
    
    .contact-meta-item {
      font-size: 0.75rem;
      padding: 0.2rem 0.4rem;
    }
    
    .contact-message {
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
        <h1 class="h4 h-md-3 mb-1 fw-bold">Mensajes de contacto</h1>
        <p class="text-muted mb-0 small">Gestiona las consultas recibidas desde el formulario de contacto</p>
      </div>
      <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
        <div class="text-muted small">
          Total: {{ $messages->total() }} mensajes
        </div>
      </div>
    </div>

    @if($messages->count() > 0)
      <!-- Desktop Table View -->
      <div class="table-container d-none d-lg-block">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th width="120">Fecha</th>
                <th width="150">Nombre</th>
                <th width="120">Teléfono</th>
                <th width="180">Email</th>
                <th width="150">Asunto</th>
                <th>Mensaje</th>
                <th width="100">Acciones</th>
              </tr>
            </thead>
            <tbody>
              @foreach($messages as $msg)
              <tr>
                <td>
                  <div class="fw-medium">{{ $msg->created_at->format('d/m/Y') }}</div>
                  <div class="small text-muted">{{ $msg->created_at->format('H:i') }}</div>
                </td>
                <td>
                  <div class="fw-medium">{{ $msg->name }}</div>
                </td>
                <td>
                  @if($msg->phone)
                    <a href="tel:{{ $msg->phone }}" class="text-decoration-none">
                      <i class="bi bi-telephone me-1"></i>{{ $msg->phone }}
                    </a>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  <a href="mailto:{{ $msg->email }}" class="text-decoration-none">
                    <i class="bi bi-envelope me-1"></i>{{ $msg->email }}
                  </a>
                </td>
                <td>
                  <span class="badge bg-primary">{{ $msg->subject ?? 'General' }}</span>
                </td>
                <td>
                  <div class="text-truncate" style="max-width: 300px;" title="{{ $msg->message }}">
                    {{ $msg->message }}
                  </div>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewMessage({{ $msg->id }})" title="Ver completo">
                      <i class="bi bi-eye"></i>
                    </button>
                    <a href="mailto:{{ $msg->email }}" class="btn btn-outline-success" title="Responder">
                      <i class="bi bi-reply"></i>
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
      <div class="d-lg-none">
        @foreach($messages as $msg)
        <div class="card contact-card">
          <div class="contact-header">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="mb-1 fw-bold">{{ $msg->name }}</h6>
                <div class="small text-muted">
                  <i class="bi bi-calendar me-1"></i>{{ $msg->created_at->format('d/m/Y H:i') }}
                </div>
              </div>
              <span class="badge bg-primary">{{ $msg->subject ?? 'General' }}</span>
            </div>
          </div>
          
          <div class="contact-body">
            <!-- Contact Info -->
            <div class="contact-meta">
              @if($msg->phone)
                <a href="tel:{{ $msg->phone }}" class="contact-meta-item text-decoration-none">
                  <i class="bi bi-telephone me-1"></i>{{ $msg->phone }}
                </a>
              @endif
              <a href="mailto:{{ $msg->email }}" class="contact-meta-item text-decoration-none">
                <i class="bi bi-envelope me-1"></i>{{ $msg->email }}
              </a>
              @if($msg->gdpr)
                <span class="contact-meta-item">
                  <i class="bi bi-shield-check me-1"></i>GDPR
                </span>
              @endif
              @if($msg->newsletter)
                <span class="contact-meta-item">
                  <i class="bi bi-newspaper me-1"></i>Newsletter
                </span>
              @endif
            </div>
            
            <!-- Message -->
            <div class="contact-message">
              {{ $msg->message }}
            </div>
            
            <!-- Actions -->
            <div class="d-flex gap-2 mt-3">
              <button class="btn btn-outline-primary btn-sm flex-fill" onclick="viewMessage({{ $msg->id }})">
                <i class="bi bi-eye me-1"></i>Ver completo
              </button>
              <a href="mailto:{{ $msg->email }}" class="btn btn-outline-success btn-sm flex-fill">
                <i class="bi bi-reply me-1"></i>Responder
              </a>
            </div>
          </div>
        </div>
        @endforeach
      </div>

      <!-- Pagination -->
      <div class="d-flex justify-content-center mt-4">
        {{ $messages->links('custom-pagination') }}
      </div>
    @else
      <!-- Empty State -->
      <div class="text-center py-5">
        <div class="text-muted">
          <i class="bi bi-envelope fs-1 d-block mb-3"></i>
          <h5>No hay mensajes</h5>
          <p>No se han recibido mensajes de contacto aún.</p>
        </div>
      </div>
    @endif
  </div>
</section>

<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="messageModalLabel">Mensaje de contacto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="messageModalBody">
        <!-- Content will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="replyButton">
          <i class="bi bi-reply me-2"></i>Responder
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
// Mobile-friendly message viewing
function viewMessage(messageId) {
  // Debug: Check if Bootstrap is loaded
  if (typeof bootstrap === 'undefined') {
    console.error('Bootstrap is not loaded!');
    alert('Bootstrap nu este încărcat. Te rog reîmprospătează pagina.');
    return;
  }
  // Find the message data from the current page
  const messages = @json($messages->items());
  const message = messages.find(m => m.id === messageId);
  
  if (message) {
    const modalBody = document.getElementById('messageModalBody');
    const replyButton = document.getElementById('replyButton');
    
    modalBody.innerHTML = `
      <div class="row g-3">
        <div class="col-md-6">
          <strong>Nombre:</strong><br>
          ${message.name}
        </div>
        <div class="col-md-6">
          <strong>Fecha:</strong><br>
          ${new Date(message.created_at).toLocaleString('es-ES')}
        </div>
        <div class="col-md-6">
          <strong>Teléfono:</strong><br>
          ${message.phone ? `<a href="tel:${message.phone}">${message.phone}</a>` : '-'}
        </div>
        <div class="col-md-6">
          <strong>Email:</strong><br>
          <a href="mailto:${message.email}">${message.email}</a>
        </div>
        <div class="col-12">
          <strong>Asunto:</strong><br>
          ${message.subject || 'General'}
        </div>
        <div class="col-12">
          <strong>Mensaje:</strong><br>
          <div class="p-3 bg-light rounded">${message.message}</div>
        </div>
        ${message.gdpr ? '<div class="col-12"><small class="text-muted"><i class="bi bi-shield-check me-1"></i>Ha aceptado la política de privacidad</small></div>' : ''}
        ${message.newsletter ? '<div class="col-12"><small class="text-muted"><i class="bi bi-newspaper me-1"></i>Ha solicitado suscribirse al newsletter</small></div>' : ''}
      </div>
    `;
    
    replyButton.onclick = () => {
      window.location.href = `mailto:${message.email}?subject=Re: ${message.subject || 'Consulta'}`;
    };
    
    const modal = new bootstrap.Modal(document.getElementById('messageModal'), {
      backdrop: false,
      keyboard: true
    });
    modal.show();
    
    // Debug: Log modal events
    document.getElementById('messageModal').addEventListener('shown.bs.modal', function () {
      console.log('Message modal shown');
    });
    
    document.getElementById('messageModal').addEventListener('hidden.bs.modal', function () {
      console.log('Message modal hidden');
    });
    
    // Force close modal when clicking outside
    document.getElementById('messageModal').addEventListener('click', function(e) {
      if (e.target === this) {
        modal.hide();
      }
    });
    
    // Force close modal with ESC key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && document.getElementById('messageModal').classList.contains('show')) {
        modal.hide();
      }
    });
  }
}

// Auto-refresh functionality for new messages
let lastMessageCount = {{ $messages->total() }};

function checkForNewMessages() {
  fetch(window.location.href, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.total > lastMessageCount) {
      // Show notification for new messages
      const notification = document.createElement('div');
      notification.className = 'alert alert-info alert-dismissible fade show position-fixed';
      notification.style.cssText = 'top: 70px; right: 20px; z-index: 1050; max-width: 300px;';
      notification.innerHTML = `
        <i class="bi bi-envelope me-2"></i>
        ${data.total - lastMessageCount} nuevo(s) mensaje(s) recibido(s)
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      document.body.appendChild(notification);
      
      lastMessageCount = data.total;
      
      // Auto-remove notification after 5 seconds
      setTimeout(() => {
        if (notification.parentNode) {
          notification.remove();
        }
      }, 5000);
    }
  })
  .catch(error => console.log('Error checking for new messages:', error));
}

// Check for new messages every 30 seconds
setInterval(checkForNewMessages, 30000);
</script>
@endpush
@endsection