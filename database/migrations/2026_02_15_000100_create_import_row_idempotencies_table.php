<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_row_idempotencies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('module', 50);
            $table->string('idempotency_key', 191);
            $table->string('row_hash', 64);
            $table->foreignUuid('last_import_batch_id')->nullable()->constrained('import_batches')->nullOnDelete();
            $table->timestamp('last_imported_at')->nullable();
            $table->timestamps();

            $table->unique(['module', 'idempotency_key'], 'import_row_idempotency_unique');
            $table->index(['module', 'last_imported_at'], 'import_row_idempotency_module_imported_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_row_idempotencies');
    }
};
