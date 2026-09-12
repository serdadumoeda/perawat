<?php

namespace App\Models;

use App\Traits\HasPerawatNik;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StrSyncRequest extends Model
{
    use HasFactory, Uuids, HasPerawatNik;

    public const STATUS_PENDING = 'pending';
    public const STATUS_SYNCED = 'synced';
    public const STATUS_REJECTED = 'rejected';

    protected $table = 'str_sync_requests';

    protected $guarded = [];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function perawat()
    {
        return $this->belongsTo(Perawat::class, 'perawat_nik', 'nik');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
