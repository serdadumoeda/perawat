<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_scenario_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('demo_scenario_run_id')->constrained('demo_scenario_runs')->cascadeOnDelete();
            $table->unsignedSmallInteger('step_order');
            $table->string('step_key');
            $table->string('actor_role');
            $table->string('actor_label');
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('happened_at')->nullable();
            $table->timestamps();

            $table->unique(['demo_scenario_run_id', 'step_order'], 'demo_scenario_events_run_step_unique');
            $table->index(['step_key', 'happened_at'], 'demo_scenario_events_step_happened_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_scenario_events');
    }
};

