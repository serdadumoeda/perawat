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
        Schema::create('perawat', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nik')->unique();
            $table->string('nama');
            $table->string('email')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('status_kesiapan')->default('draft');
            $table->string('status_ketenagakerjaan')->default('fresh_graduate');
            $table->timestamps();

            $table->index('status_kesiapan');
            $table->index('status_ketenagakerjaan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perawat');
    }
};
