<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experience_sync_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mitra_pengguna_id')->constrained('mitra_pengguna')->cascadeOnDelete();
            $table->string('base_url')->nullable();
            $table->string('api_key')->nullable();
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('sync_mode')->default('pull');
            $table->boolean('is_active')->default(false);
            $table->text('notes')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique('mitra_pengguna_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experience_sync_settings');
    }
};
