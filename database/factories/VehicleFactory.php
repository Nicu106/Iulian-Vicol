<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<\App\Models\Vehicle> */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        $brand = fake()->randomElement(['BMW','Audi','Mercedes','Volkswagen','Volvo','Toyota']);
        $model = fake()->randomElement(['X5','A4','C-Class','Tiguan','XC60','Corolla']);
        $year = fake()->numberBetween(2015, (int) date('Y'));
        $slug = Str::slug($brand.' '.$model.' '.$year.' '.Str::random(5));
        return [
            'slug' => $slug,
            'brand' => $brand,
            'model' => $model,
            'year' => $year,
            'price' => '€ '.fake()->numberBetween(9000, 69900),
            'mileage' => fake()->numberBetween(10000, 120000).' km',
            'fuel' => fake()->randomElement(['Benzină','Diesel','Hibrid','Electric']),
            'transmission' => fake()->randomElement(['Automată','Manuală']),
            'engine' => fake()->randomElement(['2.0L','3.0L','1.5L','2.5L']),
            'power' => fake()->numberBetween(100, 450).' CP',
            'drivetrain' => fake()->randomElement(['FWD','RWD','4x4']),
            'color' => fake()->safeColorName(),
            'vin' => Str::upper(Str::random(6)).'123456',
            'condition' => fake()->randomElement(['Excelentă','Foarte bună','Bună']),
            'description' => fake()->sentence(12),
            'features' => ['LED','Panoramic','Senzori parcare','Pachet sport'],
            'video_url' => null,
            'cover_image' => 'https://picsum.photos/seed/'.Str::random(6).'/800/500',
            'gallery_images' => [
                'https://picsum.photos/seed/'.Str::random(6).'/1200/800',
                'https://picsum.photos/seed/'.Str::random(6).'/1200/800',
                'https://picsum.photos/seed/'.Str::random(6).'/1200/800',
            ],
        ];
    }
}


