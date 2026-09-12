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
            'lamaran' => 'cascade',
            'rekrutmen' => 'cascade',
            'pelatihan_peserta' => 'cascade',
            'tracing_alumni' => 'cascade',
            'str_sync_requests' => 'cascade',
            'riwayat_pekerjaan_syncs' => 'set null',
            'users' => 'set null',
        ];

        if ($driver === 'sqlite') {
            foreach ($tables as $table => $onDelete) {
                $action = $onDelete === 'set null' ? 'SET NULL' : 'CASCADE';
                DB::statement("ALTER TABLE {$table} ADD COLUMN perawat_nik TEXT REFERENCES perawat(nik) ON DELETE {$action}");
                DB::statement("CREATE INDEX IF NOT EXISTS {$table}_perawat_nik_idx ON {$table}(perawat_nik)");
            }
        } else {
            foreach (array_keys($tables) as $table) {
                Schema::table($table, function (Blueprint $table) {
                    $table->string('perawat_nik')->nullable()->after('perawat_id');
                    $table->index('perawat_nik');
                });
            }
        }

        $this->backfillPerawatNik(array_keys($tables));

        if ($driver !== 'sqlite') {
            foreach ($tables as $table => $onDelete) {
                Schema::table($table, function (Blueprint $table) use ($onDelete) {
                    $foreign = $table->foreign('perawat_nik')
                        ->references('nik')
                        ->on('perawat');

                    if ($onDelete === 'set null') {
                        $foreign->nullOnDelete();
                    } else {
                        $foreign->cascadeOnDelete();
                    }
                });
            }
        }

        if ($driver === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS lamaran_perawat_nik_lowongan_unique ON lamaran(perawat_nik, lowongan_id)');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS rekrutmen_mitra_perawat_nik_unique ON rekrutmen(mitra_pengguna_id, perawat_nik)');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS pelatihan_peserta_perawat_nik_unique ON pelatihan_peserta(pelatihan_id, perawat_nik)');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS tracing_alumni_perawat_nik_institusi_unique ON tracing_alumni(perawat_nik, institusi_pendidikan_id)');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS users_perawat_nik_unique ON users(perawat_nik)');
            DB::statement('CREATE INDEX IF NOT EXISTS rekrutmen_perawat_nik_status_index ON rekrutmen(perawat_nik, status)');
            DB::statement('CREATE INDEX IF NOT EXISTS str_sync_requests_perawat_nik_status_index ON str_sync_requests(perawat_nik, status)');
            DB::statement('CREATE INDEX IF NOT EXISTS riwayat_pekerjaan_syncs_perawat_nik_status_index ON riwayat_pekerjaan_syncs(perawat_nik, status)');
        } else {
            Schema::table('lamaran', function (Blueprint $table) {
                $table->unique(['perawat_nik', 'lowongan_id'], 'lamaran_perawat_nik_lowongan_unique');
            });
            Schema::table('rekrutmen', function (Blueprint $table) {
                $table->unique(['mitra_pengguna_id', 'perawat_nik'], 'rekrutmen_mitra_perawat_nik_unique');
                $table->index(['perawat_nik', 'status'], 'rekrutmen_perawat_nik_status_index');
            });
            Schema::table('pelatihan_peserta', function (Blueprint $table) {
                $table->unique(['pelatihan_id', 'perawat_nik'], 'pelatihan_peserta_perawat_nik_unique');
            });
            Schema::table('tracing_alumni', function (Blueprint $table) {
                $table->unique(['perawat_nik', 'institusi_pendidikan_id'], 'tracing_alumni_perawat_nik_institusi_unique');
            });
            Schema::table('str_sync_requests', function (Blueprint $table) {
                $table->index(['perawat_nik', 'status'], 'str_sync_requests_perawat_nik_status_index');
            });
            Schema::table('riwayat_pekerjaan_syncs', function (Blueprint $table) {
                $table->index(['perawat_nik', 'status'], 'riwayat_pekerjaan_syncs_perawat_nik_status_index');
            });
            Schema::table('users', function (Blueprint $table) {
                $table->unique('perawat_nik', 'users_perawat_nik_unique');
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            return;
        }

        Schema::table('lamaran', function (Blueprint $table) {
            $table->dropUnique('lamaran_perawat_nik_lowongan_unique');
        });
        Schema::table('rekrutmen', function (Blueprint $table) {
            $table->dropUnique('rekrutmen_mitra_perawat_nik_unique');
            $table->dropIndex('rekrutmen_perawat_nik_status_index');
        });
        Schema::table('pelatihan_peserta', function (Blueprint $table) {
            $table->dropUnique('pelatihan_peserta_perawat_nik_unique');
        });
        Schema::table('tracing_alumni', function (Blueprint $table) {
            $table->dropUnique('tracing_alumni_perawat_nik_institusi_unique');
        });
        Schema::table('str_sync_requests', function (Blueprint $table) {
            $table->dropIndex('str_sync_requests_perawat_nik_status_index');
        });
        Schema::table('riwayat_pekerjaan_syncs', function (Blueprint $table) {
            $table->dropIndex('riwayat_pekerjaan_syncs_perawat_nik_status_index');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_perawat_nik_unique');
        });

        $tables = [
            'lamaran',
            'rekrutmen',
            'pelatihan_peserta',
            'tracing_alumni',
            'str_sync_requests',
            'riwayat_pekerjaan_syncs',
            'users',
        ];

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
