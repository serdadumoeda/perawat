<?php

namespace App\Models;

use App\Traits\HasPerawatNik;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SertifikasiKompetensi extends Model
{
    use HasFactory, Uuids, HasPerawatNik;

    public const VERIF_PENDING = 'pending';
    public const VERIF_VERIFIED = 'verified';
    public const VERIF_REJECTED = 'rejected';
    public const VERIF_VALID = 'valid';

    protected $table = 'sertifikasi_kompetensi';

    protected $fillable = [
        'perawat_id', 'lembaga_pelatihan_id', 'nama_sertifikat', 'nomor_sertifikat', 'tanggal_terbit', 
        'tanggal_kadaluarsa', 'status_verifikasi', 'file_sertifikat', 'jumlah_jam', 'nilai_kompetensi', 
        'pelatihan_id', 'perawat_nik', 'source_system', 'source_actor', 'verified_by', 'verified_at', 
        'rejected_reason'
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'tanggal_kadaluarsa' => 'date',
        'nilai_kompetensi' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->status_verifikasi ??= self::VERIF_PENDING;
            $model->source_system ??= 'lembaga_portal';
            $model->source_actor ??= 'lembaga';
        });
    }

    public function perawat()
    {
        return $this->belongsTo(Perawat::class, 'perawat_nik', 'nik');
    }

    public function lembagaPelatihan()
    {
        return $this->belongsTo(LembagaPelatihan::class);
    }

    public function pelatihan()
    {
        return $this->belongsTo(Pelatihan::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public static function verifiedStatuses(): array
    {
        return [
            self::VERIF_VERIFIED,
            self::VERIF_VALID,
        ];
    }
}
