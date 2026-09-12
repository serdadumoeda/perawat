<?php

namespace App\Notifications;

use App\Models\MitraPengguna;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExperienceSyncFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private MitraPengguna $mitra,
        private int $streak,
        private ?string $latestError
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipe' => 'experience_sync_failed',
            'mitra_pengguna_id' => $this->mitra->id,
            'mitra_nama' => $this->mitra->nama,
            'streak' => $this->streak,
            'message' => "Sync pengalaman kerja gagal {$this->streak} kali berturut-turut.",
            'latest_error' => $this->latestError,
        ];
    }
}
