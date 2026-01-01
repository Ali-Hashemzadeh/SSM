<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mediables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->onDelete('cascade');
            $table->unsignedBigInteger('mediable_id');
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->string('mediable_type');
            $table->timestamps();
            $table->index(['mediable_id', 'mediable_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mediables');
    }
}; 