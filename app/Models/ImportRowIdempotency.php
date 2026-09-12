<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRowIdempotency extends Model
{
    use HasFactory, Uuids;

    protected $table = 'import_row_idempotencies';

    protected $guarded = [];

    protected $casts = [
        'last_imported_at' => 'datetime',
    ];

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'last_import_batch_id');
    }
}
