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
        Schema::table('otps', function (Blueprint $table) {
            // 1. Make mobile nullable
            $table->string('mobile')->nullable()->change();

            // 2. Add email and type
            $table->string('email')->nullable()->index()->after('mobile');
            $table->string('type')->default('sms')->after('code'); // 'sms' or 'email'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('otps', function (Blueprint $table) {
            $table->string('mobile')->nullable(false)->change();
            $table->dropColumn(['email', 'type']);
        });
    }
};
