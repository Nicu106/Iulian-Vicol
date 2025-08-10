@extends('layouts.app')
@section('title', 'Despre Auto Premium - Lideri în vânzarea de vehicule premium')
@push('styles')
  <link rel="stylesheet" href="{{ asset('css/pages/about.css') }}">
@endpush
@section('content')
<section class="py-5 about-page">
  <div class="container">
    <!-- Hero Section -->
    <div class="row align-items-center mb-5">
      <div class="col-lg-6">
        <h1 class="display-5 fw-bold mb-3">Auto Premium</h1>
        <p class="lead text-secondary mb-4">Lideri în vânzarea de vehicule premium din România, cu peste 4 ani de experiență și mii de clienți mulțumiți.</p>
        <div class="d-flex gap-3">
          <a href="{{ route('catalog') }}" class="btn btn-primary btn-lg">Vezi catalog</a>
          <a href="{{ route('contact') }}" class="btn btn-outline-primary btn-lg">Contactează-ne</a>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="ratio ratio-16x9 rounded overflow-hidden shadow">
          <img src="https://images.unsplash.com/photo-1562141961-994d8c19b560?q=80&w=1920&auto=format&fit=crop" 
               class="w-100 h-100 object-fit-cover" alt="Showroom Auto Premium" />
        </div>
      </div>
    </div>

    <!-- Statistici impressive -->
    <div class="row text-center mb-5 about-stats">
      <div class="col-6 col-md-3 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="display-4 fw-bold text-primary mb-2">2500+</div>
            <h6 class="text-secondary mb-0">Vehicule vândute</h6>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="display-4 fw-bold text-success mb-2">98%</div>
            <h6 class="text-secondary mb-0">Clienți mulțumiți</h6>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="display-4 fw-bold text-warning mb-2">15</div>
            <h6 class="text-secondary mb-0">Mărci premium</h6>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="display-4 fw-bold text-info mb-2">4+</div>
            <h6 class="text-secondary mb-0">Ani experiență</h6>
          </div>
        </div>
      </div>
    </div>

    <!-- Povestea noastră -->
    <div class="row mb-5">
      <div class="col-lg-8 mx-auto text-center">
        <h2 class="h3 mb-4">Povestea noastră</h2>
        <p class="lead text-secondary mb-4">
          Auto Premium s-a născut din pasiunea pentru vehiculele de calitate și dorința de a oferi clienților o experiență de cumpărare exceptională. 
          De la început, ne-am concentrat pe transparență, calitate și servicii de înaltă clasă.
        </p>
        <p class="text-secondary">
          Echipa noastră de experți verifică cu atenție fiecare vehicul, oferind garanții extinse și suport complet în procesul de cumpărare. 
          Credem că fiecare client merită să conducă vehiculul perfect pentru nevoile sale.
        </p>
      </div>
    </div>

    <!-- Roadmap ultimi 4 ani - Design îmbunătățit -->
    <div class="row mb-5 about-roadmap">
      <div class="col-12">
        <h2 class="h3 text-center mb-5">Evoluția noastră (2021-2024)</h2>
        <div class="row g-4">
          <!-- 2021 -->
          <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow h-100 position-relative overflow-hidden">
              <div class="card-body p-4 text-center">
                <div class="position-absolute top-0 end-0 bg-primary text-white px-3 py-1" style="border-radius: 0 0 0 15px;">
                  <small class="fw-bold">2021</small>
                </div>
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                  <i class="bi bi-rocket-takeoff text-primary fs-3"></i>
                </div>
                <h5 class="card-title text-primary mb-3">Începutul</h5>
                <p class="text-secondary mb-3">Lansarea Auto Premium cu primul showroom în București.</p>
                <div class="d-flex justify-content-center align-items-center">
                  <span class="badge bg-primary px-3 py-2">150 vehicule vândute</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 2022 -->
          <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow h-100 position-relative overflow-hidden">
              <div class="card-body p-4 text-center">
                <div class="position-absolute top-0 end-0 bg-success text-white px-3 py-1" style="border-radius: 0 0 0 15px;">
                  <small class="fw-bold">2022</small>
                </div>
                <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                  <i class="bi bi-graph-up-arrow text-success fs-3"></i>
                </div>
                <h5 class="card-title text-success mb-3">Expansiune</h5>
                <p class="text-secondary mb-3">Deschiderea celui de-al doilea showroom în Cluj-Napoca.</p>
                <div class="d-flex justify-content-center align-items-center">
                  <span class="badge bg-success px-3 py-2">500 vehicule vândute</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 2023 -->
          <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow h-100 position-relative overflow-hidden">
              <div class="card-body p-4 text-center">
                <div class="position-absolute top-0 end-0 bg-warning text-dark px-3 py-1" style="border-radius: 0 0 0 15px;">
                  <small class="fw-bold">2023</small>
                </div>
                <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                  <i class="bi bi-laptop text-warning fs-3"></i>
                </div>
                <h5 class="card-title text-warning mb-3">Digitalizare</h5>
                <p class="text-secondary mb-3">Lansarea platformei online și serviciilor digitale.</p>
                <div class="d-flex justify-content-center align-items-center">
                  <span class="badge bg-warning text-dark px-3 py-2">1000+ vehicule vândute</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 2024 -->
          <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow h-100 position-relative overflow-hidden">
              <div class="card-body p-4 text-center">
                <div class="position-absolute top-0 end-0 bg-info text-white px-3 py-1" style="border-radius: 0 0 0 15px;">
                  <small class="fw-bold">2024</small>
                </div>
                <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                  <i class="bi bi-trophy text-info fs-3"></i>
                </div>
                <h5 class="card-title text-info mb-3">Lideratul</h5>
                <p class="text-secondary mb-3">Poziția de lider pe piața vehiculelor premium.</p>
                <div class="d-flex justify-content-center align-items-center">
                  <span class="badge bg-info px-3 py-2">2500+ vehicule vândute</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Echipa / Fondatori -->
    <div class="row mb-5">
      <div class="col-12">
        <h2 class="h3 text-center mb-5">Echipa de conducere</h2>
        <div class="row g-4 justify-content-center">
          <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm text-center">
              <div class="card-body p-4">
                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-3" style="width: 80px; height: 80px;">
                  <i class="bi bi-person-fill fs-2"></i>
                </div>
                <h5 class="mb-2">Alexandru Popescu</h5>
                <p class="text-primary small mb-2">CEO & Co-Fondator</p>
                <p class="text-secondary small">15+ ani în industria auto, fost director comercial la BMW România. Pasionat de inovație și experiența clientului.</p>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm text-center">
              <div class="card-body p-4">
                <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-3" style="width: 80px; height: 80px;">
                  <i class="bi bi-person-fill fs-2"></i>
                </div>
                <h5 class="mb-2">Maria Ionescu</h5>
                <p class="text-success small mb-2">COO & Co-Fondator</p>
                <p class="text-secondary small">Expert în operațiuni și logistică auto, cu experiență la Porsche și Mercedes-Benz. Specializată în procese și calitate.</p>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm text-center">
              <div class="card-body p-4">
                <div class="bg-warning rounded-circle d-inline-flex align-items-center justify-content-center text-dark mb-3" style="width: 80px; height: 80px;">
                  <i class="bi bi-person-fill fs-2"></i>
                </div>
                <h5 class="mb-2">Andrei Marinescu</h5>
                <p class="text-warning small mb-2">CTO</p>
                <p class="text-secondary small">Tech lead cu 12+ ani experiență în automotive tech și e-commerce. Architect al platformei digitale Auto Premium.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Valorile noastre -->
    <div class="row mb-5">
      <div class="col-lg-8 mx-auto">
        <h2 class="h3 text-center mb-5">Valorile noastre</h2>
        <div class="row g-4">
          <div class="col-md-6">
            <div class="d-flex align-items-start">
              <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                <i class="bi bi-shield-check"></i>
              </div>
              <div>
                <h6 class="mb-2">Transparență totală</h6>
                <p class="text-secondary small mb-0">Toate informațiile despre vehicule sunt verificate și comunicate clar. Fără surprize neplăcute.</p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex align-items-start">
              <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                <i class="bi bi-award"></i>
              </div>
              <div>
                <h6 class="mb-2">Calitate premium</h6>
                <p class="text-secondary small mb-0">Selectăm doar vehiculele care îndeplinesc standardele noastre ridicate de calitate și performanță.</p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex align-items-start">
              <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                <i class="bi bi-people"></i>
              </div>
              <div>
                <h6 class="mb-2">Orientare către client</h6>
                <p class="text-secondary small mb-0">Fiecare client este unic. Oferim consiliere personalizată pentru a găsi vehiculul perfect.</p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex align-items-start">
              <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                <i class="bi bi-rocket"></i>
              </div>
              <div>
                <h6 class="mb-2">Inovație continuă</h6>
                <p class="text-secondary small mb-0">Investim constant în tehnologie și procese pentru a îmbunătăți experiența de cumpărare.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Call to action final -->
    <div class="row">
      <div class="col-lg-8 mx-auto text-center">
        <div class="card border-0 bg-light">
          <div class="card-body p-5">
            <h3 class="mb-3">Hai să găsim vehiculul perfect pentru tine</h3>
            <p class="text-secondary mb-4">Cu experiența noastră de 4+ ani și peste 2500 de vehicule vândute, suntem pregătiți să te ajutăm să faci alegerea potrivită.</p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
              <a href="{{ route('catalog') }}" class="btn btn-primary btn-lg">Explorează catalogul</a>
              <a href="{{ route('contact') }}" class="btn btn-outline-primary btn-lg">Vorbește cu un expert</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

