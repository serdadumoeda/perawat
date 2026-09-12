<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perawat', function (Blueprint $table) {
            $table->boolean('public_profile_enabled')->default(false)->after('status_ketersediaan');
            $table->string('public_profile_token', 64)->nullable()->unique()->after('public_profile_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('perawat', function (Blueprint $table) {
            $table->dropUnique(['public_profile_token']);
            $table->dropColumn(['public_profile_enabled', 'public_profile_token']);
        });
    }
};
