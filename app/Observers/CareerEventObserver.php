<?php

namespace App\Observers;

use App\Models\DataLegalitas;
use App\Models\RiwayatPekerjaan;
use App\Models\RiwayatPendidikan;
use App\Models\SertifikasiKompetensi;
use App\Services\CareerEventService;
use Illuminate\Database\Eloquent\Model;

class CareerEventObserver
{
    public function __construct(private readonly CareerEventService $careerEventService)
    {
    }

    public function created(Model $model): void
    {
        if ($model instanceof RiwayatPendidikan) {
            $this->recordRiwayatPendidikanCreated($model);
            return;
        }

        if ($model instanceof SertifikasiKompetensi) {
            $this->recordSertifikasiCreated($model);
            return;
        }

        if ($model instanceof DataLegalitas) {
            $this->recordLegalitasStatus($model);
            return;
        }

        if ($model instanceof RiwayatPekerjaan) {
            $this->recordRiwayatPekerjaanCreated($model);
        }
    }

    public function updated(Model $model): void
    {
        if ($model instanceof RiwayatPendidikan) {
            $this->recordRiwayatPendidikanUpdated($model);
            return;
        }

        if ($model instanceof SertifikasiKompetensi) {
            $this->recordSertifikasiUpdated($model);
            return;
        }

        if ($model instanceof DataLegalitas) {
            $this->recordLegalitasUpdated($model);
            return;
        }

        if ($model instanceof RiwayatPekerjaan) {
            $this->recordRiwayatPekerjaanUpdated($model);
        }
    }

    private function recordRiwayatPendidikanCreated(RiwayatPendidikan $pendidikan): void
    {
        $this->careerEventService->record(
            $pendidikan->perawat_nik,
            'lulusan.recorded',
            'pendidikan',
            'Data lulusan ditambahkan',
            [
                'status' => $pendidikan->status_verifikasi,
                'source_system' => $pendidikan->source_system,
                'source_actor' => $pendidikan->source_actor,
                'actor_user_id' => $pendidikan->verified_by,
                'subject' => $pendidikan,
                'happened_at' => $pendidikan->verified_at ?? $pendidikan->created_at,
                'state' => [
                    'status_verifikasi' => $pendidikan->status_verifikasi,
                    'institusi_pendidikan_id' => $pendidikan->institusi_pendidikan_id,
                    'nomor_ijazah' => $pendidikan->nomor_ijazah,
                ],
                'payload' => [
                    'institusi_pendidikan_id' => $pendidikan->institusi_pendidikan_id,
                    'jenjang' => $pendidikan->jenjang,
                    'program_studi' => $pendidikan->program_studi,
                    'tahun_lulus' => $pendidikan->tahun_lulus,
                    'ipk' => $pendidikan->ipk,
                ],
            ]
        );
    }

    private function recordRiwayatPendidikanUpdated(RiwayatPendidikan $pendidikan): void
    {
        if (!$this->modelChanged($pendidikan, [
            'status_verifikasi',
            'jenjang',
            'program_studi',
            'nomor_ijazah',
            'tanggal_ijazah',
            'tahun_masuk',
            'tahun_lulus',
            'ipk',
        ])) {
            return;
        }

        $eventKey = $pendidikan->wasChanged('status_verifikasi')
            ? 'lulusan.status_changed'
            : 'lulusan.updated';

        $title = $pendidikan->wasChanged('status_verifikasi')
            ? 'Status verifikasi pendidikan diperbarui'
            : 'Data lulusan diperbarui';

        $this->careerEventService->record(
            $pendidikan->perawat_nik,
            $eventKey,
            'pendidikan',
            $title,
            [
                'status' => $pendidikan->status_verifikasi,
                'source_system' => $pendidikan->source_system,
                'source_actor' => $pendidikan->source_actor,
                'actor_user_id' => $pendidikan->verified_by,
                'subject' => $pendidikan,
                'happened_at' => now(),
                'state' => [
                    'status_verifikasi' => $pendidikan->status_verifikasi,
                    'source_system' => $pendidikan->source_system,
                ],
                'payload' => [
                    'institusi_pendidikan_id' => $pendidikan->institusi_pendidikan_id,
                    'jenjang' => $pendidikan->jenjang,
                    'program_studi' => $pendidikan->program_studi,
                    'tahun_lulus' => $pendidikan->tahun_lulus,
                ],
            ]
        );
    }

    private function recordSertifikasiCreated(SertifikasiKompetensi $sertifikasi): void
    {
        $eventKey = 'sertifikasi.recorded';
        $title = 'Sertifikasi ditambahkan';

        if (in_array($sertifikasi->status_verifikasi, SertifikasiKompetensi::verifiedStatuses(), true)) {
            $eventKey = 'sertifikasi.verified';
            $title = 'Sertifikasi diverifikasi';
        } elseif ($sertifikasi->status_verifikasi === SertifikasiKompetensi::VERIF_REJECTED) {
            $eventKey = 'sertifikasi.rejected';
            $title = 'Sertifikasi ditolak';
        }

        $this->careerEventService->record(
            $sertifikasi->perawat_nik,
            $eventKey,
            'sertifikasi',
            $title,
            [
                'status' => $sertifikasi->status_verifikasi,
                'source_system' => $sertifikasi->source_system,
                'source_actor' => $sertifikasi->source_actor,
                'actor_user_id' => $sertifikasi->verified_by,
                'subject' => $sertifikasi,
                'happened_at' => $sertifikasi->verified_at ?? $sertifikasi->created_at,
                'state' => [
                    'status_verifikasi' => $sertifikasi->status_verifikasi,
                    'nomor_sertifikat' => $sertifikasi->nomor_sertifikat,
                ],
                'payload' => [
                    'nama_sertifikat' => $sertifikasi->nama_sertifikat,
                    'nomor_sertifikat' => $sertifikasi->nomor_sertifikat,
                    'lembaga_pelatihan_id' => $sertifikasi->lembaga_pelatihan_id,
                    'tanggal_terbit' => optional($sertifikasi->tanggal_terbit)?->toDateString(),
                    'tanggal_kadaluarsa' => optional($sertifikasi->tanggal_kadaluarsa)?->toDateString(),
                ],
            ]
        );
    }

    private function recordSertifikasiUpdated(SertifikasiKompetensi $sertifikasi): void
    {
        if (!$this->modelChanged($sertifikasi, [
            'status_verifikasi',
            'jumlah_jam',
            'nilai_kompetensi',
            'nomor_sertifikat',
            'tanggal_terbit',
            'tanggal_kadaluarsa',
            'rejected_reason',
        ])) {
            return;
        }

        $eventKey = $sertifikasi->wasChanged('status_verifikasi')
            ? match ($sertifikasi->status_verifikasi) {
                SertifikasiKompetensi::VERIF_REJECTED => 'sertifikasi.rejected',
                SertifikasiKompetensi::VERIF_VERIFIED, SertifikasiKompetensi::VERIF_VALID => 'sertifikasi.verified',
                default => 'sertifikasi.status_changed',
            }
            : 'sertifikasi.updated';

        $title = match ($eventKey) {
            'sertifikasi.rejected' => 'Sertifikasi ditolak',
            'sertifikasi.verified' => 'Sertifikasi diverifikasi',
            'sertifikasi.status_changed' => 'Status sertifikasi diperbarui',
            default => 'Data sertifikasi diperbarui',
        };

        $this->careerEventService->record(
            $sertifikasi->perawat_nik,
            $eventKey,
            'sertifikasi',
            $title,
            [
                'status' => $sertifikasi->status_verifikasi,
                'source_system' => $sertifikasi->source_system,
                'source_actor' => $sertifikasi->source_actor,
                'actor_user_id' => $sertifikasi->verified_by,
                'subject' => $sertifikasi,
                'happened_at' => $sertifikasi->verified_at ?? now(),
                'state' => [
                    'status_verifikasi' => $sertifikasi->status_verifikasi,
                    'nomor_sertifikat' => $sertifikasi->nomor_sertifikat,
                ],
                'payload' => [
                    'nama_sertifikat' => $sertifikasi->nama_sertifikat,
                    'jumlah_jam' => $sertifikasi->jumlah_jam,
                    'nilai_kompetensi' => $sertifikasi->nilai_kompetensi,
                    'rejected_reason' => $sertifikasi->rejected_reason,
                ],
            ]
        );
    }

    private function recordLegalitasStatus(DataLegalitas $legalitas): void
    {
        [$eventKey, $title] = $this->resolveLegalitasEvent($legalitas->data_status);

        $this->careerEventService->record(
            $legalitas->perawat_nik,
            $eventKey,
            'legalitas',
            $title,
            [
                'status' => $legalitas->data_status,
                'source_system' => $legalitas->source_system,
                'source_actor' => $legalitas->source_actor,
                'actor_user_id' => $legalitas->data_reviewed_by,
                'subject' => $legalitas,
                'happened_at' => $legalitas->data_reviewed_at ?? $legalitas->terakhir_validasi_at ?? $legalitas->created_at,
                'state' => [
                    'data_status' => $legalitas->data_status,
                    'status_str' => $legalitas->status_str,
                    'nomor_str' => $legalitas->nomor_str,
                ],
                'payload' => [
                    'nomor_str' => $legalitas->nomor_str,
                    'status_str' => $legalitas->status_str,
                    'str_berlaku_sampai' => optional($legalitas->str_berlaku_sampai)?->toDateString(),
                    'data_rejection_note' => $legalitas->data_rejection_note,
                ],
            ]
        );
    }

    private function recordLegalitasUpdated(DataLegalitas $legalitas): void
    {
        if ($legalitas->wasChanged('data_status')) {
            $this->recordLegalitasStatus($legalitas);
            return;
        }

        if (!$this->modelChanged($legalitas, ['status_str', 'nomor_str', 'str_berlaku_mulai', 'str_berlaku_sampai'])) {
            return;
        }

        $this->careerEventService->record(
            $legalitas->perawat_nik,
            'legalitas.updated',
            'legalitas',
            'Data legalitas diperbarui',
            [
                'status' => $legalitas->data_status,
                'source_system' => $legalitas->source_system,
                'source_actor' => $legalitas->source_actor,
                'actor_user_id' => $legalitas->data_reviewed_by,
                'subject' => $legalitas,
                'happened_at' => $legalitas->terakhir_validasi_at ?? now(),
                'state' => [
                    'status_str' => $legalitas->status_str,
                    'data_status' => $legalitas->data_status,
                    'nomor_str' => $legalitas->nomor_str,
                ],
                'payload' => [
                    'nomor_str' => $legalitas->nomor_str,
                    'status_str' => $legalitas->status_str,
                    'str_berlaku_mulai' => optional($legalitas->str_berlaku_mulai)?->toDateString(),
                    'str_berlaku_sampai' => optional($legalitas->str_berlaku_sampai)?->toDateString(),
                ],
            ]
        );
    }

    private function recordRiwayatPekerjaanCreated(RiwayatPekerjaan $riwayat): void
    {
        $eventKey = 'pengalaman.recorded';
        $title = 'Riwayat pekerjaan ditambahkan';

        if ($riwayat->verifikasi_status === RiwayatPekerjaan::VERIF_PENDING) {
            $eventKey = 'pengalaman.submitted';
            $title = 'Riwayat pekerjaan diajukan untuk verifikasi';
        } elseif ($riwayat->verifikasi_status === RiwayatPekerjaan::VERIF_VERIFIED || $riwayat->is_verified) {
            $eventKey = 'pengalaman.verified';
            $title = 'Pengalaman kerja terverifikasi';
        }

        $this->careerEventService->record(
            $riwayat->perawat_nik,
            $eventKey,
            'pengalaman',
            $title,
            [
                'status' => $riwayat->verifikasi_status,
                'source_system' => $riwayat->source_system,
                'source_actor' => $riwayat->source_actor,
                'actor_user_id' => $riwayat->verified_by,
                'subject' => $riwayat,
                'happened_at' => $riwayat->verified_at ?? $riwayat->created_at,
                'state' => [
                    'verifikasi_status' => $riwayat->verifikasi_status,
                    'status_pekerjaan' => $riwayat->status_pekerjaan,
                    'mitra_pengguna_id' => $riwayat->mitra_pengguna_id,
                ],
                'payload' => [
                    'mitra_pengguna_id' => $riwayat->mitra_pengguna_id,
                    'posisi' => $riwayat->posisi,
                    'status_pekerjaan' => $riwayat->status_pekerjaan,
                    'tanggal_mulai' => optional($riwayat->tanggal_mulai)?->toDateString(),
                    'tanggal_selesai' => optional($riwayat->tanggal_selesai)?->toDateString(),
                ],
            ]
        );
    }

    private function recordRiwayatPekerjaanUpdated(RiwayatPekerjaan $riwayat): void
    {
        if ($riwayat->wasChanged('verifikasi_status')) {
            $eventKey = match ($riwayat->verifikasi_status) {
                RiwayatPekerjaan::VERIF_VERIFIED => 'pengalaman.verified',
                RiwayatPekerjaan::VERIF_REJECTED => 'pengalaman.rejected',
                default => 'pengalaman.reviewed',
            };

            $title = match ($eventKey) {
                'pengalaman.verified' => 'Pengalaman kerja diverifikasi',
                'pengalaman.rejected' => 'Pengalaman kerja ditolak',
                default => 'Status verifikasi pengalaman diperbarui',
            };

            $this->careerEventService->record(
                $riwayat->perawat_nik,
                $eventKey,
                'pengalaman',
                $title,
                [
                    'status' => $riwayat->verifikasi_status,
                    'source_system' => $riwayat->source_system,
                    'source_actor' => $riwayat->source_actor,
                    'actor_user_id' => $riwayat->verified_by,
                    'subject' => $riwayat,
                    'happened_at' => $riwayat->verified_at ?? now(),
                    'state' => [
                        'verifikasi_status' => $riwayat->verifikasi_status,
                        'status_pekerjaan' => $riwayat->status_pekerjaan,
                    ],
                    'payload' => [
                        'mitra_pengguna_id' => $riwayat->mitra_pengguna_id,
                        'rejected_reason' => $riwayat->rejected_reason,
                    ],
                ]
            );

            return;
        }

        if ($riwayat->wasChanged('status_pekerjaan')) {
            $eventKey = match ($riwayat->status_pekerjaan) {
                RiwayatPekerjaan::STATUS_SELESAI => 'pengalaman.completed',
                RiwayatPekerjaan::STATUS_RESIGN => 'pengalaman.resigned',
                default => 'pengalaman.active',
            };

            $title = match ($eventKey) {
                'pengalaman.completed' => 'Kontrak pekerjaan selesai',
                'pengalaman.resigned' => 'Status kerja berubah menjadi resign',
                default => 'Status kerja aktif',
            };

            $this->careerEventService->record(
                $riwayat->perawat_nik,
                $eventKey,
                'pengalaman',
                $title,
                [
                    'status' => $riwayat->status_pekerjaan,
                    'source_system' => $riwayat->source_system,
                    'source_actor' => $riwayat->source_actor,
                    'actor_user_id' => $riwayat->verified_by,
                    'subject' => $riwayat,
                    'happened_at' => now(),
                    'state' => [
                        'status_pekerjaan' => $riwayat->status_pekerjaan,
                        'verifikasi_status' => $riwayat->verifikasi_status,
                    ],
                    'payload' => [
                        'mitra_pengguna_id' => $riwayat->mitra_pengguna_id,
                        'tanggal_selesai' => optional($riwayat->tanggal_selesai)?->toDateString(),
                    ],
                ]
            );

            return;
        }

        if (!$this->modelChanged($riwayat, ['penilaian_kinerja', 'kinerja_rating', 'kinerja_score'])) {
            return;
        }

        $this->careerEventService->record(
            $riwayat->perawat_nik,
            'kinerja.updated',
            'kinerja',
            'Evaluasi kinerja diperbarui',
            [
                'status' => $riwayat->status_pekerjaan,
                'source_system' => $riwayat->source_system,
                'source_actor' => $riwayat->source_actor,
                'actor_user_id' => $riwayat->verified_by,
                'subject' => $riwayat,
                'happened_at' => now(),
                'state' => [
                    'kinerja_rating' => $riwayat->kinerja_rating,
                    'kinerja_score' => $riwayat->kinerja_score,
                    'status_pekerjaan' => $riwayat->status_pekerjaan,
                ],
                'payload' => [
                    'mitra_pengguna_id' => $riwayat->mitra_pengguna_id,
                    'penilaian_kinerja' => $riwayat->penilaian_kinerja,
                    'kinerja_rating' => $riwayat->kinerja_rating,
                    'kinerja_score' => $riwayat->kinerja_score,
                ],
            ]
        );
    }

    /**
     * @param list<string> $columns
     */
    private function modelChanged(Model $model, array $columns): bool
    {
        foreach ($columns as $column) {
            if ($model->wasChanged($column)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{0:string,1:string}
     */
    private function resolveLegalitasEvent(?string $dataStatus): array
    {
        return match ($dataStatus) {
            DataLegalitas::DATA_STATUS_VERIFIED => ['legalitas.verified', 'Legalitas STR diverifikasi'],
            DataLegalitas::DATA_STATUS_EXPIRED => ['legalitas.expired', 'Legalitas STR expired'],
            DataLegalitas::DATA_STATUS_REJECTED => ['legalitas.rejected', 'Legalitas STR ditolak'],
            DataLegalitas::DATA_STATUS_PENDING_SYNC => ['legalitas.pending_sync', 'Legalitas menunggu sinkronisasi'],
            DataLegalitas::DATA_STATUS_SUBMITTED => ['legalitas.submitted', 'Data STR diajukan untuk verifikasi'],
            default => ['legalitas.draft', 'Data legalitas disimpan sebagai draft'],
        };
    }
}
