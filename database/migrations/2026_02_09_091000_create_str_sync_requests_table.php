<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('str_sync_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('perawat_id')->constrained('perawat')->cascadeOnDelete();
            $table->string('nomor_str');
            $table->string('status')->default('pending');
            $table->foreignUuid('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('requested_role')->nullable();
            $table->foreignUuid('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('catatan')->nullable();
            $table->text('response_note')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['perawat_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('str_sync_requests');
    }
};
