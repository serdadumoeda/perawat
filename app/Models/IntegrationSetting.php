<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationSetting extends Model
{
    use HasFactory, Uuids;

    public const PROVIDER_KEMENKES = 'kemenkes';
    public const PROVIDER_KTKI = 'ktki';

    public const PROVIDERS = [
        self::PROVIDER_KEMENKES,
        self::PROVIDER_KTKI,
    ];

    protected $table = 'integration_settings';

    protected $guarded = [];

    protected $casts = [
        'api_key' => 'encrypted',
        'client_id' => 'encrypted',
        'client_secret' => 'encrypted',
        'username' => 'encrypted',
        'password' => 'encrypted',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];
}
