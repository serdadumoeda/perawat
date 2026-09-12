<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organisasi_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('organisasiable_id');
            $table->string('organisasiable_type');
            $table->timestamps();

            $table->unique(['user_id', 'organisasiable_type', 'organisasiable_id'], 'organisasi_user_unique');
            $table->index(['organisasiable_type', 'organisasiable_id'], 'organisasi_user_organisasi_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisasi_user');
    }
};
