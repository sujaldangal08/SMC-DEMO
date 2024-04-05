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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name');
            $table->string('branch_street');
            $table->string('branch_street2')->nullable();
            $table->string('branch_city');
            $table->string('branch_state');
            $table->string('branch_zip');
            $table->string('branch_phone');
            $table->string('branch_email');
            $table->string('branch_code')->unique();
            $table->string('branch_status');
            $table->string('branch_country_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
