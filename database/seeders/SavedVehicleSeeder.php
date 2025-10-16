<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SavedVehicle;
use App\Models\User;
use App\Models\Vehicle;

class SavedVehicleSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->inRandomOrder()->limit(5)->get();
        $vehicles = Vehicle::query()->inRandomOrder()->limit(10)->get();

        foreach ($users as $user) {
            foreach ($vehicles->random(min(3, $vehicles->count())) as $vehicle) {
                SavedVehicle::query()->firstOrCreate([
                    'user_id' => $user->id,
                    'vehicle_id' => $vehicle->id,
                ], [
                    'notes' => 'Interesat de acest model.',
                    'metadata' => ['source' => 'seed'],
                ]);
            }
        }
    }
}



