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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('purchase_order_number');
            $table->dateTime('date');
            $table->dateTime('delivery_date');
            $table->text('delivery_address');
            $table->string('attention_to');
            $table->string('telephone');
            $table->text('delivery_instructions');
            $table->boolean('has_errors');
            $table->boolean('is_discounted');
            $table->string('reference');
            $table->string('type');
            $table->decimal('currency_rate', 10, 10);
            $table->string('currency_code');
            $table->unsignedBigInteger('contact_id');
            $table->uuid('branding_theme_id');
            $table->string('status');
            $table->string('line_amount_types');
            $table->decimal('sub_total', 8, 2);
            $table->decimal('total_tax', 8, 2);
            $table->decimal('total', 8, 2);
            $table->dateTime('updated_date_utc');
            $table->boolean('has_attachments');
            $table->timestamps();

            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
