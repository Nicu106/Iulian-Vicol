@extends('layouts.app')

@section('title','APTECH Auto - Acasă')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/pages/home.css') }}">
  <link rel="preload" as="image" href="https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?q=80&w=1920&auto=format&fit=crop" imagesrcset="https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?q=80&w=1280&auto=format&fit=crop 1280w, https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?q=80&w=1920&auto=format&fit=crop 1920w" imagesizes="100vw">
@endpush

@section('content')
  <section class="home-hero text-light home-page" data-anim="reveal">
    <div class="container py-5">
      <div class="col-lg-8">
        <h1 class="display-4 fw-bold">Performanță în mișcare.</h1>
        <p class="lead mb-1">Tehnologie avansată, design inovativ.</p>
        <p class="mb-4">O colecție selectă de vehicule verificate, unde fiecare detaliu contează.</p>
        <form class="hero-search mb-3" action="{{ route('catalog') }}" method="get" role="search" aria-label="Caută marcă sau model">
          <div class="input-group input-group-lg">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" name="q" placeholder="Caută marcă sau model..." value="{{ request('q') }}">
            <button class="btn btn-primary">Caută</button>
          </div>
        </form>
        <div class="d-flex gap-2 flex-wrap hero-ctas">
          <a href="{{ route('catalog') }}" class="btn btn-primary btn-lg btn-cta">Vezi Catalogul</a>
          <a href="{{ route('contact') }}" class="btn btn-outline-light btn-lg">Cere o ofertă</a>
        </div>
        <ul class="list-inline small mt-3 hero-benefits">
          <li class="list-inline-item"><span class="badge bg-success-subtle text-success border">Verificat tehnic</span></li>
          <li class="list-inline-item"><span class="badge bg-primary-subtle text-primary border">Garanție</span></li>
          <li class="list-inline-item"><span class="badge bg-info-subtle text-dark border">Istoric clar</span></li>
        </ul>
      </div>
    </div>
  </section>

  <section class="py-5 bg-light values-section" data-anim="reveal">
    <div class="container text-center">
      <h2 class="fw-bold section-title">Valorile Noastre</h2>
      <p class="text-secondary mb-4 section-subtitle">Principiile care ne definesc și ne ghidează în fiecare zi</p>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card shadow-sm h-100 value-card">
            <div class="card-body">
              <div class="value-icon mb-3" aria-hidden="true">🛡️</div>
              <h5 class="card-title value-title">Transparență</h5>
              <p class="card-text small text-secondary value-text">Istoric complet și verificare tehnică detaliată pentru fiecare vehicul.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm h-100 value-card">
            <div class="card-body">
              <div class="value-icon mb-3" aria-hidden="true">⚡</div>
              <h5 class="card-title value-title">Calitate</h5>
              <p class="card-text small text-secondary value-text">Verificat cu atenție de tehnicieni specializați pentru calitate superioară.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm h-100 value-card">
            <div class="card-body">
              <div class="value-icon mb-3" aria-hidden="true">💙</div>
              <h5 class="card-title value-title">Încredere</h5>
              <p class="card-text small text-secondary value-text">Relații de încredere prin servicii de calitate și suport după achiziție.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="py-4 border-top border-bottom bg-white" data-anim="reveal">
    <div class="container">
      <div class="row text-center g-4 trust-row trust-badges" aria-label="Garanții și beneficii">
        <div class="col-6 col-md-3">
          <div class="trust-item">
            <div class="trust-icon" aria-hidden="true"><i class="bi bi-shield-check"></i></div>
            <div class="trust-label">Kilometri verificați</div>
            <div class="trust-value">Istoric transparent</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="trust-item">
            <div class="trust-icon" aria-hidden="true"><i class="bi bi-award"></i></div>
            <div class="trust-label">Garanție extinsă</div>
            <div class="trust-value">Până la 24 luni</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="trust-item">
            <div class="trust-icon" aria-hidden="true"><i class="bi bi-lightning-charge-fill"></i></div>
            <div class="trust-label">Finanțare rapidă</div>
            <div class="trust-value">Aprobare în 24h</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="trust-item">
            <div class="trust-icon" aria-hidden="true"><i class="bi bi-steering-wheel"></i></div>
            <div class="trust-label">Test drive</div>
            <div class="trust-value">Fără obligații</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5 category-section" data-anim="reveal">
    <div class="container">
      <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
          <h2 class="fw-bold mb-1">Alege-ți următoarea mașină</h2>
          <p class="text-secondary mb-0">Segmentate după cele mai căutate preferințe</p>
        </div>
        <a href="{{ route('catalog') }}" class="btn btn-outline-primary">Vezi toate</a>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 shadow-sm category-card">
            <img src="https://images.unsplash.com/photo-1511919884226-fd3cad34687c?q=80&w=1200&auto=format&fit=crop" class="card-img-top" alt="SUV pentru familie">
            <div class="card-body">
              <h5 class="card-title">SUV pentru familie</h5>
              <p class="card-text small text-secondary mb-3">Spațiu, siguranță și confort pentru drumuri lungi.</p>
              <a href="{{ route('catalog') }}" class="btn btn-primary category-cta">Vezi în catalog</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm category-card">
            <img src="https://images.unsplash.com/photo-1494976388531-d1058494cdd8?q=80&w=1200&auto=format&fit=crop" class="card-img-top" alt="Sedan premium">
            <div class="card-body">
              <h5 class="card-title">Sedan premium</h5>
              <p class="card-text small text-secondary mb-3">Eleganță și tehnologie pentru un condus rafinat.</p>
              <a href="{{ route('catalog') }}" class="btn btn-primary category-cta">Vezi în catalog</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm category-card">
            <img src="https://images.unsplash.com/photo-1483728642387-6c3bdd6c93e5?q=80&w=1200&auto=format&fit=crop" class="card-img-top" alt="Hibrid & electric">
            <div class="card-body">
              <h5 class="card-title">Hibrid & electric</h5>
              <p class="card-text small text-secondary mb-3">Eficiență și costuri reduse de utilizare.</p>
              <a href="{{ route('catalog') }}" class="btn btn-primary category-cta">Vezi în catalog</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5" data-anim="reveal">
    <div class="container text-center">
      <h2 class="fw-bold">Serviciile Noastre</h2>
      <p class="text-secondary mb-4">Oferim soluții complete pentru nevoile tale auto</p>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card shadow-sm h-100"><div class="card-body">
            <div class="display-6 mb-2">🔧</div>
            <h5 class="card-title">Service Tehnic</h5>
            <p class="card-text small text-secondary">Mentenanță și reparații de înaltă calitate.</p>
          </div></div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm h-100"><div class="card-body">
            <div class="display-6 mb-2">📋</div>
            <h5 class="card-title">Consultanță</h5>
            <p class="card-text small text-secondary">Sfaturi specializate pentru alegerea potrivită.</p>
          </div></div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm h-100"><div class="card-body">
            <div class="display-6 mb-2">🚗</div>
            <h5 class="card-title">Test Drive</h5>
            <p class="card-text small text-secondary">Încearcă vehiculul înainte de cumpărare.</p>
          </div></div>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5 bg-light" data-anim="reveal">
    <div class="container">
      <div class="row g-4 align-items-center steps">
        <div class="col-lg-5">
          <h2 class="fw-bold mb-3">Proces simplu în 4 pași</h2>
          <p class="text-secondary mb-4">De la selecție la livrare, echipa noastră te ghidează fără bătăi de cap.</p>
          <a href="{{ route('catalog') }}" class="btn btn-primary">Începe acum</a>
        </div>
        <div class="col-lg-7">
          <div class="row g-3">
            <div class="col-6">
              <div class="p-3 border rounded-3 h-100 bg-white">
                <div class="badge rounded-pill text-bg-primary mb-2">1</div>
                <div class="fw-semibold">Alege</div>
                <div class="small text-secondary">Caută în catalog modelul potrivit.</div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3 border rounded-3 h-100 bg-white">
                <div class="badge rounded-pill text-bg-primary mb-2">2</div>
                <div class="fw-semibold">Verifică</div>
                <div class="small text-secondary">Istoric clar și raport tehnic detaliat.</div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3 border rounded-3 h-100 bg-white">
                <div class="badge rounded-pill text-bg-primary mb-2">3</div>
                <div class="fw-semibold">Testează</div>
                <div class="small text-secondary">Programează un test drive fără obligații.</div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3 border rounded-3 h-100 bg-white">
                <div class="badge rounded-pill text-bg-primary mb-2">4</div>
                <div class="fw-semibold">Finalizează</div>
                <div class="small text-secondary">Finanțare și livrare rapidă.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5" data-anim="reveal">
    <div class="container">
      <div class="text-center mb-4">
        <h2 class="fw-bold">Părerile clienților</h2>
        <p class="text-secondary">Încredere construită prin experiențe reale</p>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <p class="mb-3">“Proces foarte clar și rapid. Mașina a fost exact cum a fost descrisă.”</p>
              <div class="small text-secondary">— Andrei P., București</div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <p class="mb-3">“Consultanță excelentă și finanțare aprobată în aceeași zi.”</p>
              <div class="small text-secondary">— Ioana D., Cluj</div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <p class="mb-3">“Transparență totală la istoricul mașinii. Recomand 100%.”</p>
              <div class="small text-secondary">— Mihai S., Iași</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5 bg-light" data-anim="reveal">
    <div class="container">
      <div class="row g-4 align-items-start">
        <div class="col-lg-6">
          <h2 class="fw-bold mb-3">Întrebări frecvente</h2>
          <p class="text-secondary mb-0">Răspundem la cele mai comune întrebări ca să decizi informat.</p>
        </div>
        <div class="col-lg-6">
          <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
              <h2 class="accordion-header" id="faqOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseOne" aria-expanded="false" aria-controls="faqCollapseOne">
                  Oferiți garanție pentru vehicule?
                </button>
              </h2>
              <div id="faqCollapseOne" class="accordion-collapse collapse" aria-labelledby="faqOne" data-bs-parent="#faqAccordion">
                <div class="accordion-body small text-secondary">Da, oferim garanție extinsă până la 24 de luni, în funcție de model.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="faqTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseTwo" aria-expanded="false" aria-controls="faqCollapseTwo">
                  Pot cumpăra în leasing sau cu finanțare?
                </button>
              </h2>
              <div id="faqCollapseTwo" class="accordion-collapse collapse" aria-labelledby="faqTwo" data-bs-parent="#faqAccordion">
                <div class="accordion-body small text-secondary">Da, colaborăm cu parteneri financiari pentru oferte rapide și avantajoase.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="faqThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseThree" aria-expanded="false" aria-controls="faqCollapseThree">
                  Pot programa un test drive?
                </button>
              </h2>
              <div id="faqCollapseThree" class="accordion-collapse collapse" aria-labelledby="faqThree" data-bs-parent="#faqAccordion">
                <div class="accordion-body small text-secondary">Sigur. Completează formularul de contact sau sună-ne pentru programare.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="final-cta text-light py-5" data-anim="reveal">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-8">
          <h2 class="fw-bold mb-2">Găsește-ți mașina potrivită azi</h2>
          <p class="mb-0 text-light-emphasis">Stoc actualizat, verificări complete și oferte flexibile de finanțare.</p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
          <a href="{{ route('catalog') }}" class="btn btn-primary btn-lg">Intră în catalog</a>
        </div>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script src="{{ asset('js/pages/home.js') }}" defer></script>
@endpush

