<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DemoScenarioRun extends Model
{
    use HasFactory, Uuids;

    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $table = 'demo_scenario_runs';

    protected $guarded = [];

    protected $casts = [
        'summary' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function perawat(): BelongsTo
    {
        return $this->belongsTo(Perawat::class, 'perawat_nik', 'nik');
    }

    public function events(): HasMany
    {
        return $this->hasMany(DemoScenarioEvent::class)->orderBy('step_order');
    }
}

