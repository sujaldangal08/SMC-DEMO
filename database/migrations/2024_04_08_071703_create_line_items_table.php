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
        Schema::create('line_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
    $table->uuid('purchase_order_id');
    $table->string('item_code');
    $table->text('description');
    $table->decimal('unit_amount', 8, 4);
    $table->string('tax_type');
    $table->decimal('tax_amount', 8, 2);
    $table->decimal('line_amount', 8, 2);
    $table->string('account_code');
    $table->decimal('quantity', 8, 4);
    $table->timestamps();

    $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
});
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('line_items');
    }
};
