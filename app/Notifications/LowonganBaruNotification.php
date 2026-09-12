<?php

namespace App\Notifications;

use App\Models\Lowongan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowonganBaruNotification extends Notification
{
    use Queueable;

    public function __construct(private Lowongan $lowongan)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'lowongan_id' => $this->lowongan->id,
            'judul' => $this->lowongan->judul,
            'mitra_pengguna_id' => $this->lowongan->mitra_pengguna_id,
            'tipe_penempatan' => $this->lowongan->tipe_penempatan,
            'negara_penempatan' => $this->lowongan->negara_penempatan,
            'provinsi_penempatan' => $this->lowongan->provinsi_penempatan,
            'kota_penempatan' => $this->lowongan->kota_penempatan,
            'published_at' => $this->lowongan->published_at,
        ];
    }
}
