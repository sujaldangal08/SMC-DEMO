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
            $table->foreignId('route_id')->nullable()->constrained('routes')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->date('pickup_date');
            $table->enum('status', ['pending', 'active', 'inactive', 'done', 'unloading', 'full', 'cancelled'])->default('pending');
            $table->string('notes')->nullable();
            $table->string('materials')->nullable();
            $table->string('weighing_type')->nullable();
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
