<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lowongan extends Model
{
    use HasFactory, Uuids;

    public const PENEMPATAN_DOMESTIK = 'domestik';
    public const PENEMPATAN_LUAR_NEGERI = 'luar_negeri';

    public const PENEMPATAN_OPTIONS = [
        self::PENEMPATAN_DOMESTIK,
        self::PENEMPATAN_LUAR_NEGERI,
    ];

    protected $table = 'lowongan';

    protected $fillable = [
        'mitra_pengguna_id', 'judul', 'deskripsi', 'kriteria', 'status', 'published_at', 
        'tipe_penempatan', 'negara_penempatan', 'provinsi_penempatan', 'kota_penempatan'
    ];

    protected $casts = [
        'kriteria' => 'array',
        'published_at' => 'datetime',
    ];

    public function mitraPengguna()
    {
        return $this->belongsTo(MitraPengguna::class);
    }

    public function rekrutmen()
    {
        return $this->hasMany(Rekrutmen::class);
    }

    public function lamaran()
    {
        return $this->hasMany(Lamaran::class);
    }
}
