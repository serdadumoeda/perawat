<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables where perawat_id and perawat_nik must always be paired and valid.
     *
     * @var array<int, string>
     */
    private array $strictPerawatTables = [
        'riwayat_pendidikan',
        'sertifikasi_kompetensi',
        'data_legalitas',
        'riwayat_pekerjaan',
        'lamaran',
        'rekrutmen',
        'pelatihan_peserta',
        'tracing_alumni',
        'str_sync_requests',
    ];

    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        $this->syncPerawatNikByPerawatId(array_merge($this->strictPerawatTables, [
            'users',
            'riwayat_pekerjaan_syncs',
            'demo_scenario_runs',
        ]));

        if ($driver === 'sqlite') {
            return;
        }

        $this->nullifyOrphanedReviewerIds();

        Schema::table('perawat', function (Blueprint $table) {
            $table->unique(['id', 'nik'], 'perawat_id_nik_unique');
        });

        foreach ($this->strictPerawatTables as $tableName) {
            $this->setPerawatNikNotNull($driver, $tableName);
            $this->addCompositePerawatForeign($tableName, 'cascade');
        }

        Schema::table('data_legalitas', function (Blueprint $table) {
            $table->foreign('data_reviewed_by', 'data_legalitas_data_reviewed_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('str_document_reviewed_by', 'data_legalitas_str_document_reviewed_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('izin_luar_negeri_document_reviewed_by', 'data_legalitas_izin_reviewed_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            return;
        }

        Schema::table('data_legalitas', function (Blueprint $table) {
            $table->dropForeign('data_legalitas_data_reviewed_by_fk');
            $table->dropForeign('data_legalitas_str_document_reviewed_by_fk');
            $table->dropForeign('data_legalitas_izin_reviewed_by_fk');
        });

        foreach ($this->strictPerawatTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropForeign($tableName.'_perawat_pair_fk');
            });
        }

        foreach ($this->strictPerawatTables as $tableName) {
            $this->setPerawatNikNullable($driver, $tableName);
        }

        Schema::table('perawat', function (Blueprint $table) {
            $table->dropUnique('perawat_id_nik_unique');
        });
    }

    private function syncPerawatNikByPerawatId(array $tables): void
    {
        $perawatMap = DB::table('perawat')->pluck('nik', 'id');

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)
                || !Schema::hasColumn($tableName, 'perawat_id')
                || !Schema::hasColumn($tableName, 'perawat_nik')) {
                continue;
            }

            DB::table($tableName)
                ->select('id', 'perawat_id', 'perawat_nik')
                ->whereNotNull('perawat_id')
                ->orderBy('id')
                ->chunk(500, function ($rows) use ($tableName, $perawatMap) {
                    foreach ($rows as $row) {
                        $expectedNik = $perawatMap[$row->perawat_id] ?? null;
                        if (!$expectedNik || $row->perawat_nik === $expectedNik) {
                            continue;
                        }

                        DB::table($tableName)
                            ->where('id', $row->id)
                            ->update(['perawat_nik' => $expectedNik]);
                    }
                });
        }
    }

    private function nullifyOrphanedReviewerIds(): void
    {
        foreach ([
            'data_reviewed_by',
            'str_document_reviewed_by',
            'izin_luar_negeri_document_reviewed_by',
        ] as $column) {
            DB::table('data_legalitas')
                ->whereNotNull($column)
                ->whereNotIn($column, function ($query) {
                    $query->select('id')->from('users');
                })
                ->update([$column => null]);
        }
    }

    private function addCompositePerawatForeign(string $tableName, string $onDelete): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($tableName, $onDelete) {
            $foreign = $table->foreign(
                ['perawat_id', 'perawat_nik'],
                $tableName.'_perawat_pair_fk'
            )->references(['id', 'nik'])
                ->on('perawat')
                ->cascadeOnUpdate();

            if ($onDelete === 'set null') {
                $foreign->nullOnDelete();
            } else {
                $foreign->cascadeOnDelete();
            }
        });
    }

    private function setPerawatNikNotNull(string $driver, string $tableName): void
    {
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE {$tableName} MODIFY perawat_nik VARCHAR(255) NOT NULL");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE {$tableName} ALTER COLUMN perawat_nik SET NOT NULL");
        }
    }

    private function setPerawatNikNullable(string $driver, string $tableName): void
    {
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE {$tableName} MODIFY perawat_nik VARCHAR(255) NULL");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE {$tableName} ALTER COLUMN perawat_nik DROP NOT NULL");
        }
    }
};
