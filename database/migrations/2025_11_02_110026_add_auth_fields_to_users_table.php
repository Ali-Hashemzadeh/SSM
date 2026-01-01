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
        Schema::table('users', function (Blueprint $table) {
            // 1. Make mobile nullable
            $table->string('mobile')->nullable()->change();

            // 2. Add new auth and profile fields
            $table->string('email')->nullable()->unique()->after('mobile');
            $table->string('company_name')->nullable()->after('last_name');
            $table->string('country')->nullable()->after('company_name');
            $table->string('province')->nullable()->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Note: Reverting mobile to NOT NULL might fail if null data exists.
            $table->string('mobile')->nullable(false)->change();
            $table->dropColumn(['email', 'company_name', 'country', 'province']);
        });
    }
};
