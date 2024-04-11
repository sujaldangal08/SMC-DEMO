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
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('user')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('user')->onDelete('cascade');
            $table->dateTime('pickup_date');
            $table->dateTime('status');
            $table->text('notes')->nullable();
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
