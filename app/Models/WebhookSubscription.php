<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookSubscription extends Model
{
    use HasFactory, Uuids;

    public const EVENT_LEGALITAS_VERIFIED = 'legalitas.data.verified';
    public const EVENT_SERTIFIKASI_VERIFIED = 'sertifikasi.verified';
    public const EVENT_RIWAYAT_PEKERJAAN_VERIFIED = 'riwayat_pekerjaan.verified';

    public const EVENTS = [
        self::EVENT_LEGALITAS_VERIFIED,
        self::EVENT_SERTIFIKASI_VERIFIED,
        self::EVENT_RIWAYAT_PEKERJAAN_VERIFIED,
    ];

    protected $guarded = [];

    protected $casts = [
        'events' => 'array',
        'signing_secret' => 'encrypted',
        'is_active' => 'boolean',
        'last_sent_at' => 'datetime',
    ];

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function listensTo(string $event): bool
    {
        $events = $this->events ?? [];

        return in_array('*', $events, true) || in_array($event, $events, true);
    }
}
