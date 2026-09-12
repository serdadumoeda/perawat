<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegulatorOrganisasi extends Model
{
    use HasFactory, Uuids;

    protected $table = 'regulator_organisasi';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->morphToMany(User::class, 'organisasiable', 'organisasi_user');
    }

    public function dataLegalitas()
    {
        return $this->hasMany(DataLegalitas::class, 'regulator_organisasi_id');
    }

    public function apiKeys()
    {
        return $this->morphMany(ApiKey::class, 'owner');
    }
}
