<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vehicle;

class CreateTestVehicle extends Command
{
    protected $signature = 'vehicle:create-test {--brand=Test} {--model=Demo} {--year=2022}';
    protected $description = 'Create a test vehicle quickly on the server';

    public function handle(): int
    {
        $slug = 'test-' . now()->format('YmdHis');
        $vehicle = Vehicle::create([
            'slug' => $slug,
            'brand' => (string) $this->option('brand'),
            'model' => (string) $this->option('model'),
            'year' => (int) $this->option('year'),
            'title' => ((string) $this->option('brand')) . ' ' . ((string) $this->option('model')) . ' ' . (string) $this->option('year'),
            'price' => 19999,
            'status' => 'available',
            'featured' => false,
            'mileage' => 12345,
            'fuel' => 'Gasolina',
            'transmission' => 'Manual',
            'color' => 'Negro',
            'description' => 'Vehículo de prueba creado automáticamente.',
            'cover_image' => 'https://picsum.photos/seed/' . $slug . '/900/600',
            'gallery_images' => [
                'https://picsum.photos/seed/' . $slug . 'a/900/600',
                'https://picsum.photos/seed/' . $slug . 'b/900/600',
            ],
        ]);

        $this->info('Created test vehicle: ' . $vehicle->slug);
        $this->info('URL: ' . route('vehicle.show', ['slug' => $vehicle->slug]));
        return Command::SUCCESS;
    }
}


