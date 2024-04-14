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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('thumbnail_image');
            $table->text('description');
            $table->string('material_type');
            $table->integer('stock');
            $table->decimal('cost_price', 8, 2);
            $table->string('manufacturing');
            $table->string('supplier');
            $table->string('serial_number')->unique();
            $table->unsignedBigInteger('SKU_id');
            $table->foreign('SKU_id')->references('id')->on('skus')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
