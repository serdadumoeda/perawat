<?php

namespace App\Notifications;

use App\Models\DataLegalitas;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LegalitasDocumentStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private DataLegalitas $legalitas,
        private string $documentType,
        private string $status,
        private ?string $note,
        private string $message
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipe' => 'legalitas_document_status',
            'legalitas_id' => $this->legalitas->id,
            'perawat_nik' => $this->legalitas->perawat_nik,
            'document_type' => $this->documentType,
            'status' => $this->status,
            'note' => $this->note,
            'message' => $this->message,
        ];
    }
}
