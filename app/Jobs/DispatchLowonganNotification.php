<?php

namespace App\Jobs;

use App\Models\Lowongan;
use App\Services\LowonganNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchLowonganNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $deleteWhenMissingModels = true;

    public function __construct(public Lowongan $lowongan)
    {
    }

    public function handle(LowonganNotificationService $service): void
    {
        $service->notifyMatchingPerawat($this->lowongan);
    }
}
