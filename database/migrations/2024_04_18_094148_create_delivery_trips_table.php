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
        // Create delivery_trips table schema which will store the delivery trips associated with a delivery schedule
        Schema::create('delivery_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('delivery_schedules');
            $table->foreignId('driver_id')->constrained('users');
            $table->foreignId('truck_id')->constrained('assets');
            $table->string('materials_loaded');
            $table->string('amount_loaded');
            $table->string('trip_number');
            $table->enum('status', ['pending', 'in_progress', 'completed']);
            $table->date('trip_date');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_trips');
    }
};
