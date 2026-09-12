<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experience_sync_settings', function (Blueprint $table) {
            $table->timestamp('last_failure_notified_at')->nullable()->after('last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('experience_sync_settings', function (Blueprint $table) {
            $table->dropColumn('last_failure_notified_at');
        });
    }
};
