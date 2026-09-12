<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sertifikasi_kompetensi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('perawat_id')->constrained('perawat')->cascadeOnDelete();
            $table->foreignUuid('lembaga_pelatihan_id')->constrained('lembaga_pelatihan')->cascadeOnDelete();
            $table->string('nama_sertifikat');
            $table->string('nomor_sertifikat')->nullable();
            $table->date('tanggal_terbit')->nullable();
            $table->date('tanggal_kadaluarsa')->nullable();
            $table->string('status_verifikasi')->nullable();
            $table->string('file_sertifikat')->nullable();
            $table->timestamps();

            $table->index('nama_sertifikat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sertifikasi_kompetensi');
    }
};
