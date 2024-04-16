<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void
    {
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
            $table->boolean('is_otp')->default(false); //Boolean indicating if it's an OTP or not
            $table->string('template_type');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
