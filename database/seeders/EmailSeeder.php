<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('email_templates')->insert([
            'subject' => 'Example Subject',
            'logo' => 'https://i.ibb.co/5Tf8VcN/Untitled-design-4.png',
            'top_link' => null,
            'top_text' => 'Visit Website',
            'title' => 'Lorem Ipsum',
            'emessage' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s,',
            'icon' => 'https://i.ibb.co/tHXcmVS/Black-and-White-Minimalist-Typographic-Recycling-Business-Logo.png',
            'buttons' => 'Click here',
            'button_link' => null,
            'footer_address' => 'Example Address',
            'footer_message' => 'Example Footer Message',
            'footer_link' => 'Example Link',
            'footer_text' => 'Example Footer Text',
            'color' => '2ab463',
            'is_otp' => false,
            'template_type' => 'Delivery Confirmation',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
