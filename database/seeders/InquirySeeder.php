<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inquiry;
use App\Models\Vehicle;
use Faker\Factory as FakerFactory;

class InquirySeeder extends Seeder
{
    public function run(): void
    {
        $faker = FakerFactory::create('ro_RO');
        $vehicles = Vehicle::query()->inRandomOrder()->limit(10)->get();

        foreach ($vehicles as $vehicle) {
            for ($i = 0; $i < 2; $i++) {
                Inquiry::create([
                    'vehicle_id' => $vehicle->id,
                    'vehicle_slug' => $vehicle->slug,
                    'vehicle_title' => $vehicle->title ?? ($vehicle->brand.' '.$vehicle->model),
                    'vehicle_link' => '/vehicule/'.$vehicle->slug,
                    'name' => $faker->name(),
                    'phone' => $faker->phoneNumber(),
                    'message' => $faker->optional()->sentence(12),
                    'status' => $faker->randomElement(['new','contacted','closed']),
                ]);
            }
        }

        // Generic inquiries without a specific vehicle
        for ($i = 0; $i < 5; $i++) {
            Inquiry::create([
                'vehicle_id' => null,
                'vehicle_slug' => null,
                'vehicle_title' => null,
                'vehicle_link' => null,
                'name' => $faker->name(),
                'phone' => $faker->phoneNumber(),
                'message' => $faker->optional()->sentence(12),
                'status' => 'new',
            ]);
        }
    }
}


