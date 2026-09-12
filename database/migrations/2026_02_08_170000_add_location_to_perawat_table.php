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
        Schema::table('perawat', function (Blueprint $table) {
            $table->string('kota')->nullable()->after('alamat');
            $table->string('provinsi')->nullable()->after('kota');

            $table->index('kota');
            $table->index('provinsi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perawat', function (Blueprint $table) {
            $table->dropIndex(['kota']);
            $table->dropIndex(['provinsi']);
            $table->dropColumn(['kota', 'provinsi']);
        });
    }
};
