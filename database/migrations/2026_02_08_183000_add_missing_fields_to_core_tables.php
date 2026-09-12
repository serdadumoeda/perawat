<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institusi_pendidikan', function (Blueprint $table) {
            $table->string('akreditasi')->nullable()->after('jenis');
        });

        Schema::table('lembaga_pelatihan', function (Blueprint $table) {
            $table->string('akreditasi')->nullable()->after('jenis');
        });

        Schema::table('mitra_pengguna', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('jenis');
            $table->string('wilayah_operasi')->nullable()->after('kategori');
        });

        Schema::table('perawat', function (Blueprint $table) {
            $table->string('status_ketersediaan')->default('available')->after('status_ketenagakerjaan');
            $table->index('status_ketersediaan');
        });

        Schema::table('riwayat_pendidikan', function (Blueprint $table) {
            $table->string('ijazah_path')->nullable()->after('transkrip_path');
        });

        Schema::table('sertifikasi_kompetensi', function (Blueprint $table) {
            $table->unsignedInteger('jumlah_jam')->nullable()->after('nama_sertifikat');
            $table->decimal('nilai_kompetensi', 5, 2)->nullable()->after('jumlah_jam');
        });

        Schema::table('data_legalitas', function (Blueprint $table) {
            $table->string('nomor_sipp')->nullable()->after('nomor_str');
        });
    }

    public function down(): void
    {
        Schema::table('institusi_pendidikan', function (Blueprint $table) {
            $table->dropColumn('akreditasi');
        });

        Schema::table('lembaga_pelatihan', function (Blueprint $table) {
            $table->dropColumn('akreditasi');
        });

        Schema::table('mitra_pengguna', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'wilayah_operasi']);
        });

        Schema::table('perawat', function (Blueprint $table) {
            $table->dropIndex(['status_ketersediaan']);
            $table->dropColumn('status_ketersediaan');
        });

        Schema::table('riwayat_pendidikan', function (Blueprint $table) {
            $table->dropColumn('ijazah_path');
        });

        Schema::table('sertifikasi_kompetensi', function (Blueprint $table) {
            $table->dropColumn(['jumlah_jam', 'nilai_kompetensi']);
        });

        Schema::table('data_legalitas', function (Blueprint $table) {
            $table->dropColumn('nomor_sipp');
        });
    }
};
