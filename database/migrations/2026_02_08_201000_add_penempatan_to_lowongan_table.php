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
        Schema::table('lowongan', function (Blueprint $table) {
            $table->string('tipe_penempatan')->default('domestik')->after('status');
            $table->string('negara_penempatan')->nullable()->after('tipe_penempatan');
            $table->string('provinsi_penempatan')->nullable()->after('negara_penempatan');
            $table->string('kota_penempatan')->nullable()->after('provinsi_penempatan');

            $table->index('tipe_penempatan');
            $table->index('negara_penempatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lowongan', function (Blueprint $table) {
            $table->dropIndex(['tipe_penempatan']);
            $table->dropIndex(['negara_penempatan']);
            $table->dropColumn(['tipe_penempatan', 'negara_penempatan', 'provinsi_penempatan', 'kota_penempatan']);
        });
    }
};
