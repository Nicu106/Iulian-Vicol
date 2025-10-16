<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContactMessage;
use Faker\Factory as FakerFactory;

class ContactMessageSeeder extends Seeder
{
    public function run(): void
    {
        $faker = FakerFactory::create('ro_RO');
        for ($i = 0; $i < 15; $i++) {
            ContactMessage::create([
                'name' => $faker->name(),
                'phone' => $faker->phoneNumber(),
                'email' => $faker->optional()->safeEmail(),
                'subject' => $faker->optional()->sentence(6),
                'message' => $faker->paragraph(2),
                'gdpr' => $faker->boolean(70),
                'newsletter' => $faker->boolean(30),
            ]);
        }
    }
}


