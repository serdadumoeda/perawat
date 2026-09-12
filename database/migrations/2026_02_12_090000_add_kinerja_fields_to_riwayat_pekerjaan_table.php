<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_pekerjaan', function (Blueprint $table) {
            $table->unsignedTinyInteger('kinerja_rating')->nullable()->after('penilaian_kinerja');
            $table->decimal('kinerja_score', 5, 2)->nullable()->after('kinerja_rating');
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_pekerjaan', function (Blueprint $table) {
            $table->dropColumn(['kinerja_rating', 'kinerja_score']);
        });
    }
};
