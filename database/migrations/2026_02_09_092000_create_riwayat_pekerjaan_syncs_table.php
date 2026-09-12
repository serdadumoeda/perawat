<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_pekerjaan_syncs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('perawat_id')->nullable()->constrained('perawat')->nullOnDelete();
            $table->foreignUuid('mitra_pengguna_id')->nullable()->constrained('mitra_pengguna')->nullOnDelete();
            $table->foreignUuid('riwayat_pekerjaan_id')->nullable()->constrained('riwayat_pekerjaan')->nullOnDelete();
            $table->string('source_system');
            $table->string('external_id');
            $table->string('status')->default('pending');
            $table->timestamp('last_synced_at')->nullable();
            $table->json('payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['source_system', 'external_id']);
            $table->index(['perawat_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_pekerjaan_syncs');
    }
};
