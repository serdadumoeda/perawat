<?php

namespace App\Models;

use App\Traits\HasPerawatNik;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPekerjaanSync extends Model
{
    use HasFactory, Uuids, HasPerawatNik;

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    protected $table = 'riwayat_pekerjaan_syncs';

    protected $guarded = [];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'payload' => 'array',
    ];

    public function perawat()
    {
        return $this->belongsTo(Perawat::class, 'perawat_nik', 'nik');
    }

    public function mitraPengguna()
    {
        return $this->belongsTo(MitraPengguna::class);
    }

    public function riwayatPekerjaan()
    {
        return $this->belongsTo(RiwayatPekerjaan::class);
    }
}
