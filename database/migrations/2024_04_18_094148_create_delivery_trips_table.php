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
        Schema::create('delivery_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constraint('delivery_schedules');
            $table->foreignId('driver_id')->constraint('users');
            $table->foreignId('truck_id')->constraint('assets');
            $table->string('materials_loaded');
            $table->string('amount_loaded');
            $table->string('trip_number');
            $table->enum('status', ['pending', 'in_progress', 'completed']);
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
