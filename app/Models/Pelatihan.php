<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelatihan extends Model
{
    use HasFactory, Uuids;

    public const STATUS_DIBUKA = 'dibuka';
    public const STATUS_DITUTUP = 'ditutup';

    protected $table = 'pelatihan';

    protected $guarded = [];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function lembagaPelatihan()
    {
        return $this->belongsTo(LembagaPelatihan::class);
    }

    public function peserta()
    {
        return $this->hasMany(PelatihanPeserta::class);
    }
}
