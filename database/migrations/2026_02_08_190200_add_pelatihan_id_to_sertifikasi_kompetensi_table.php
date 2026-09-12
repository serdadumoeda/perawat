<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sertifikasi_kompetensi', function (Blueprint $table) {
            $table->foreignUuid('pelatihan_id')->nullable()->after('lembaga_pelatihan_id')->constrained('pelatihan')->nullOnDelete();
            $table->index('pelatihan_id');
        });
    }

    public function down(): void
    {
        Schema::table('sertifikasi_kompetensi', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pelatihan_id');
        });
    }
};
