<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $tables = [
            'riwayat_pendidikan',
            'sertifikasi_kompetensi',
            'data_legalitas',
            'riwayat_pekerjaan',
        ];

        if ($driver === 'sqlite') {
            foreach ($tables as $table) {
                DB::statement("ALTER TABLE {$table} ADD COLUMN perawat_nik TEXT REFERENCES perawat(nik) ON DELETE CASCADE");
            }
        } else {
            foreach ($tables as $table) {
                Schema::table($table, function (Blueprint $table) {
                    $table->string('perawat_nik')->nullable()->after('perawat_id');
                    $table->index('perawat_nik');
                });
            }
        }

        $this->backfillPerawatNik($tables);

        if ($driver !== 'sqlite') {
            foreach ($tables as $table) {
                Schema::table($table, function (Blueprint $table) {
                    $table->foreign('perawat_nik')
                        ->references('nik')
                        ->on('perawat')
                        ->cascadeOnDelete();
                });
            }
        }

        if ($driver === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS data_legalitas_perawat_nik_unique ON data_legalitas(perawat_nik)');
        } else {
            Schema::table('data_legalitas', function (Blueprint $table) {
                $table->unique('perawat_nik');
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $tables = [
            'riwayat_pendidikan',
            'sertifikasi_kompetensi',
            'data_legalitas',
            'riwayat_pekerjaan',
        ];

        if ($driver === 'sqlite') {
            return;
        }

        Schema::table('data_legalitas', function (Blueprint $table) {
            $table->dropUnique(['perawat_nik']);
        });

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['perawat_nik']);
                $table->dropIndex(['perawat_nik']);
                $table->dropColumn('perawat_nik');
            });
        }
    }

    private function backfillPerawatNik(array $tables): void
    {
        $perawatMap = DB::table('perawat')->pluck('nik', 'id');

        foreach ($tables as $table) {
            $rows = DB::table($table)->select('id', 'perawat_id')->get();
            foreach ($rows as $row) {
                $nik = $perawatMap[$row->perawat_id] ?? null;
                if ($nik) {
                    DB::table($table)->where('id', $row->id)->update(['perawat_nik' => $nik]);
                }
            }
        }
    }
};
