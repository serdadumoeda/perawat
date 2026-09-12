<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_legalitas', function (Blueprint $table) {
            $table->string('str_document_status')->nullable()->after('str_document_path');
            $table->uuid('str_document_reviewed_by')->nullable()->after('str_document_status');
            $table->timestamp('str_document_reviewed_at')->nullable()->after('str_document_reviewed_by');
            $table->string('str_document_note')->nullable()->after('str_document_reviewed_at');

            $table->string('izin_luar_negeri_document_status')->nullable()->after('izin_luar_negeri_document_path');
            $table->uuid('izin_luar_negeri_document_reviewed_by')->nullable()->after('izin_luar_negeri_document_status');
            $table->timestamp('izin_luar_negeri_document_reviewed_at')->nullable()->after('izin_luar_negeri_document_reviewed_by');
            $table->string('izin_luar_negeri_document_note')->nullable()->after('izin_luar_negeri_document_reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('data_legalitas', function (Blueprint $table) {
            $table->dropColumn([
                'str_document_status',
                'str_document_reviewed_by',
                'str_document_reviewed_at',
                'str_document_note',
                'izin_luar_negeri_document_status',
                'izin_luar_negeri_document_reviewed_by',
                'izin_luar_negeri_document_reviewed_at',
                'izin_luar_negeri_document_note',
            ]);
        });
    }
};
