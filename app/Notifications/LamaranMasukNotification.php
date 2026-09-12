<?php

namespace App\Notifications;

use App\Models\Lamaran;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LamaranMasukNotification extends Notification
{
    use Queueable;

    public function __construct(private Lamaran $lamaran)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->lamaran->loadMissing(['perawat', 'lowongan']);

        return [
            'tipe' => 'lamaran_masuk',
            'lamaran_id' => $this->lamaran->id,
            'lowongan_id' => $this->lamaran->lowongan_id,
            'mitra_pengguna_id' => $this->lamaran->mitra_pengguna_id,
            'perawat_nik' => $this->lamaran->perawat_nik,
            'perawat_nama' => $this->lamaran->perawat?->nama,
            'judul_lowongan' => $this->lamaran->lowongan?->judul,
            'status' => $this->lamaran->status,
        ];
    }
}
