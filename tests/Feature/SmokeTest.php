<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_listings_renders(): void
    {
        $this->get('/vehicles')->assertStatus(200);
    }

    public function test_vehicle_detail_renders(): void
    {
        $vehicle = Vehicle::create([
            'title' => 'Test Car',
            'brand' => 'BrandX',
            'model' => 'ModelY',
            'year' => 2020,
            'price' => 10000,
            'mileage' => 50000,
            'fuel_type' => 'Benzină',
            'transmission' => 'Manuală',
            'engine_size' => '1.6L',
            'color' => 'Negru',
            'description' => 'Desc test',
            'is_featured' => false,
            'is_available' => true,
            'slug' => 'test-car'
        ]);

        $this->get('/vehicles/'.$vehicle->slug)->assertStatus(200);
    }

    public function test_contact_renders(): void
    {
        $this->get('/contact')->assertStatus(200);
    }
}


