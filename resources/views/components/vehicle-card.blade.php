<div class="car-card">
    <!-- Image Section -->
    <div class="car-image">
        @if($vehicle->primaryImage)
            <img src="{{ $vehicle->primaryImage->image_url }}" 
                 alt="{{ $vehicle->title }}" 
                 class="car-img">
        @else
            <div class="car-placeholder">🚗</div>
        @endif
    </div>

    <!-- Content Section -->
    <div class="car-content">
        <!-- Title -->
        <div class="car-title">
            <h3 class="car-brand">{{ $vehicle->brand }}</h3>
            <h4 class="car-model">{{ $vehicle->model }}</h4>
        </div>
        
        <!-- Basic Info -->
        <div class="car-info">
            <span class="car-year">{{ $vehicle->year }}</span>
            <span class="car-separator">•</span>
            <span class="car-mileage">{{ $vehicle->formatted_mileage }} km</span>
        </div>
        
        <!-- Specs -->
        <div class="car-specs">
            @if($vehicle->engine_size)
                <span class="spec">{{ $vehicle->engine_size }}L</span>
            @endif
            <span class="spec">{{ $vehicle->fuel_type }}</span>
            <span class="spec">{{ $vehicle->transmission ?? 'Manual' }}</span>
        </div>
        
        <!-- Price -->
        <div class="car-price">
            <span class="price">{{ $vehicle->formatted_price }}</span>
        </div>
        
        <!-- Button -->
        <div class="car-button">
            <a href="{{ route('vehicles.show', $vehicle->slug) }}" class="btn-details">
                Vezi Detalii
            </a>
        </div>
    </div>
</div> 