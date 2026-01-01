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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            // The product_id is nullable in case the original product is deleted
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');

            $table->unsignedInteger('quantity');

            // --- Product Snapshot ---
            // We copy the product data so the order is accurate even if the product is edited later
            $table->string('title');
            $table->string('company_name')->nullable();
            $table->string('dimensions')->nullable();
            $table->string('material')->nullable();
            $table->string('chrome_plating')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
