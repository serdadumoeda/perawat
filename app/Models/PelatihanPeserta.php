<?php

namespace App\Models;

use App\Traits\HasPerawatNik;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelatihanPeserta extends Model
{
    use HasFactory, Uuids, HasPerawatNik;

    public const STATUS_TERDAFTAR = 'terdaftar';
    public const STATUS_LULUS = 'lulus';
    public const STATUS_TIDAK_LULUS = 'tidak_lulus';
    public const STATUS_BATAL = 'batal';

    protected $table = 'pelatihan_peserta';

    protected $guarded = [];

    protected $casts = [
        'tanggal_daftar' => 'date',
        'tanggal_lulus' => 'date',
        'nilai_kompetensi' => 'decimal:2',
    ];

    public function pelatihan()
    {
        return $this->belongsTo(Pelatihan::class);
    }

    public function perawat()
    {
        return $this->belongsTo(Perawat::class, 'perawat_nik', 'nik');
    }
}
