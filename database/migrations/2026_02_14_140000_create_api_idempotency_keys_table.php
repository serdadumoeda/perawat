<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_idempotency_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('api_key_id')->constrained('api_keys')->cascadeOnDelete();
            $table->string('method', 10);
            $table->string('endpoint', 255);
            $table->string('idempotency_key', 120);
            $table->string('request_hash', 64);
            $table->unsignedSmallInteger('response_status_code')->nullable();
            $table->longText('response_body')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();

            $table->unique(
                ['api_key_id', 'method', 'endpoint', 'idempotency_key'],
                'api_idempotency_unique'
            );
            $table->index(['api_key_id', 'created_at'], 'api_idempotency_api_key_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_idempotency_keys');
    }
};

