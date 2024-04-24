<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Faq::factory()->create([
            'title' => 'Question1',
            'description' => 'what is laravel',
        ]);
    }
}
