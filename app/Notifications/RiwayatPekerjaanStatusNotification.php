<?php

namespace App\Notifications;

use App\Models\RiwayatPekerjaan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RiwayatPekerjaanStatusNotification extends Notification
{
    use Queueable;

    public function __construct(private RiwayatPekerjaan $riwayat, private string $message)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->riwayat->loadMissing(['mitraPengguna']);

        return [
            'tipe' => 'riwayat_pekerjaan_status',
            'riwayat_pekerjaan_id' => $this->riwayat->id,
            'mitra_pengguna_id' => $this->riwayat->mitra_pengguna_id,
            'mitra_nama' => $this->riwayat->mitraPengguna?->nama,
            'status' => $this->riwayat->verifikasi_status,
            'message' => $this->message,
            'posisi' => $this->riwayat->posisi,
        ];
    }
}
