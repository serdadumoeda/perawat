<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_legalitas', function (Blueprint $table) {
            $table->string('str_document_path')->nullable()->after('nomor_str');
            $table->string('izin_luar_negeri_document_path')->nullable()->after('nomor_izin_luar_negeri');
        });
    }

    public function down(): void
    {
        Schema::table('data_legalitas', function (Blueprint $table) {
            $table->dropColumn(['str_document_path', 'izin_luar_negeri_document_path']);
        });
    }
};
