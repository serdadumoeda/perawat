<?php

namespace App\Models;

use App\Traits\HasPerawatNik;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataLegalitas extends Model
{
    use HasFactory, Uuids, HasPerawatNik;

    public const STATUS_STR_AKTIF = 'aktif';
    public const STATUS_STR_HOLD = 'hold';
    public const STATUS_STR_EXPIRED = 'expired';
    public const DOC_STATUS_PENDING = 'pending';
    public const DOC_STATUS_APPROVED = 'approved';
    public const DOC_STATUS_REJECTED = 'rejected';
    public const DATA_STATUS_DRAFT = 'draft';
    public const DATA_STATUS_SUBMITTED = 'submitted';
    public const DATA_STATUS_PENDING_SYNC = 'pending_sync';
    public const DATA_STATUS_VERIFIED = 'verified';
    public const DATA_STATUS_EXPIRED = 'expired';
    public const DATA_STATUS_REJECTED = 'rejected';

    protected $table = 'data_legalitas';

    protected $fillable = [
        'perawat_id', 'nomor_str', 'status_str', 'str_berlaku_mulai', 'str_berlaku_sampai', 
        'nomor_izin_luar_negeri', 'status_izin_luar_negeri', 'sumber_validasi', 'terakhir_validasi_at', 
        'nomor_sipp', 'str_document_path', 'izin_luar_negeri_document_path', 'str_document_status', 
        'str_document_reviewed_by', 'str_document_reviewed_at', 'str_document_note', 
        'izin_luar_negeri_document_status', 'izin_luar_negeri_document_reviewed_by', 
        'izin_luar_negeri_document_reviewed_at', 'izin_luar_negeri_document_note', 'perawat_nik', 
        'data_status', 'data_submitted_at', 'data_reviewed_by', 'data_reviewed_at', 
        'data_rejection_note', 'source_verification', 'source_system', 'source_actor', 
        'regulator_organisasi_id'
    ];

    protected $casts = [
        'str_berlaku_mulai' => 'date',
        'str_berlaku_sampai' => 'date',
        'terakhir_validasi_at' => 'datetime',
        'str_document_reviewed_at' => 'datetime',
        'izin_luar_negeri_document_reviewed_at' => 'datetime',
        'data_submitted_at' => 'datetime',
        'data_reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        /**
         * Hooks ini otomatis mengisi data_status awal berdasarkan status_str jika tidak didefinisikan secara eksplisit.
         * Ini terutama berguna untuk menjaga kompatibilitas data seeder dan database lama (tests).
         */
        static::creating(function (self $model): void {
            $model->source_system ??= 'regulator_portal';
            $model->source_actor ??= 'regulator';
            if ($model->data_status === null) {
                if ($model->status_str === self::STATUS_STR_AKTIF) {
                    $model->data_status = self::DATA_STATUS_VERIFIED;
                } elseif ($model->status_str === self::STATUS_STR_EXPIRED) {
                    $model->data_status = self::DATA_STATUS_EXPIRED;
                } else {
                    $model->data_status = self::DATA_STATUS_DRAFT;
                }
            }
        });

        /**
         * PENTING: Hook updating ini secara otomatis menyelaraskan data_status ketika status_str diubah.
         * Hanya berjalan ketika status_str berubah secara independen tanpa menyertakan perubahan data_status
         * (misal pembaruan manual di luar service regulator). Operasi sinkronisasi API/Regulator yang mengubah
         * kedua field secara eksplisit tidak akan terpengaruh.
         */
        static::updating(function (self $model): void {
            if ($model->isDirty('status_str') && !$model->isDirty('data_status')) {
                if ($model->status_str === self::STATUS_STR_AKTIF) {
                    $model->data_status = self::DATA_STATUS_VERIFIED;
                } elseif ($model->status_str === self::STATUS_STR_EXPIRED) {
                    $model->data_status = self::DATA_STATUS_EXPIRED;
                }
            }
        });
    }

    public static function docStatusOptions(): array
    {
        return [
            self::DOC_STATUS_PENDING,
            self::DOC_STATUS_APPROVED,
            self::DOC_STATUS_REJECTED,
        ];
    }

    public static function dataStatusOptions(): array
    {
        return [
            self::DATA_STATUS_DRAFT,
            self::DATA_STATUS_SUBMITTED,
            self::DATA_STATUS_PENDING_SYNC,
            self::DATA_STATUS_VERIFIED,
            self::DATA_STATUS_EXPIRED,
            self::DATA_STATUS_REJECTED,
        ];
    }

    public static function normalizeDocStatus(?string $status): string
    {
        return $status ?: self::DOC_STATUS_PENDING;
    }

    public static function normalizeDataStatus(?string $status): string
    {
        return $status ?: self::DATA_STATUS_DRAFT;
    }

    public function perawat()
    {
        return $this->belongsTo(Perawat::class, 'perawat_nik', 'nik');
    }

    public function dataReviewedBy()
    {
        return $this->belongsTo(User::class, 'data_reviewed_by');
    }

    public function strDocumentReviewedBy()
    {
        return $this->belongsTo(User::class, 'str_document_reviewed_by');
    }

    public function izinLuarNegeriDocumentReviewedBy()
    {
        return $this->belongsTo(User::class, 'izin_luar_negeri_document_reviewed_by');
    }

    public function regulatorOrganisasi()
    {
        return $this->belongsTo(RegulatorOrganisasi::class, 'regulator_organisasi_id');
    }
}
