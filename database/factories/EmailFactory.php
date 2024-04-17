<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;



/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class EmailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            Schema::create('email_templates', function (Blueprint $table) {
                $table->id();
                $table->string('subject');
                $table->string('logo')->nullable(); // Logo can be a URL to the image
                $table->string('top_link')->nullable();
                $table->string('top_text')->nullable();
                $table->string('title');
                $table->string('emessage');
                $table->string('icon')->nullable(); // Icons can be a JSON array of URLs to the icons
                $table->string('buttons')->nullable(); // Buttons can be a JSON array of buttons
                $table->string('button_link')->nullable();
                $table->string('footer_address');
                $table->string('footer_message');
                $table->string('footer_link');
                $table->string('footer_text');
                $table->string('color');
                $table->string('template_type');
                $table->timestamps();
            })
        ];
    }
}
