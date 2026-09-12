<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perawat', function (Blueprint $table) {
            $table->string('nim', 50)->nullable()->index()->after('nik');
        });

        Schema::table('riwayat_pendidikan', function (Blueprint $table) {
            $table->string('nim', 50)->nullable()->index()->after('institusi_pendidikan_id');
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_pendidikan', function (Blueprint $table) {
            $table->dropIndex(['nim']);
            $table->dropColumn('nim');
        });

        Schema::table('perawat', function (Blueprint $table) {
            $table->dropIndex(['nim']);
            $table->dropColumn('nim');
        });
    }
};
