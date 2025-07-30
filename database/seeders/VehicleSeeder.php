<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Support\Str;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = [
            [
                'title' => 'BMW X5 2023 - SUV de Lux',
                'brand' => 'BMW',
                'model' => 'X5',
                'year' => 2023,
                'price' => 85000,
                'mileage' => '15,000',
                'fuel_type' => 'Diesel',
                'transmission' => 'Automată',
                'engine_size' => '3.0L',
                'color' => 'Albastru',
                'description' => 'BMW X5 în stare excelentă, cu toate dotările moderne. Vehicul verificat tehnic, cu istoric complet. Perfect pentru familie sau afaceri.',
                'specifications' => [
                    'Putere' => '286 CP',
                    'Consum' => '6.5L/100km',
                    'Cilindri' => '6',
                    'Tracțiune' => '4x4'
                ],
                'features' => [
                    'Navigație GPS',
                    'Scaune încălzite',
                    'Sistem audio premium',
                    'Senzori de parcare',
                    'Camera de marșarier',
                    'Cruise control adaptiv'
                ],
                'vin' => 'WBA5A7C50FD123456',
                'condition' => 'Excelentă',
                'is_featured' => true,
                'is_available' => true,
                'slug' => 'bmw-x5-2023-suv-lux'
            ],
            [
                'title' => 'Mercedes-Benz C-Class 2022 - Sedan Elegant',
                'brand' => 'Mercedes-Benz',
                'model' => 'C-Class',
                'year' => 2022,
                'price' => 65000,
                'mileage' => '25,000',
                'fuel_type' => 'Benzină',
                'transmission' => 'Automată',
                'engine_size' => '2.0L',
                'color' => 'Alb',
                'description' => 'Mercedes-Benz C-Class elegant și modern, cu design rafinat și tehnologie de ultimă generație. Ideal pentru conducerea urbană și extraurbană.',
                'specifications' => [
                    'Putere' => '197 CP',
                    'Consum' => '7.2L/100km',
                    'Cilindri' => '4',
                    'Tracțiune' => 'Spate'
                ],
                'features' => [
                    'LED Matrix',
                    'Sistem MBUX',
                    'Scaune sport',
                    'Senzori de ploaie',
                    'Start/Stop automat',
                    'Sistem de siguranță avansat'
                ],
                'vin' => 'WDDWF4FB0FR123456',
                'condition' => 'Foarte bună',
                'is_featured' => true,
                'is_available' => true,
                'slug' => 'mercedes-c-class-2022-sedan-elegant'
            ],
            [
                'title' => 'Audi A4 2021 - Sedan Sport',
                'brand' => 'Audi',
                'model' => 'A4',
                'year' => 2021,
                'price' => 45000,
                'mileage' => '35,000',
                'fuel_type' => 'Diesel',
                'transmission' => 'Automată',
                'engine_size' => '2.0L',
                'color' => 'Gri',
                'description' => 'Audi A4 sport și dinamic, cu performanțe excelente și consum redus. Vehicul perfect întreținut, cu toate reviziile la zi.',
                'specifications' => [
                    'Putere' => '150 CP',
                    'Consum' => '5.8L/100km',
                    'Cilindri' => '4',
                    'Tracțiune' => 'Față'
                ],
                'features' => [
                    'Xenon adaptive',
                    'Sistem MMI',
                    'Scaune sport',
                    'Senzori de parcare',
                    'Cruise control',
                    'Sistem audio Bose'
                ],
                'vin' => 'WAUZZZ8K9BA123456',
                'condition' => 'Bună',
                'is_featured' => false,
                'is_available' => true,
                'slug' => 'audi-a4-2021-sedan-sport'
            ],
            [
                'title' => 'Volkswagen Golf 2023 - Hatchback Versatil',
                'brand' => 'Volkswagen',
                'model' => 'Golf',
                'year' => 2023,
                'price' => 35000,
                'mileage' => '8,000',
                'fuel_type' => 'Benzină',
                'transmission' => 'Manuală',
                'engine_size' => '1.5L',
                'color' => 'Roșu',
                'description' => 'Volkswagen Golf nou, cu tehnologie modernă și eficiență ridicată. Perfect pentru oraș și călătorii. Garantie de fabrică.',
                'specifications' => [
                    'Putere' => '110 CP',
                    'Consum' => '6.1L/100km',
                    'Cilindri' => '4',
                    'Tracțiune' => 'Față'
                ],
                'features' => [
                    'LED headlights',
                    'Sistem infotainment',
                    'Senzori de parcare',
                    'Start/Stop',
                    'Cruise control',
                    'Sistem de siguranță'
                ],
                'vin' => 'WVWZZZ1KZ8W123456',
                'condition' => 'Excelentă',
                'is_featured' => true,
                'is_available' => true,
                'slug' => 'volkswagen-golf-2023-hatchback-versatil'
            ],
            [
                'title' => 'Toyota RAV4 2022 - SUV Hibrid',
                'brand' => 'Toyota',
                'model' => 'RAV4',
                'year' => 2022,
                'price' => 55000,
                'mileage' => '18,000',
                'fuel_type' => 'Hibrid',
                'transmission' => 'CVT',
                'engine_size' => '2.5L',
                'color' => 'Negru',
                'description' => 'Toyota RAV4 hibrid, cu tehnologie avansată și consum foarte redus. Perfect pentru familie și aventuri. Fiabilitate Toyota garantată.',
                'specifications' => [
                    'Putere' => '218 CP',
                    'Consum' => '4.8L/100km',
                    'Cilindri' => '4',
                    'Tracțiune' => '4x4'
                ],
                'features' => [
                    'Sistem hibrid',
                    'Senzori de siguranță',
                    'Camera 360°',
                    'Scaune încălzite',
                    'Navigație',
                    'Sistem audio JBL'
                ],
                'vin' => 'JTMRFREV0MD123456',
                'condition' => 'Foarte bună',
                'is_featured' => false,
                'is_available' => true,
                'slug' => 'toyota-rav4-2022-suv-hibrid'
            ],
            [
                'title' => 'Ford Focus 2021 - Hatchback Economic',
                'brand' => 'Ford',
                'model' => 'Focus',
                'year' => 2021,
                'price' => 28000,
                'mileage' => '42,000',
                'fuel_type' => 'Diesel',
                'transmission' => 'Manuală',
                'engine_size' => '1.6L',
                'color' => 'Alb',
                'description' => 'Ford Focus economic și fiabil, perfect pentru utilizarea zilnică. Consum redus și costuri de întreținere mici.',
                'specifications' => [
                    'Putere' => '95 CP',
                    'Consum' => '5.2L/100km',
                    'Cilindri' => '4',
                    'Tracțiune' => 'Față'
                ],
                'features' => [
                    'Senzori de parcare',
                    'Sistem SYNC',
                    'Cruise control',
                    'Senzori de ploaie',
                    'Sistem audio',
                    'Scaune confortabile'
                ],
                'vin' => 'WF0AXXGAKA123456',
                'condition' => 'Bună',
                'is_featured' => false,
                'is_available' => true,
                'slug' => 'ford-focus-2021-hatchback-economic'
            ]
        ];

        foreach ($vehicles as $vehicleData) {
            $vehicle = Vehicle::create($vehicleData);
            
            // Create sample images for each vehicle
            for ($i = 1; $i <= 3; $i++) {
                VehicleImage::create([
                    'vehicle_id' => $vehicle->id,
                    'image_path' => "vehicles/{$vehicle->slug}/image-{$i}.jpg",
                    'alt_text' => "{$vehicle->brand} {$vehicle->model} - Imagine {$i}",
                    'is_primary' => $i === 1,
                    'sort_order' => $i
                ]);
            }
        }
    }
}
