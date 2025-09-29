@extends('layouts.app')
@section('title', 'Contacto - Auto Premium | Contacta con nuestro equipo')
@push('styles')
  <link rel="stylesheet" href="{{ asset('css/pages/contact.css') }}">
@endpush
@section('content')
<section class="py-5 contact-page">
  <div class="container">
    <!-- Header Section -->
    <div class="row mb-5">
      <div class="col-lg-8 mx-auto text-center">
        <h1 class="display-5 fw-bold mb-3">Contáctanos</h1>
        <p class="lead text-secondary">Estamos aquí para ayudarte a encontrar el vehículo perfecto. Nuestro equipo de expertos está a tu disposición para cualquier pregunta.</p>
      </div>
    </div>

    <div class="row g-5 align-items-start">
      <!-- Contact Info -->
      <div class="col-lg-5">
        <div class="row g-4">
          <!-- Informații de contact principale -->
          <div class="col-12">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body p-4">
                <h4 class="card-title mb-4">Información de contacto</h4>
                <div class="d-flex align-items-center mb-3">
                  <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="bi bi-telephone"></i>
                  </div>
                  <div>
                    <h6 class="mb-1">Teléfono</h6>
                    <a href="tel:+40123456789" class="text-decoration-none text-primary fw-semibold">+40 123 456 789</a>
                  </div>
                </div>
                <div class="d-flex align-items-center mb-3">
                  <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="bi bi-whatsapp"></i>
                  </div>
                  <div>
                    <h6 class="mb-1">WhatsApp</h6>
                    <a href="https://wa.me/40123456789" target="_blank" class="text-decoration-none text-success fw-semibold">Enviar mensaje</a>
                  </div>
                </div>
                <div class="d-flex align-items-center mb-3">
                  <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="bi bi-envelope"></i>
                  </div>
                  <div>
                    <h6 class="mb-1">Email</h6>
                    <a href="mailto:contact@autopremium.ro" class="text-decoration-none text-info fw-semibold">contact@autopremium.ro</a>
                  </div>
                </div>
                <div class="d-flex align-items-center">
                  <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="bi bi-geo-alt"></i>
                  </div>
                  <div>
                    <h6 class="mb-1">Dirección</h6>
                    <p class="text-secondary mb-0">Șoseaua Kiseleff nr. 15<br/>Bucuresti, Sector 1, 011347</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Program de lucru -->
          <div class="col-12">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body p-4">
                <h5 class="card-title mb-3">Horario de trabajo</h5>
                <div class="row g-3 small">
                  <div class="col-6">
                    <div class="d-flex justify-content-between">
                      <span class="text-secondary">Lunes-Viernes:</span>
                      <span class="fw-semibold">09:00 - 18:00</span>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="d-flex justify-content-between">
                      <span class="text-secondary">Sábado:</span>
                      <span class="fw-semibold">10:00 - 16:00</span>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="d-flex justify-content-between">
                      <span class="text-secondary">Domingo:</span>
                      <span class="text-warning fw-semibold">Cerrado</span>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="d-flex justify-content-between">
                      <span class="text-secondary">Emergencias:</span>
                      <span class="text-success fw-semibold">24/7</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Acțiuni rapide -->
          <div class="col-12 contact-quick">
            <div class="card border-0 bg-light">
              <div class="card-body p-4 text-center">
                <h6 class="mb-3">Acciones rápidas</h6>
                <div class="d-grid gap-2">
                  <a href="tel:+40123456789" class="btn btn-primary">
                    <i class="bi bi-telephone me-2"></i>Llama ahora
                  </a>
                  <a href="{{ route('catalog') }}" class="btn btn-outline-primary">
                    <i class="bi bi-grid-3x3-gap me-2"></i>Ver catálogo
                  </a>
                  <a href="https://wa.me/40123456789?text=Salut!%20Sunt%20interesat%20de%20vehiculele%20dumneavoastră." target="_blank" class="btn btn-outline-success">
                    <i class="bi bi-whatsapp me-2"></i>WhatsApp
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Form -->
      <div class="col-lg-7">
        <div class="card border-0 shadow">
          <div class="card-body p-5">
            <h4 class="card-title mb-4">Envíanos un mensaje</h4>
            <p class="text-secondary mb-4">Completa el formulario de abajo y te responderemos en el menor tiempo posible.</p>
            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif
            
            <form method="post" action="{{ route('contact.send') }}" novalidate>
              @csrf
              <div class="row g-4">
                <div class="col-md-6">
                  <label for="name" class="form-label">Nombre completo <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                  <div class="invalid-feedback">Por favor, introduce tu nombre completo.</div>
                </div>
                <div class="col-md-6">
                  <label for="phone" class="form-label">Número de teléfono <span class="text-danger">*</span></label>
                  <input type="tel" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" required>
                  <div class="invalid-feedback">Por favor, introduce un número de teléfono válido.</div>
                </div>
                <div class="col-12">
                  <label for="email" class="form-label">Dirección de email</label>
                  <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                  <div class="form-text">Opcional - para confirmación del mensaje</div>
                </div>
                <div class="col-12">
                  <label for="subject" class="form-label">Asunto</label>
                  <select class="form-select" id="subject" name="subject">
                    <option value="">Elige un asunto...</option>
                    <option value="informazioni" {{ old('subject')=='informazioni' ? 'selected' : '' }}>Información general</option>
                    <option value="test-drive" {{ old('subject')=='test-drive' ? 'selected' : '' }}>Programar prueba de manejo</option>
                    <option value="oferta" {{ old('subject')=='oferta' ? 'selected' : '' }}>Solicitar oferta</option>
                    <option value="finantare" {{ old('subject')=='finantare' ? 'selected' : '' }}>Opciones de financiación</option>
                    <option value="garantie" {{ old('subject')=='garantie' ? 'selected' : '' }}>Garantía y servicio</option>
                    <option value="altele" {{ old('subject')=='altele' ? 'selected' : '' }}>Otros</option>
                  </select>
                </div>
                <div class="col-12">
                  <label for="message" class="form-label">Tu mensaje <span class="text-danger">*</span></label>
                  <textarea class="form-control" id="message" name="message" rows="5" required placeholder="Descríbenos en detalle tu solicitud...">{{ old('message') }}</textarea>
                  <div class="invalid-feedback">Por favor, introduce un mensaje.</div>
                </div>
                <div class="col-12">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="gdpr" name="gdpr" {{ old('gdpr') ? 'checked' : '' }} required>
                    <label class="form-check-label" for="gdpr">
                      Estoy de acuerdo con el <a href="#" class="text-decoration-none">procesamiento de datos personales</a> según GDPR <span class="text-danger">*</span>
                    </label>
                    <div class="invalid-feedback">Debes estar de acuerdo con el procesamiento de datos.</div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-check">
                    <input type="hidden" name="newsletter" value="0">
                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter" value="1" {{ old('newsletter') ? 'checked' : '' }}>
                    <label class="form-check-label" for="newsletter">
                      Quiero recibir ofertas y novedades por email
                    </label>
                  </div>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-send me-2"></i>Enviar mensaje
                  </button>
                </div>
              </div>
            </form>
            @if(session('status'))
              <div class="alert alert-success mt-3">{{ session('status') }}</div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Informații suplimentare -->
    <div class="row mt-5">
      <div class="col-12">
        <div class="card border-0 bg-light">
          <div class="card-body p-5 text-center">
            <h4 class="mb-3">¿Por qué elegirnos?</h4>
            <div class="row g-4">
              <div class="col-md-3">
                <i class="bi bi-shield-check text-primary fs-1"></i>
                <h6 class="mt-2">Vehículos verificados</h6>
                <p class="text-secondary small mb-0">Todos los coches están inspeccionados y certificados</p>
              </div>
              <div class="col-md-3">
                <i class="bi bi-award text-success fs-1"></i>
                <h6 class="mt-2">Garantía extendida</h6>
                <p class="text-secondary small mb-0">Ofrecemos garantía de hasta 24 meses</p>
              </div>
              <div class="col-md-3">
                <i class="bi bi-credit-card text-info fs-1"></i>
                <h6 class="mt-2">Financiación flexible</h6>
                <p class="text-secondary small mb-0">Opciones de pago personalizadas</p>
              </div>
              <div class="col-md-3">
                <i class="bi bi-headset text-warning fs-1"></i>
                <h6 class="mt-2">Soporte 24/7</h6>
                <p class="text-secondary small mb-0">Nuestro equipo te ayuda en cualquier momento</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  var success = @json(session('status'));
  if (success) {
    var modalHtml = `
    <div class="modal fade" id="contactSuccessModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h5 class="modal-title text-success"><i class="bi bi-check-circle me-2"></i>Mensaje enviado</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            {{ session('status') }}
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Ok</button>
          </div>
        </div>
      </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    var modal = new bootstrap.Modal(document.getElementById('contactSuccessModal'));
    modal.show();
  }
});
</script>
@endpush

