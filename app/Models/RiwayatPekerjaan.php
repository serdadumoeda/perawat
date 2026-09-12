<?php

namespace App\Models;

use App\Traits\HasPerawatNik;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPekerjaan extends Model
{
    use HasFactory, Uuids, HasPerawatNik;

    public const SOURCE_MITRA = 'mitra';
    public const SOURCE_PERAWAT = 'perawat';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_RESIGN = 'resign';

    public const VERIF_PENDING = 'pending';
    public const VERIF_VERIFIED = 'verified';
    public const VERIF_REJECTED = 'rejected';

    protected $table = 'riwayat_pekerjaan';

    protected $fillable = [
        'perawat_id', 'mitra_pengguna_id', 'posisi', 'unit', 'tanggal_mulai', 'tanggal_selesai', 
        'status_pekerjaan', 'keterangan', 'penilaian_kinerja', 'is_verified', 'verified_at', 
        'sumber_data', 'verifikasi_status', 'verified_by', 'kinerja_rating', 'kinerja_score', 
        'perawat_nik', 'source_system', 'source_actor', 'rejected_reason'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'kinerja_rating' => 'integer',
        'kinerja_score' => 'float',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $source = $model->sumber_data ?? self::SOURCE_MITRA;
            $model->source_actor ??= $source === self::SOURCE_PERAWAT ? 'perawat' : 'mitra';
            $model->source_system ??= $source === self::SOURCE_PERAWAT ? 'perawat_portal' : 'mitra_portal';
        });
    }

    public function perawat()
    {
        return $this->belongsTo(Perawat::class, 'perawat_nik', 'nik');
    }

    public function mitraPengguna()
    {
        return $this->belongsTo(MitraPengguna::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
