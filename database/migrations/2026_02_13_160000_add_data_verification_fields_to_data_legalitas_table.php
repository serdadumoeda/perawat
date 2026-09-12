<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_legalitas', function (Blueprint $table) {
            $table->string('data_status')->nullable()->after('status_str');
            $table->timestamp('data_submitted_at')->nullable()->after('data_status');
            $table->uuid('data_reviewed_by')->nullable()->after('data_submitted_at');
            $table->timestamp('data_reviewed_at')->nullable()->after('data_reviewed_by');
            $table->string('data_rejection_note')->nullable()->after('data_reviewed_at');
            $table->string('source_verification')->nullable()->after('sumber_validasi');
            $table->index('data_status');
        });

        $this->backfillStatuses();
    }

    public function down(): void
    {
        Schema::table('data_legalitas', function (Blueprint $table) {
            $table->dropIndex(['data_status']);
            $table->dropColumn([
                'data_status',
                'data_submitted_at',
                'data_reviewed_by',
                'data_reviewed_at',
                'data_rejection_note',
                'source_verification',
            ]);
        });
    }

    private function backfillStatuses(): void
    {
        DB::statement("
            UPDATE data_legalitas
            SET data_status = CASE
                WHEN status_str = 'aktif' THEN 'verified'
                WHEN status_str = 'expired' THEN 'expired'
                WHEN status_str = 'hold' THEN 'submitted'
                WHEN nomor_str IS NOT NULL THEN 'submitted'
                ELSE 'draft'
            END
            WHERE data_status IS NULL
        ");

        DB::statement("
            UPDATE data_legalitas
            SET source_verification = COALESCE(source_verification, sumber_validasi, 'manual')
            WHERE source_verification IS NULL
        ");
    }
};
