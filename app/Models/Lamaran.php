<?php

namespace App\Models;

use App\Traits\HasPerawatNik;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lamaran extends Model
{
    use HasFactory, Uuids, HasPerawatNik;

    public const STATUS_DIAJUKAN = 'diajukan';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_DITERIMA = 'diterima';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    protected $table = 'lamaran';

    protected $fillable = [
        'perawat_id', 'lowongan_id', 'mitra_pengguna_id', 'status', 'catatan', 'perawat_nik'
    ];

    public function perawat()
    {
        return $this->belongsTo(Perawat::class, 'perawat_nik', 'nik');
    }

    public function lowongan()
    {
        return $this->belongsTo(Lowongan::class);
    }

    public function mitraPengguna()
    {
        return $this->belongsTo(MitraPengguna::class);
    }
}
