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
        // Create delivery_schedules table schema which will store the delivery schedules
        Schema::create('delivery_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constraint('users');
            $table->foreignId('driver_id')->constraint('users')->nullable();
            $table->foreignId('truck_id')->constraint('assets')->nullable();
            $table->string('name');
            $table->string('coordinates');
            $table->string('materials');
            $table->string('rate');
            $table->string('amount');
            $table->integer('n_trips');
            $table->integer('n_trips_done')->default(0);
            $table->integer('interval')->default(0);
            $table->date('start_date');
            $table->string('delivery_date');
            $table->date('end_date');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->string('delivery_notes');
            $table->enum('locale', ['domestic', 'international']);
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_schedules');
    }
};
