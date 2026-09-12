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
        Schema::create('data_legalitas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('perawat_id')->constrained('perawat')->cascadeOnDelete();
            $table->string('nomor_str')->nullable();
            $table->string('status_str')->nullable();
            $table->date('str_berlaku_mulai')->nullable();
            $table->date('str_berlaku_sampai')->nullable();
            $table->string('nomor_izin_luar_negeri')->nullable();
            $table->string('status_izin_luar_negeri')->nullable();
            $table->string('sumber_validasi')->nullable();
            $table->timestamp('terakhir_validasi_at')->nullable();
            $table->timestamps();

            $table->unique('perawat_id');
            $table->unique('nomor_str');
            $table->index('status_str');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_legalitas');
    }
};
