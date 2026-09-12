<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->boolean('require_signature')->default(false)->after('scopes');
            $table->text('signature_secret')->nullable()->after('require_signature');
            $table->timestamp('signature_last_rotated_at')->nullable()->after('signature_secret');
        });
    }

    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn([
                'require_signature',
                'signature_secret',
                'signature_last_rotated_at',
            ]);
        });
    }
};
