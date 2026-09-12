<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperienceSyncSetting extends Model
{
    use HasFactory, Uuids;

    public const MODE_PULL = 'pull';
    public const MODE_PUSH = 'push';
    public const MODE_BOTH = 'both';

    public const MODES = [
        self::MODE_PULL,
        self::MODE_PUSH,
        self::MODE_BOTH,
    ];

    protected $table = 'experience_sync_settings';

    protected $fillable = [
        'base_url', 'api_key', 'client_id', 'client_secret',
        'username', 'password', 'sync_mode', 'is_active', 'notes',
        'last_synced_at', 'last_failure_notified_at',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'client_id' => 'encrypted',
        'client_secret' => 'encrypted',
        'username' => 'encrypted',
        'password' => 'encrypted',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
        'last_failure_notified_at' => 'datetime',
    ];

    public function mitraPengguna()
    {
        return $this->belongsTo(MitraPengguna::class);
    }
}
