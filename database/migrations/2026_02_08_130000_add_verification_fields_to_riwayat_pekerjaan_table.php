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
            $table->string('sumber_data')->default('mitra')->after('keterangan');
            $table->string('verifikasi_status')->default('pending')->after('sumber_data');
            $table->foreignUuid('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();

            $table->index('verifikasi_status');
            $table->index(['mitra_pengguna_id', 'verifikasi_status']);
        });

        DB::table('riwayat_pekerjaan')
            ->where('is_verified', true)
            ->update([
                'verifikasi_status' => 'verified',
            ]);
    }

    public function down(): void
    {
        Schema::table('riwayat_pekerjaan', function (Blueprint $table) {
            $table->dropIndex(['verifikasi_status']);
            $table->dropIndex(['mitra_pengguna_id', 'verifikasi_status']);
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['sumber_data', 'verifikasi_status']);
        });
    }
};
