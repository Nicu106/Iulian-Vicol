<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;

class OneTestVehicleSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'test-demo-' . date('YmdHis');

        $exists = Vehicle::query()->where('slug', $slug)->exists();
        if ($exists) {
            $slug .= '-dup';
        }

        Vehicle::create([
            'slug' => $slug,
            'brand' => 'Test',
            'model' => 'Demo',
            'year' => 2022,
            'title' => 'Test Demo 2022',
            'price' => 19999,
            'status' => 'available',
            'featured' => false,
            'mileage' => 12345,
            'fuel' => 'Gasolina',
            'transmission' => 'Manual',
            'color' => 'Negro',
            'description' => 'Vehículo de prueba creado automáticamente para validación.',
            'cover_image' => 'https://picsum.photos/seed/' . $slug . '/900/600',
            'gallery_images' => [
                'https://picsum.photos/seed/' . $slug . 'a/900/600',
                'https://picsum.photos/seed/' . $slug . 'b/900/600',
            ],
        ]);

        $this->command?->info('Created test vehicle with slug: ' . $slug);
    }
}


