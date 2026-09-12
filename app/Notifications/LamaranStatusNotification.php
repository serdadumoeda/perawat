<?php

namespace App\Notifications;

use App\Models\Lamaran;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LamaranStatusNotification extends Notification
{
    use Queueable;

    public function __construct(private Lamaran $lamaran, private string $message)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->lamaran->loadMissing(['lowongan']);

        return [
            'tipe' => 'lamaran_status',
            'lamaran_id' => $this->lamaran->id,
            'lowongan_id' => $this->lamaran->lowongan_id,
            'mitra_pengguna_id' => $this->lamaran->mitra_pengguna_id,
            'status' => $this->lamaran->status,
            'judul_lowongan' => $this->lamaran->lowongan?->judul,
            'message' => $this->message,
        ];
    }
}
