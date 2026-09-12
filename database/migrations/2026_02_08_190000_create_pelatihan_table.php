<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelatihan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lembaga_pelatihan_id')->constrained('lembaga_pelatihan')->cascadeOnDelete();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('jumlah_jam')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('lokasi')->nullable();
            $table->unsignedInteger('kuota')->nullable();
            $table->string('status')->default('dibuka');
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelatihan');
    }
};
