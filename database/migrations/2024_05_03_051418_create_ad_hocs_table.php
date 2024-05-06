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
        Schema::create('ad_hocs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('staff_id')->constrained('users');
            $table->string('materials');
            $table->string('rate');
            $table->enum('staff_status', ['pending', 'approved', 'rejected', 'review'])->default('pending');
            $table->string('weighing_type');
            $table->string('notes')->nullable();
            $table->string('amount');
            $table->enum('customer_status', ['pending', 'approved', 'rejected', 'review'])->default('pending');
            $table->string('attachment')->nullable();
            $table->foreignId('branch_id')->constrained('branches');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_hocs');
    }
};
