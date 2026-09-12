<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    use HasFactory, Uuids;

    protected $guarded = [];

    protected $casts = [
        'request_payload' => 'array',
        'is_success' => 'boolean',
        'is_dead_letter' => 'boolean',
        'delivered_at' => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    public function webhookSubscription(): BelongsTo
    {
        return $this->belongsTo(WebhookSubscription::class);
    }
}
