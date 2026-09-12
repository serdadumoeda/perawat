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
        Schema::create('tracing_alumni', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('perawat_id')->constrained('perawat')->cascadeOnDelete();
            $table->foreignUuid('institusi_pendidikan_id')->constrained('institusi_pendidikan')->cascadeOnDelete();
            $table->string('status')->default('belum_bekerja');
            $table->string('instansi')->nullable();
            $table->string('posisi')->nullable();
            $table->string('kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['perawat_id', 'institusi_pendidikan_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracing_alumni');
    }
};
