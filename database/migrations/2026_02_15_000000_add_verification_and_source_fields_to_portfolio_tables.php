<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_pendidikan', function (Blueprint $table) {
            $table->string('source_system')->nullable()->after('status_verifikasi');
            $table->string('source_actor')->nullable()->after('source_system');
            $table->foreignUuid('verified_by')->nullable()->after('source_actor')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('rejected_reason')->nullable()->after('verified_at');

            $table->index('source_system');
            $table->index('status_verifikasi');
        });

        Schema::table('sertifikasi_kompetensi', function (Blueprint $table) {
            $table->string('source_system')->nullable()->after('status_verifikasi');
            $table->string('source_actor')->nullable()->after('source_system');
            $table->foreignUuid('verified_by')->nullable()->after('source_actor')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('rejected_reason')->nullable()->after('verified_at');

            $table->index('source_system');
            $table->index('status_verifikasi');
        });

        Schema::table('data_legalitas', function (Blueprint $table) {
            $table->string('source_system')->nullable()->after('source_verification');
            $table->string('source_actor')->nullable()->after('source_system');

            $table->index('source_system');
        });

        Schema::table('riwayat_pekerjaan', function (Blueprint $table) {
            $table->string('source_system')->nullable()->after('verifikasi_status');
            $table->string('source_actor')->nullable()->after('source_system');

            $table->index('source_system');
        });

        $this->backfillRiwayatPendidikan();
        $this->backfillSertifikasi();
        $this->backfillLegalitas();
        $this->backfillRiwayatPekerjaan();
    }

    public function down(): void
    {
        Schema::table('riwayat_pendidikan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropIndex(['source_system']);
            $table->dropIndex(['status_verifikasi']);
            $table->dropColumn([
                'source_system',
                'source_actor',
                'verified_at',
                'rejected_reason',
            ]);
        });

        Schema::table('sertifikasi_kompetensi', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropIndex(['source_system']);
            $table->dropIndex(['status_verifikasi']);
            $table->dropColumn([
                'source_system',
                'source_actor',
                'verified_at',
                'rejected_reason',
            ]);
        });

        Schema::table('data_legalitas', function (Blueprint $table) {
            $table->dropIndex(['source_system']);
            $table->dropColumn(['source_system', 'source_actor']);
        });

        Schema::table('riwayat_pekerjaan', function (Blueprint $table) {
            $table->dropIndex(['source_system']);
            $table->dropColumn(['source_system', 'source_actor']);
        });
    }

    private function backfillRiwayatPendidikan(): void
    {
        DB::table('riwayat_pendidikan')
            ->whereNull('status_verifikasi')
            ->update(['status_verifikasi' => 'verified']);

        DB::table('riwayat_pendidikan')
            ->whereNull('source_system')
            ->update(['source_system' => 'kampus_portal']);

        DB::table('riwayat_pendidikan')
            ->whereNull('source_actor')
            ->update(['source_actor' => 'kampus']);

        DB::table('riwayat_pendidikan')
            ->whereIn('status_verifikasi', [
                'verified',
                'rejected',
            ])
            ->whereNull('verified_at')
            ->update(['verified_at' => DB::raw('COALESCE(updated_at, created_at)')]);
    }

    private function backfillSertifikasi(): void
    {
        DB::table('sertifikasi_kompetensi')
            ->whereNull('status_verifikasi')
            ->update(['status_verifikasi' => 'pending']);

        DB::table('sertifikasi_kompetensi')
            ->whereNull('source_system')
            ->update(['source_system' => 'lembaga_portal']);

        DB::table('sertifikasi_kompetensi')
            ->whereNull('source_actor')
            ->update(['source_actor' => 'lembaga']);

        DB::table('sertifikasi_kompetensi')
            ->whereIn('status_verifikasi', [
                'verified',
                'valid',
                'rejected',
            ])
            ->whereNull('verified_at')
            ->update(['verified_at' => DB::raw('COALESCE(updated_at, created_at)')]);
    }

    private function backfillLegalitas(): void
    {
        DB::statement("
            UPDATE data_legalitas
            SET source_system = CASE
                WHEN LOWER(COALESCE(source_verification, '')) = 'api' THEN 'regulator_api'
                WHEN LOWER(COALESCE(sumber_validasi, '')) = 'perawat' THEN 'perawat_portal'
                ELSE 'regulator_portal'
            END
            WHERE source_system IS NULL
        ");

        DB::statement("
            UPDATE data_legalitas
            SET source_actor = CASE
                WHEN LOWER(COALESCE(sumber_validasi, '')) = 'perawat' THEN 'perawat'
                ELSE 'regulator'
            END
            WHERE source_actor IS NULL
        ");
    }

    private function backfillRiwayatPekerjaan(): void
    {
        DB::statement("
            UPDATE riwayat_pekerjaan
            SET source_system = CASE
                WHEN LOWER(COALESCE(sumber_data, '')) = 'perawat' THEN 'perawat_portal'
                WHEN LOWER(COALESCE(sumber_data, '')) = 'mitra' THEN 'mitra_portal'
                ELSE 'internal'
            END
            WHERE source_system IS NULL
        ");

        DB::statement("
            UPDATE riwayat_pekerjaan
            SET source_actor = CASE
                WHEN LOWER(COALESCE(sumber_data, '')) = 'perawat' THEN 'perawat'
                WHEN LOWER(COALESCE(sumber_data, '')) = 'mitra' THEN 'mitra'
                ELSE 'system'
            END
            WHERE source_actor IS NULL
        ");
    }
};
