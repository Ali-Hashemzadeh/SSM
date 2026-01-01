<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('link');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('menus')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
}; 