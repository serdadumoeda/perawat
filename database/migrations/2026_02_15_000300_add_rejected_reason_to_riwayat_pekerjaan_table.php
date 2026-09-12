<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_pekerjaan', function (Blueprint $table) {
            $table->text('rejected_reason')->nullable()->after('keterangan');
        });

        DB::statement("
            UPDATE riwayat_pekerjaan
            SET rejected_reason = keterangan
            WHERE rejected_reason IS NULL
              AND verifikasi_status = 'rejected'
              AND keterangan IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('riwayat_pekerjaan', function (Blueprint $table) {
            $table->dropColumn('rejected_reason');
        });
    }
};
