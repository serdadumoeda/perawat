<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('perawat_id')->constrained('perawat')->cascadeOnDelete();
            $table->string('perawat_nik', 16);
            $table->string('category', 50)->index();
            $table->string('event_key', 120)->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 80)->nullable();
            $table->string('source_system', 120)->nullable();
            $table->string('source_actor', 80)->nullable();
            $table->foreignUuid('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name')->nullable();
            $table->string('actor_role', 50)->nullable();
            $table->string('subject_type')->nullable()->index();
            $table->uuid('subject_id')->nullable()->index();
            $table->json('payload')->nullable();
            $table->timestamp('happened_at')->index();
            $table->char('fingerprint', 64)->unique();
            $table->timestamps();

            $table->foreign('perawat_nik')
                ->references('nik')
                ->on('perawat')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_events');
    }
};
