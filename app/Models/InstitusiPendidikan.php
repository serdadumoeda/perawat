<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitusiPendidikan extends Model
{
    use HasFactory, Uuids;

    protected $table = 'institusi_pendidikan';

    protected $guarded = [];

    public function riwayatPendidikan()
    {
        return $this->hasMany(RiwayatPendidikan::class);
    }

    public function tracingAlumni()
    {
        return $this->hasMany(TracingAlumni::class);
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
