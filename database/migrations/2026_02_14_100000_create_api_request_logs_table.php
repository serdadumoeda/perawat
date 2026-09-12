<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('api_key_id')->nullable()->constrained('api_keys')->nullOnDelete();
            $table->string('method', 10);
            $table->string('endpoint', 255)->index();
            $table->string('payload_hash', 64)->nullable();
            $table->unsignedSmallInteger('status_code')->index();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['api_key_id', 'created_at']);
            $table->index(['status_code', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
    }
};

