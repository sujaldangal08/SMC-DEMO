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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('invoice_id')->unique();
            $table->string('invoice_number');
            $table->string('reference')->nullable();
            $table->decimal('amount_due', 10, 2);
            $table->decimal('amount_paid', 10, 2);
            $table->decimal('amount_credited', 10, 2);
            $table->decimal('sub_total', 10, 2)->nullable();
            $table->decimal('total_tax', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->string('currency_code');
            $table->unsignedBigInteger('contact_id');
            $table->string('status');
            $table->string('line_amount_types');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
