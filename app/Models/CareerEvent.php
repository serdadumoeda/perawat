<?php

namespace App\Models;

use App\Traits\HasPerawatNik;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareerEvent extends Model
{
    use HasFactory, Uuids, HasPerawatNik;

    protected $table = 'career_events';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'happened_at' => 'datetime',
    ];

    public function perawat()
    {
        return $this->belongsTo(Perawat::class, 'perawat_nik', 'nik');
    }

    public function actorUser()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
