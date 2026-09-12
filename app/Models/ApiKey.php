<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiKey extends Model
{
    use HasFactory, Uuids;

    protected $table = 'api_keys';

    protected $guarded = [];

    protected $casts = [
        'scopes' => 'array',
        'require_signature' => 'boolean',
        'signature_secret' => 'encrypted',
        'signature_last_rotated_at' => 'datetime',
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->morphTo();
    }

    public function requestLogs(): HasMany
    {
        return $this->hasMany(ApiRequestLog::class);
    }

    public function isRevoked(): bool
    {
        return (bool) $this->revoked_at;
    }
}
