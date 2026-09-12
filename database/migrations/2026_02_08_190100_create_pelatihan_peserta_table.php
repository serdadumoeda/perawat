<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelatihan_peserta', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pelatihan_id')->constrained('pelatihan')->cascadeOnDelete();
            $table->foreignUuid('perawat_id')->constrained('perawat')->cascadeOnDelete();
            $table->string('status')->default('terdaftar');
            $table->date('tanggal_daftar')->nullable();
            $table->date('tanggal_lulus')->nullable();
            $table->decimal('nilai_kompetensi', 5, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['pelatihan_id', 'perawat_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelatihan_peserta');
    }
};
