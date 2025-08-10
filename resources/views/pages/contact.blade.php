@extends('layouts.app')
@section('title', 'Contact - Auto Premium | Contactează echipa noastră')
@push('styles')
  <link rel="stylesheet" href="{{ asset('css/pages/contact.css') }}">
@endpush
@section('content')
<section class="py-5 contact-page">
  <div class="container">
    <!-- Header Section -->
    <div class="row mb-5">
      <div class="col-lg-8 mx-auto text-center">
        <h1 class="display-5 fw-bold mb-3">Contactează-ne</h1>
        <p class="lead text-secondary">Suntem aici să te ajutăm să găsești vehiculul perfect. Echipa noastră de experți îți stă la dispoziție pentru orice întrebare.</p>
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
                <h4 class="card-title mb-4">Informații de contact</h4>
                <div class="d-flex align-items-center mb-3">
                  <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="bi bi-telephone"></i>
                  </div>
                  <div>
                    <h6 class="mb-1">Telefon</h6>
                    <a href="tel:+40123456789" class="text-decoration-none text-primary fw-semibold">+40 123 456 789</a>
                  </div>
                </div>
                <div class="d-flex align-items-center mb-3">
                  <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="bi bi-whatsapp"></i>
                  </div>
                  <div>
                    <h6 class="mb-1">WhatsApp</h6>
                    <a href="https://wa.me/40123456789" target="_blank" class="text-decoration-none text-success fw-semibold">Trimite mesaj</a>
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
                    <h6 class="mb-1">Adresă</h6>
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
                <h5 class="card-title mb-3">Program de lucru</h5>
                <div class="row g-3 small">
                  <div class="col-6">
                    <div class="d-flex justify-content-between">
                      <span class="text-secondary">Luni-Vineri:</span>
                      <span class="fw-semibold">09:00 - 18:00</span>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="d-flex justify-content-between">
                      <span class="text-secondary">Sâmbătă:</span>
                      <span class="fw-semibold">10:00 - 16:00</span>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="d-flex justify-content-between">
                      <span class="text-secondary">Duminică:</span>
                      <span class="text-warning fw-semibold">Închis</span>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="d-flex justify-content-between">
                      <span class="text-secondary">Urgențe:</span>
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
                <h6 class="mb-3">Acțiuni rapide</h6>
                <div class="d-grid gap-2">
                  <a href="tel:+40123456789" class="btn btn-primary">
                    <i class="bi bi-telephone me-2"></i>Sună acum
                  </a>
                  <a href="{{ route('catalog') }}" class="btn btn-outline-primary">
                    <i class="bi bi-grid-3x3-gap me-2"></i>Vezi catalogul
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
            <h4 class="card-title mb-4">Trimite-ne un mesaj</h4>
            <p class="text-secondary mb-4">Completează formularul de mai jos și îți vom răspunde în cel mai scurt timp posibil.</p>
            
            <form method="post" action="#" novalidate>
              @csrf
              <div class="row g-4">
                <div class="col-md-6">
                  <label for="name" class="form-label">Nume complet <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="name" name="name" required>
                  <div class="invalid-feedback">Vă rugăm să introduceți numele complet.</div>
                </div>
                <div class="col-md-6">
                  <label for="phone" class="form-label">Număr de telefon <span class="text-danger">*</span></label>
                  <input type="tel" class="form-control" id="phone" name="phone" required>
                  <div class="invalid-feedback">Vă rugăm să introduceți un număr de telefon valid.</div>
                </div>
                <div class="col-12">
                  <label for="email" class="form-label">Adresă email</label>
                  <input type="email" class="form-control" id="email" name="email">
                  <div class="form-text">Opțional - pentru confirmarea mesajului</div>
                </div>
                <div class="col-12">
                  <label for="subject" class="form-label">Subiect</label>
                  <select class="form-select" id="subject" name="subject">
                    <option value="">Alege un subiect...</option>
                    <option value="informazioni">Informații generale</option>
                    <option value="test-drive">Programare test drive</option>
                    <option value="oferta">Solicitare ofertă</option>
                    <option value="finantare">Opțiuni de finanțare</option>
                    <option value="garantie">Garanție și service</option>
                    <option value="altele">Altele</option>
                  </select>
                </div>
                <div class="col-12">
                  <label for="message" class="form-label">Mesajul tău <span class="text-danger">*</span></label>
                  <textarea class="form-control" id="message" name="message" rows="5" required placeholder="Descrie-ne în detaliu cerința ta..."></textarea>
                  <div class="invalid-feedback">Vă rugăm să introduceți un mesaj.</div>
                </div>
                <div class="col-12">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="gdpr" name="gdpr" required>
                    <label class="form-check-label" for="gdpr">
                      Sunt de acord cu <a href="#" class="text-decoration-none">prelucrarea datelor personale</a> conform GDPR <span class="text-danger">*</span>
                    </label>
                    <div class="invalid-feedback">Trebuie să fiți de acord cu prelucrarea datelor.</div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                    <label class="form-check-label" for="newsletter">
                      Vreau să primesc oferte și noutăți prin email
                    </label>
                  </div>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-send me-2"></i>Trimite mesajul
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Informații suplimentare -->
    <div class="row mt-5">
      <div class="col-12">
        <div class="card border-0 bg-light">
          <div class="card-body p-5 text-center">
            <h4 class="mb-3">De ce să ne alegi pe noi?</h4>
            <div class="row g-4">
              <div class="col-md-3">
                <i class="bi bi-shield-check text-primary fs-1"></i>
                <h6 class="mt-2">Vehicule verificate</h6>
                <p class="text-secondary small mb-0">Toate mașinile sunt inspectate și certificate</p>
              </div>
              <div class="col-md-3">
                <i class="bi bi-award text-success fs-1"></i>
                <h6 class="mt-2">Garanție extinsă</h6>
                <p class="text-secondary small mb-0">Oferim garanție de până la 24 luni</p>
              </div>
              <div class="col-md-3">
                <i class="bi bi-credit-card text-info fs-1"></i>
                <h6 class="mt-2">Finanțare flexibilă</h6>
                <p class="text-secondary small mb-0">Opțiuni de plată personalizate</p>
              </div>
              <div class="col-md-3">
                <i class="bi bi-headset text-warning fs-1"></i>
                <h6 class="mt-2">Suport 24/7</h6>
                <p class="text-secondary small mb-0">Echipa noastră te ajută oricând</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

