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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('truck_id');
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('delivery_location');
            $table->string('delivery_start_date');
            $table->string('delivery_end_date');
            $table->string('delivery_ start_time');
            $table->string('delivery_end_time');
            $table->string('delivery_file');
            $table->string('delivery_interval');
            $table->string('delivery_status');
            $table->string('delivery_notes');
            $table->timestamps();
            $table->foreign('truck_id')->references('id')->on('assets');
            $table->foreign('driver_id')->references('id')->on('users');
            $table->foreign('customer_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
