@extends('layouts.app')
@section('title', $vehicle->title ?? ($vehicle->brand . ' ' . $vehicle->model . ' ' . $vehicle->year))

@php
  $extractPrice = function($priceStr) {
      if (is_numeric($priceStr)) return (float) $priceStr;
      preg_match_all('/\d+/', $priceStr ?? '0', $matches);
      return $matches[0] ? (float) implode('', $matches[0]) : 0;
  };
  
  $extractMileage = function($mileageStr) {
      if (is_numeric($mileageStr)) return (float) $mileageStr;
      preg_match_all('/\d+/', $mileageStr ?? '0', $matches);
      return $matches[0] ? (float) implode('', $matches[0]) : 0;
  };
  
  $title = $vehicle->title ?? ($vehicle->brand . ' ' . $vehicle->model . ' ' . $vehicle->year);
  $displayPrice = $vehicle->has_active_offer ? $vehicle->offer_price : $vehicle->price;
  $originalPrice = $vehicle->has_active_offer ? $vehicle->price : null;
@endphp

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/pages/vehicle.css') }}">
@endpush
@section('content')
<section class="pt-5 vehicle-page">
  <div class="container py-4">
    <div class="row g-4 align-items-start mb-3">
      <div class="col-lg-8 vehicle-gallery">
        <nav class="small text-secondary mb-2" aria-label="breadcrumb">
          <a href="{{ route('home') }}" class="text-decoration-none">Acasă</a>
          <span class="mx-1">›</span>
          <a href="{{ route('catalog') }}" class="text-decoration-none">Catalog</a>
          <span class="mx-1">›</span>
          <a href="{{ route('catalog') }}?brand={{ $vehicle->brand }}" class="text-decoration-none">{{ $vehicle->brand }}</a>
          <span class="mx-1">›</span>
          <span class="text-dark">{{ $vehicle->model }}</span>
        </nav>
        <h1 class="h3 fw-bold mb-2">{{ $title }}</h1>
        <div class="d-flex align-items-center gap-3 flex-wrap">
          @if($vehicle->has_active_offer)
            <div class="d-flex align-items-center gap-2">
              <span class="text-decoration-line-through text-muted h5">€ {{ number_format($extractPrice($originalPrice)) }}</span>
              <div class="display-6 text-danger fw-bold">€ {{ number_format($extractPrice($displayPrice)) }}</div>
              <span class="badge bg-danger">OFERTĂ</span>
            </div>
          @else
            <div class="display-6 text-primary fw-bold">€ {{ number_format($extractPrice($displayPrice)) }}</div>
          @endif
          
          @if($vehicle->featured)
            <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Recomandat</span>
          @endif
          
          @foreach($vehicle->badges ?? [] as $badge)
            <span class="badge bg-info text-dark">{{ $badge }}</span>
          @endforeach
          
          <span class="badge bg-success">Finanțare disponibilă</span>
        </div>
      </div>
      <div class="col-lg-4 d-grid gap-2 gap-lg-2">
        <a href="tel:+40123456789" class="btn btn-primary btn-lg"><i class="bi bi-telephone me-2"></i>Sună acum</a>
        <a href="https://wa.me/40123456789" class="btn btn-outline-success btn-lg"><i class="bi bi-whatsapp me-2"></i>WhatsApp</a>
        <a href="#testdrive" class="btn btn-outline-primary btn-lg"><i class="bi bi-calendar2-check me-2"></i>Programează test drive</a>
        <a href="#rezerva" class="btn btn-warning btn-lg text-dark"><i class="bi bi-bookmark-check me-2"></i>Rezervă vehiculul</a>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-lg-8">
        @if($vehicle->cover_image || !empty($vehicle->gallery_images))
          @php
            $allImages = [];
            if($vehicle->cover_image) $allImages[] = $vehicle->cover_image;
            if($vehicle->gallery_images) $allImages = array_merge($allImages, $vehicle->gallery_images);
            $mainImage = $allImages[0] ?? 'https://via.placeholder.com/800x450/f8f9fa/6c757d?text=Fără+imagine';
            $thumbnails = array_slice($allImages, 1, 5); // Max 5 thumbnails
          @endphp
          
          <div class="ratio ratio-16x9 rounded overflow-hidden">
            <img src="{{ $mainImage }}" class="w-100 h-100 object-fit-cover" alt="{{ $title }}" 
                 onerror="this.src='https://via.placeholder.com/800x450/f8f9fa/6c757d?text=Fără+imagine'" />
          </div>
          
          @if(count($thumbnails) > 0)
            <div class="row g-2 mt-2">
              @foreach($thumbnails as $img)
              <div class="col-4">
                <div class="ratio ratio-16x9 rounded overflow-hidden">
                  <img src="{{ $img }}" class="w-100 h-100 object-fit-cover cursor-pointer" alt="{{ $title }}" loading="lazy"
                       onerror="this.src='https://via.placeholder.com/300x200/f8f9fa/6c757d?text=Fără+imagine'"
                       onclick="document.querySelector('.vehicle-gallery .ratio img').src = this.src" />
                </div>
              </div>
              @endforeach
            </div>
          @endif
        @else
          <div class="ratio ratio-16x9 rounded overflow-hidden bg-light d-flex align-items-center justify-content-center">
            <div class="text-center text-muted">
              <i class="bi bi-car-front" style="font-size: 4rem;"></i>
              <p class="mt-2">Fără imagini disponibile</p>
            </div>
          </div>
        @endif
      </div>
      <div class="col-lg-4 vehicle-specs">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Specificații rapide</h5>
            <ul class="list-unstyled small mb-0">
              <li class="mb-2"><strong>An:</strong> {{ $vehicle->year ?? 'N/A' }}</li>
              <li class="mb-2"><strong>Kilometraj:</strong> {{ $vehicle->mileage ? number_format($extractMileage($vehicle->mileage)) . ' km' : 'N/A' }}</li>
              <li class="mb-2"><strong>Combustibil:</strong> {{ $vehicle->fuel ?? 'N/A' }}</li>
              <li class="mb-2"><strong>Transmisie:</strong> {{ $vehicle->transmission ?? 'N/A' }}</li>
              @if($vehicle->power)
                <li class="mb-2"><strong>Putere:</strong> {{ $vehicle->power }}</li>
              @endif
              @if($vehicle->drivetrain)
                <li class="mb-2"><strong>Tracțiune:</strong> {{ $vehicle->drivetrain }}</li>
              @endif
              @if($vehicle->engine)
                <li class="mb-2"><strong>Motor:</strong> {{ $vehicle->engine }}</li>
              @endif
              @if($vehicle->color)
                <li class="mb-2"><strong>Culoare:</strong> {{ $vehicle->color }}</li>
              @endif
              @if($vehicle->body_type)
                <li class="mb-2"><strong>Caroserie:</strong> {{ $vehicle->body_type }}</li>
              @endif
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        <h4>Descriere</h4>
        <p class="text-secondary">{{ $vehicle->description ?? 'Descriere nu este disponibilă pentru acest vehicul.' }}</p>

        <h4 class="mt-4">Specificații Tehnice</h4>
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <tbody>
              <tr><th class="w-25">Marcă</th><td>{{ $vehicle->brand ?? 'N/A' }}</td><th>Transmisie</th><td>{{ $vehicle->transmission ?? 'N/A' }}</td></tr>
              <tr><th>Model</th><td>{{ $vehicle->model ?? 'N/A' }}</td><th>Motor</th><td>{{ $vehicle->engine ?? 'N/A' }}</td></tr>
              <tr><th>An</th><td>{{ $vehicle->year ?? 'N/A' }}</td><th>Culoare</th><td>{{ $vehicle->color ?? 'N/A' }}</td></tr>
              <tr><th>Kilometri</th><td>{{ $vehicle->mileage ? number_format($extractMileage($vehicle->mileage)) . ' km' : 'N/A' }}</td><th>Stare</th><td>{{ $vehicle->condition ?? 'N/A' }}</td></tr>
              <tr><th>Combustibil</th><td>{{ $vehicle->fuel ?? 'N/A' }}</td><th>VIN</th><td>@if($vehicle->vin)<code>{{ substr($vehicle->vin,0,6) }}••••••</code>@else N/A @endif</td></tr>
              @if($vehicle->power || $vehicle->drivetrain)
                <tr>
                  @if($vehicle->power)<th>Putere</th><td>{{ $vehicle->power }}</td>@else<th></th><td></td>@endif
                  @if($vehicle->drivetrain)<th>Tracțiune</th><td>{{ $vehicle->drivetrain }}</td>@else<th></th><td></td>@endif
                </tr>
              @endif
              @if($vehicle->body_type || $vehicle->location)
                <tr>
                  @if($vehicle->body_type)<th>Caroserie</th><td>{{ $vehicle->body_type }}</td>@else<th></th><td></td>@endif
                  @if($vehicle->location)<th>Locație</th><td>{{ $vehicle->location }}</td>@else<th></th><td></td>@endif
                </tr>
              @endif
            </tbody>
          </table>
        </div>

        @if(!empty($vehicle->features))
          <h4 class="mt-4">Dotări</h4>
          <div class="row g-2">
            @foreach($vehicle->features as $feature)
              <div class="col-6 col-md-4"><span class="badge bg-light text-dark border">✓ {{ $feature }}</span></div>
            @endforeach
          </div>
        @endif

        @if(!empty($vehicle->tags))
          <h4 class="mt-4">Etichete</h4>
          <div class="d-flex flex-wrap gap-1">
            @foreach($vehicle->tags as $tag)
              <span class="badge bg-secondary">{{ $tag }}</span>
            @endforeach
          </div>
        @endif
        
        @if($vehicle->video_url)
          <h4 class="mt-4">Video prezentare</h4>
          <div class="ratio ratio-16x9">
            <iframe src="{{ $vehicle->video_url }}" allowfullscreen></iframe>
          </div>
        @endif
      </div>
      <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
          <div class="card-body">
            <h5 class="card-title">Solicită ofertă / test drive</h5>
            <form>
              <div class="mb-2"><input type="text" class="form-control" placeholder="Nume" required></div>
              <div class="mb-2"><input type="tel" class="form-control" placeholder="Telefon" required></div>
              <div class="mb-2"><textarea class="form-control" rows="3" placeholder="Mesaj" required></textarea></div>
              <button class="btn btn-primary w-100" type="submit">Trimite</button>
            </form>
          </div>
        </div>
        <div class="card shadow-sm">
          <div class="card-body">
            <h6 class="mb-2">Calculator finanțare (estimativ)</h6>
            <div class="row g-2 align-items-end">
              <div class="col-6">
                <label class="form-label small">Avans (€)</label>
                <input type="number" class="form-control" value="5000" min="0">
              </div>
              <div class="col-6">
                <label class="form-label small">Luni</label>
                <input type="number" class="form-control" value="60" min="12" step="6">
              </div>
              <div class="col-12"><button class="btn btn-outline-primary w-100" type="button">Află oferta</button></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    @if($similarVehicles && $similarVehicles->count() > 0)
      <h4 class="mt-5">Vehicule similare</h4>
      <div class="row g-4">
        @foreach($similarVehicles as $similar)
        <div class="col-12 col-sm-6 col-lg-3">
          <div class="card h-100 shadow-sm">
            @if($similar->cover_image)
              <img src="{{ $similar->cover_image }}" class="card-img-top" alt="{{ $similar->title ?? ($similar->brand . ' ' . $similar->model) }}" 
                   style="height: 200px; object-fit: cover;"
                   onerror="this.src='https://via.placeholder.com/400x200/f8f9fa/6c757d?text=Fără+imagine'" />
            @else
              <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                <i class="bi bi-car-front text-muted" style="font-size: 2rem;"></i>
              </div>
            @endif
            <div class="card-body d-flex flex-column">
              @if($similar->featured)
                <div class="mb-1">
                  <span class="badge bg-warning text-dark">
                    <i class="bi bi-star-fill"></i> Recomandat
                  </span>
                </div>
              @endif
              
              <h6 class="card-title mb-1">{{ $similar->title ?? ($similar->brand . ' ' . $similar->model . ' ' . $similar->year) }}</h6>
              <div class="small text-secondary mb-2">
                {{ $similar->fuel ?? 'N/A' }} • {{ $similar->mileage ? number_format($extractMileage($similar->mileage)) . ' km' : 'N/A' }} • {{ $similar->transmission ?? 'N/A' }}
              </div>
              
              <div class="mt-auto d-flex justify-content-between align-items-center">
                @if($similar->has_active_offer)
                  <div>
                    <div class="small text-decoration-line-through text-muted">€ {{ number_format($extractPrice($similar->price)) }}</div>
                    <span class="fw-bold text-danger">€ {{ number_format($extractPrice($similar->offer_price)) }}</span>
                  </div>
                @else
                  <span class="fw-bold text-primary">€ {{ number_format($extractPrice($similar->price)) }}</span>
                @endif
                <a href="{{ route('vehicle.show', $similar->slug) }}" class="btn btn-sm btn-primary">Detalii</a>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    @endif
  </div>
</section>
@endsection

