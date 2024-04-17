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
        Schema::create('pickup_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete()->nullable();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete()->nullable();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete()->nullable();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete()->nullable();
            $table->date('pickup_date');
            $table->enum('status', ['pending', 'active', 'inactive', 'done', 'unloading', 'full', 'cancelled'])->default('pending');
            $table->string('notes')->nullable();
            $table->string('material_type')->nullable();
            $table->string('n_bins')->nullable();
            $table->string('tare_weight')->nullable();
            $table->string('image')->nullable();
            $table->string('coordinates')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pickup_schedules');
    }
};
