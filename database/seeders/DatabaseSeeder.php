<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UsersSeeder;
use Database\Seeders\VehicleSeeder;
use Database\Seeders\InquirySeeder;
use Database\Seeders\ContactMessageSeeder;
use Database\Seeders\SavedVehicleSeeder;
use Database\Seeders\TestimonialSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Base user to ensure auth works
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Additional seeders
        $this->call([
            UsersSeeder::class,
            VehicleSeeder::class,
            InquirySeeder::class,
            ContactMessageSeeder::class,
            SavedVehicleSeeder::class,
            TestimonialSeeder::class,
        ]);
    }
}
