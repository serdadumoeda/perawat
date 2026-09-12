<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoScenarioEvent extends Model
{
    use HasFactory, Uuids;

    protected $table = 'demo_scenario_events';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'happened_at' => 'datetime',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(DemoScenarioRun::class, 'demo_scenario_run_id');
    }
}

