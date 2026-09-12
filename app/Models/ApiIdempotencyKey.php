<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiIdempotencyKey extends Model
{
    use HasFactory, Uuids;

    protected $table = 'api_idempotency_keys';

    protected $guarded = [];

    protected $casts = [
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }
}

