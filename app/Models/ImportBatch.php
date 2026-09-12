<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportBatch extends Model
{
    use HasFactory, Uuids;

    protected $table = 'import_batches';

    protected $fillable = [
        'user_id', 'module', 'status', 'original_filename', 'field_mapping',
        'context', 'total_rows', 'processed_rows', 'success_rows', 'failed_rows',
        'created_rows', 'updated_rows', 'duplicate_rows', 'error_count', 'report_path',
    ];

    protected $casts = [
        'field_mapping' => 'array',
        'context' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

