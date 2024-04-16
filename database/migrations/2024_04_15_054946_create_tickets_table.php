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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('rego_number'); //comes from OCR
            $table->foreignId('driver_id')->constrained('users');
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('route_id')->constrained('routes')->nullable(); //For automation of ticket generation for schedule
            $table->string('material');
            $table->integer('initial_truck_weight')->nullable(); //comes from the machine
            $table->integer('full_bin_weight')->nullable();
            $table->integer('next_truck_weight')->nullable(); //comes from the machine
            $table->integer('tare_bin'); //comes from the machine
            $table->integer('gross_weight'); //comes from the machine
            $table->string('notes')->nullable();
            $table->string('image')->default('bin_image.png');
            $table->enum('weighing_type', ['bridge', 'pallet']);
            $table->enum('ticked_type', ['direct', 'schedule'])->default('direct');
            $table->string('lot_number');
            $table->string('ticket_number');
            $table->timestamp('in_time');
            $table->timestamp('out_time');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
