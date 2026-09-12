<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perawat extends Model
{
    use HasFactory, Uuids;

    public const KESIAPAN_DRAFT = 'draft';
    public const KESIAPAN_READY = 'ready_to_hire';

    public const KETENAGAKERJAAN_FRESH = 'fresh_graduate';
    public const KETENAGAKERJAAN_BEKERJA = 'bekerja';
    public const KETENAGAKERJAAN_BERPENGALAMAN = 'berpengalaman';

    public const KETERSEDIAAN_AVAILABLE = 'available';
    public const KETERSEDIAAN_OPEN = 'open_to_offer';
    public const KETERSEDIAAN_UNAVAILABLE = 'not_available';

    protected $table = 'perawat';

    protected $fillable = [
        'nik', 'nim', 'nama', 'email', 'no_hp', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'alamat', 
        'status_kesiapan', 'status_ketenagakerjaan', 'kota', 'provinsi', 'status_ketersediaan', 
        'public_profile_enabled', 'public_profile_token'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'public_profile_enabled' => 'boolean',
    ];

    public function sertifikasi()
    {
        return $this->hasMany(SertifikasiKompetensi::class, 'perawat_nik', 'nik');
    }

    public function riwayatPendidikan()
    {
        return $this->hasMany(RiwayatPendidikan::class, 'perawat_nik', 'nik');
    }

    public function riwayatPekerjaan()
    {
        return $this->hasMany(RiwayatPekerjaan::class, 'perawat_nik', 'nik');
    }

    public function tracingAlumni()
    {
        return $this->hasMany(TracingAlumni::class, 'perawat_nik', 'nik');
    }

    public function dataLegalitas()
    {
        return $this->hasOne(DataLegalitas::class, 'perawat_nik', 'nik');
    }

    public function pelatihanPeserta()
    {
        return $this->hasMany(PelatihanPeserta::class, 'perawat_nik', 'nik');
    }

    public function rekrutmen()
    {
        return $this->hasMany(Rekrutmen::class, 'perawat_nik', 'nik');
    }

    public function lamaran()
    {
        return $this->hasMany(Lamaran::class, 'perawat_nik', 'nik');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'perawat_nik', 'nik');
    }

    public function careerEvents()
    {
        return $this->hasMany(CareerEvent::class, 'perawat_nik', 'nik')
            ->orderByDesc('happened_at')
            ->orderByDesc('created_at');
    }

    public function isVerifiedExperience(): bool
    {
        return $this->status_ketenagakerjaan === self::KETENAGAKERJAAN_BERPENGALAMAN;
    }

    public function getKesiapanLabelAttribute(): string
    {
        return match ($this->status_kesiapan) {
            self::KESIAPAN_DRAFT => 'Draft (Belum Lengkap)',
            self::KESIAPAN_READY => 'Siap Kerja (Ready to Hire)',
            default => $this->status_kesiapan ?? '-',
        };
    }

    public function getKetenagakerjaanLabelAttribute(): string
    {
        return match ($this->status_ketenagakerjaan) {
            self::KETENAGAKERJAAN_FRESH => 'Lulusan Baru (Fresh Graduate)',
            self::KETENAGAKERJAAN_BEKERJA => 'Sedang Bekerja',
            self::KETENAGAKERJAAN_BERPENGALAMAN => 'Berpengalaman',
            default => $this->status_ketenagakerjaan ?? '-',
        };
    }

    public function getKetersediaanLabelAttribute(): string
    {
        return match ($this->status_ketersediaan) {
            self::KETERSEDIAAN_AVAILABLE => 'Sangat Tersedia',
            self::KETERSEDIAAN_OPEN => 'Terbuka Untuk Penawaran',
            self::KETERSEDIAAN_UNAVAILABLE => 'Tidak Tersedia',
            default => $this->status_ketersediaan ?? '-',
        };
    }
}
