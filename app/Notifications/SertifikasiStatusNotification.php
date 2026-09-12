<?php

namespace App\Notifications;

use App\Models\SertifikasiKompetensi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SertifikasiStatusNotification extends Notification
{
    use Queueable;

    public function __construct(private SertifikasiKompetensi $sertifikasi, private string $message)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->sertifikasi->loadMissing(['lembagaPelatihan']);

        return [
            'tipe' => 'sertifikasi_status',
            'sertifikasi_id' => $this->sertifikasi->id,
            'perawat_nik' => $this->sertifikasi->perawat_nik,
            'status' => $this->sertifikasi->status_verifikasi,
            'nama_sertifikat' => $this->sertifikasi->nama_sertifikat,
            'lembaga_nama' => $this->sertifikasi->lembagaPelatihan?->nama,
            'message' => $this->message,
        ];
    }
}
