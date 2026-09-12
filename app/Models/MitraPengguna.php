<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MitraPengguna extends Model
{
    use HasFactory, Uuids;

    protected $table = 'mitra_pengguna';

    protected $guarded = [];

    public function riwayatPekerjaan()
    {
        return $this->hasMany(RiwayatPekerjaan::class);
    }

    public function lowongan()
    {
        return $this->hasMany(Lowongan::class);
    }

    public function rekrutmen()
    {
        return $this->hasMany(Rekrutmen::class);
    }

    public function lamaran()
    {
        return $this->hasMany(Lamaran::class);
    }

    public function users()
    {
        return $this->morphToMany(User::class, 'organisasiable', 'organisasi_user');
    }
}
