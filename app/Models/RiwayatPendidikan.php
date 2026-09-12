<?php

namespace App\Models;

use App\Traits\HasPerawatNik;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPendidikan extends Model
{
    use HasFactory, Uuids, HasPerawatNik;

    public const VERIF_PENDING = 'pending';
    public const VERIF_VERIFIED = 'verified';
    public const VERIF_REJECTED = 'rejected';

    protected $table = 'riwayat_pendidikan';

    protected $fillable = [
        'perawat_id', 'institusi_pendidikan_id', 'nim', 'jenjang', 'program_studi', 'nomor_ijazah', 
        'tanggal_ijazah', 'tahun_masuk', 'tahun_lulus', 'ipk', 'transkrip_path', 'status_verifikasi', 
        'ijazah_path', 'perawat_nik', 'source_system', 'source_actor', 'verified_by', 'verified_at', 
        'rejected_reason'
    ];

    protected $casts = [
        'tanggal_ijazah' => 'date',
        'ipk' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->status_verifikasi ??= self::VERIF_VERIFIED;
            $model->source_system ??= 'kampus_portal';
            $model->source_actor ??= 'kampus';
        });
    }

    public function perawat()
    {
        return $this->belongsTo(Perawat::class, 'perawat_nik', 'nik');
    }

    public function institusiPendidikan()
    {
        return $this->belongsTo(InstitusiPendidikan::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public static function verifiedStatuses(): array
    {
        return [self::VERIF_VERIFIED];
    }
}
