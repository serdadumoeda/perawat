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
        Schema::create('riwayat_pendidikan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('perawat_id')->constrained('perawat')->cascadeOnDelete();
            $table->foreignUuid('institusi_pendidikan_id')->constrained('institusi_pendidikan')->cascadeOnDelete();
            $table->string('jenjang')->nullable();
            $table->string('program_studi')->nullable();
            $table->string('nomor_ijazah')->nullable();
            $table->date('tanggal_ijazah')->nullable();
            $table->unsignedSmallInteger('tahun_masuk')->nullable();
            $table->unsignedSmallInteger('tahun_lulus')->nullable();
            $table->decimal('ipk', 4, 2)->nullable();
            $table->string('transkrip_path')->nullable();
            $table->string('status_verifikasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_pendidikan');
    }
};
