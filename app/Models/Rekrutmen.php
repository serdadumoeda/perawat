<?php

namespace App\Models;

use App\Traits\HasPerawatNik;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rekrutmen extends Model
{
    use HasFactory, Uuids, HasPerawatNik;

    public const STATUS_LOCKED = 'locked';
    public const STATUS_HIRED = 'hired';
    public const STATUS_RELEASED = 'released';

    protected $table = 'rekrutmen';

    protected $fillable = [
        'mitra_pengguna_id', 'perawat_id', 'lowongan_id', 'status', 'locked_at', 
        'hired_at', 'released_at', 'perawat_nik'
    ];

    protected $casts = [
        'locked_at' => 'datetime',
        'hired_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function mitraPengguna()
    {
        return $this->belongsTo(MitraPengguna::class);
    }

    public function perawat()
    {
        return $this->belongsTo(Perawat::class, 'perawat_nik', 'nik');
    }

    public function lowongan()
    {
        return $this->belongsTo(Lowongan::class);
    }
}
