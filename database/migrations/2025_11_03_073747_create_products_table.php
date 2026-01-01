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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Foreign key to the user who created this product (admin/author)
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');

            // Core product fields
            $table->string('title'); // e.g., "Premium Oak Veneer MDF"
            $table->string('slug')->unique(); // e.g., "premium-oak-veneer-mdf"
            $table->text('description')->nullable(); // For a detailed product description

            // Your custom fields, now as dedicated columns
            $table->string('company_name')->nullable(); // e.g., "Fakher Woods"
            $table->string('dimensions')->nullable(); // e.g., "2440 x 1220 x 18mm"
            $table->string('material')->nullable(); // e.g., "MDF Core, Oak Veneer"
            $table->string('chrome_plating')->nullable(); // e.g., "High-Gloss Chrome" or "Matte Finish"

            // Status for admin control (so you can save drafts)
            $table->enum('status', ['published', 'draft'])->default('draft');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
