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
        Schema::create('rekrutmen', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mitra_pengguna_id')->constrained('mitra_pengguna')->cascadeOnDelete();
            $table->foreignUuid('perawat_id')->constrained('perawat')->cascadeOnDelete();
            $table->foreignUuid('lowongan_id')->nullable()->constrained('lowongan')->nullOnDelete();
            $table->string('status')->default('locked');
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('hired_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['perawat_id', 'status']);
            $table->unique(['mitra_pengguna_id', 'perawat_id'], 'rekrutmen_unique_pair');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekrutmen');
    }
};
