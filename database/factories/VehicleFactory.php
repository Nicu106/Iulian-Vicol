<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'brand' => $this->faker->randomElement(['BMW', 'Audi', 'Mercedes', 'Volkswagen', 'Toyota', 'Honda', 'Ford', 'Nissan']),
            'model' => $this->faker->word(),
            'year' => $this->faker->numberBetween(2015, 2024),
            'price' => $this->faker->numberBetween(15000, 80000),
            'mileage' => $this->faker->numberBetween(1000, 200000),
            'fuel_type' => $this->faker->randomElement(['Benzină', 'Diesel', 'Hibrid', 'Electric']),
            'transmission' => $this->faker->randomElement(['Manuală', 'Automată']),
            'body_type' => $this->faker->randomElement(['Sedan', 'SUV', 'Hatchback', 'Coupe', 'Convertible']),
            'color' => $this->faker->colorName(),
            'engine_capacity' => $this->faker->numberBetween(1000, 5000),
            'power' => $this->faker->numberBetween(100, 500),
            'description' => $this->faker->paragraph(3),
            'is_featured' => $this->faker->boolean(20),
            'offer_type' => $this->faker->randomElement(['Vânzare', 'Închiriere', 'Leasing']),
        ];
    }
}
