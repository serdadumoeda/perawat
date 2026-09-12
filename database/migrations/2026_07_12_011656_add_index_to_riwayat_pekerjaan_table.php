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
        Schema::table('riwayat_pekerjaan', function (Blueprint $table) {
            $table->index(['perawat_nik', 'tanggal_mulai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_pekerjaan', function (Blueprint $table) {
            $table->dropIndex(['perawat_nik', 'tanggal_mulai']);
        });
    }
};
