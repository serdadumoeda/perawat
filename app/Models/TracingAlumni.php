<?php

namespace App\Models;

use App\Traits\HasPerawatNik;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TracingAlumni extends Model
{
    use HasFactory, Uuids, HasPerawatNik;

    public const STATUS_BELUM_BEKERJA = 'belum_bekerja';
    public const STATUS_BEKERJA = 'bekerja';
    public const STATUS_WIRAUSAHA = 'wirausaha';
    public const STATUS_LANJUT_STUDI = 'lanjut_studi';

    public const STATUSES = [
        self::STATUS_BELUM_BEKERJA,
        self::STATUS_BEKERJA,
        self::STATUS_WIRAUSAHA,
        self::STATUS_LANJUT_STUDI,
    ];

    protected $table = 'tracing_alumni';

    protected $fillable = [
        'perawat_nik', 'institusi_pendidikan_id', 'status', 'instansi',
        'posisi', 'kota', 'provinsi', 'tanggal_mulai', 'catatan', 'updated_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
    ];

    public function perawat()
    {
        return $this->belongsTo(Perawat::class, 'perawat_nik', 'nik');
    }

    public function institusiPendidikan()
    {
        return $this->belongsTo(InstitusiPendidikan::class);
    }
}
