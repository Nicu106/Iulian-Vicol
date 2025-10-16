<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Testimonial;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'author_name' => 'Ion Popescu',
                'author_location' => 'Cluj-Napoca',
                'image_path' => null,
                'quote' => 'Experiență excelentă! Recomand cu încredere.',
                'is_active' => true,
                'order_index' => 1,
            ],
            [
                'author_name' => 'Maria Ionescu',
                'author_location' => 'București',
                'image_path' => null,
                'quote' => 'Proces rapid și transparent, mașină impecabilă.',
                'is_active' => true,
                'order_index' => 2,
            ],
            [
                'author_name' => 'Andrei Vasile',
                'author_location' => 'Timișoara',
                'image_path' => null,
                'quote' => 'Cele mai bune oferte și suport profesionist.',
                'is_active' => true,
                'order_index' => 3,
            ],
        ];

        foreach ($items as $item) {
            Testimonial::query()->updateOrCreate(
                [ 'author_name' => $item['author_name'], 'quote' => $item['quote'] ],
                $item
            );
        }
    }
}



