<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webhook_deliveries', function (Blueprint $table) {
            $table->unsignedSmallInteger('attempt_number')->default(1)->after('event');
            $table->unsignedSmallInteger('max_attempts')->default(1)->after('attempt_number');
            $table->timestamp('next_retry_at')->nullable()->after('delivered_at');
            $table->boolean('is_dead_letter')->default(false)->after('is_success')->index();

            $table->index(['webhook_subscription_id', 'event', 'attempt_number'], 'webhook_delivery_attempt_idx');
        });
    }

    public function down(): void
    {
        Schema::table('webhook_deliveries', function (Blueprint $table) {
            $table->dropIndex('webhook_delivery_attempt_idx');
            $table->dropIndex(['is_dead_letter']);
            $table->dropColumn(['attempt_number', 'max_attempts', 'next_retry_at', 'is_dead_letter']);
        });
    }
};

