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
        Schema::create('lamaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('perawat_id')->constrained('perawat')->cascadeOnDelete();
            $table->foreignUuid('lowongan_id')->constrained('lowongan')->cascadeOnDelete();
            $table->foreignUuid('mitra_pengguna_id')->constrained('mitra_pengguna')->cascadeOnDelete();
            $table->string('status')->default('diajukan');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['perawat_id', 'lowongan_id'], 'lamaran_unique_pair');
            $table->index(['mitra_pengguna_id', 'status']);
            $table->index('lowongan_id');
            $table->index('perawat_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamaran');
    }
};
