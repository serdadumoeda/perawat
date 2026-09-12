<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regulator_organisasi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode')->nullable()->unique();
            $table->string('nama');
            $table->string('tingkat')->nullable();
            $table->string('wilayah')->nullable();
            $table->text('alamat')->nullable();
            $table->string('email')->nullable();
            $table->string('telepon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('tingkat');
            $table->index('is_active');
        });

        Schema::table('data_legalitas', function (Blueprint $table) {
            $table->foreignUuid('regulator_organisasi_id')
                ->nullable()
                ->after('source_actor')
                ->constrained('regulator_organisasi')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('data_legalitas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('regulator_organisasi_id');
        });

        Schema::dropIfExists('regulator_organisasi');
    }
};
