<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_scenario_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('scenario_name')->default('single_perawat_living_cv');
            $table->string('scenario_key')->unique();
            $table->foreignUuid('perawat_id')->nullable()->constrained('perawat')->nullOnDelete();
            $table->string('perawat_nik')->nullable();
            $table->string('status')->default('running');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->json('summary')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('perawat_nik');
            $table->index('status');
            $table->index(['scenario_name', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_scenario_runs');
    }
};
