<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('webhook_subscription_id')->constrained('webhook_subscriptions')->cascadeOnDelete();
            $table->string('event', 120)->index();
            $table->string('payload_hash', 64);
            $table->unsignedSmallInteger('status_code')->nullable()->index();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->boolean('is_success')->default(false)->index();
            $table->text('error_message')->nullable();
            $table->timestamp('delivered_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};
