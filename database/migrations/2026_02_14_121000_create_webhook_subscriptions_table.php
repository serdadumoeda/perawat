<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('target_url');
            $table->json('events');
            $table->text('signing_secret')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_sent_at')->nullable();
            $table->unsignedInteger('consecutive_failures')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_subscriptions');
    }
};
