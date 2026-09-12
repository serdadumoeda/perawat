<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LembagaPelatihan extends Model
{
    use HasFactory, Uuids;

    protected $table = 'lembaga_pelatihan';

    protected $guarded = [];

    public function sertifikasiKompetensi()
    {
        return $this->hasMany(SertifikasiKompetensi::class);
    }

    public function pelatihan()
    {
        return $this->hasMany(Pelatihan::class);
    }

    public function users()
    {
        return $this->morphToMany(User::class, 'organisasiable', 'organisasi_user');
    }

    public function apiKeys()
    {
        return $this->morphMany(ApiKey::class, 'owner');
    }
}
