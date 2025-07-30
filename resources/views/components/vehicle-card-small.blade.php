<div class="tech-vehicle-card">
    <!-- Vehicle Image -->
    <div class="tech-vehicle-image">
        @if($vehicle->primaryImage)
            <img src="{{ $vehicle->primaryImage->image_url }}" 
                 alt="{{ $vehicle->title }}" 
                 class="w-full h-full object-cover">
        @else
            <div class="text-3xl">🚗</div>
        @endif
    </div>

    <!-- Vehicle Info -->
    <div class="tech-vehicle-content">
        <h3 class="tech-vehicle-title">
            {{ $vehicle->brand }} {{ $vehicle->model }}
        </h3>
        <p class="tech-vehicle-subtitle">
            {{ $vehicle->year }} • {{ $vehicle->formatted_mileage }} km
        </p>
        
        <div class="tech-vehicle-details">
            <span class="tech-vehicle-fuel">{{ $vehicle->fuel_type }}</span>
            <span class="tech-vehicle-transmission">{{ $vehicle->transmission ?? 'Manual' }}</span>
        </div>
        
        <div class="tech-vehicle-price">
            <span class="tech-price-amount">{{ $vehicle->formatted_price }}</span>
        </div>
        
        <a href="{{ route('vehicles.show', $vehicle->slug) }}" class="tech-vehicle-button">
            Vezi Detalii
        </a>
    </div>
</div> 