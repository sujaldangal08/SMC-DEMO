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
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders');
            $table->string('item_code')->nullable();
            $table->text('description');
            $table->decimal('unit_amount', 8, 4);
            $table->string('tax_type');
            $table->decimal('tax_amount', 8, 2);
            $table->decimal('line_amount', 8, 2);
            $table->string('account_code')->nullable();
            $table->decimal('quantity', 8, 4);
            $table->uuid('account_id')->nullable();
            $table->foreignId('item_id')->nullable()->constrained('items');
            $table->foreignId('tracking_id')->nullable()->constrained('trackings');
            $table->timestamps();


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
