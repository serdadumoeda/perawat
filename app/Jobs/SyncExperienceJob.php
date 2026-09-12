<?php

namespace App\Jobs;

use App\Models\ExperienceSyncSetting;
use App\Models\RiwayatPekerjaan;
use App\Models\RiwayatPekerjaanSync;
use App\Notifications\ExperienceSyncFailedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class SyncExperienceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $mitraId = null)
    {
    }

    public function handle(): void
    {
        $query = ExperienceSyncSetting::query()->where('is_active', true);
        if ($this->mitraId) {
            $query->where('mitra_pengguna_id', $this->mitraId);
        }

        $query->get()->each(function (ExperienceSyncSetting $setting) {
            $this->syncSetting($setting);
        });
    }

    private function syncSetting(ExperienceSyncSetting $setting): void
    {
        $now = now();
        $source = $setting->base_url ? (parse_url($setting->base_url, PHP_URL_HOST) ?: $setting->base_url) : 'mock';

        if (!$setting->base_url) {
            RiwayatPekerjaanSync::create([
                'perawat_nik' => null,
                'mitra_pengguna_id' => $setting->mitra_pengguna_id,
                'riwayat_pekerjaan_id' => null,
                'source_system' => (string) $source,
                'external_id' => (string) Str::uuid(),
                'status' => RiwayatPekerjaanSync::STATUS_FAILED,
                'last_synced_at' => $now,
                'payload' => [
                    'items' => 0,
                    'sync_mode' => $setting->sync_mode,
                ],
                'error_message' => 'Base URL belum diisi. Sinkronisasi otomatis tidak dapat dijalankan.',
            ]);

            $setting->update(['last_synced_at' => $now]);
            $this->notifyFailureStreak($setting);
            return;
        }

        try {
            $perawatNiks = RiwayatPekerjaan::where('mitra_pengguna_id', $setting->mitra_pengguna_id)
                ->whereNotNull('perawat_nik')
                ->select('perawat_nik')
                ->distinct()
                ->pluck('perawat_nik');

            if ($perawatNiks->isEmpty()) {
                RiwayatPekerjaanSync::create([
                    'perawat_nik' => null,
                    'mitra_pengguna_id' => $setting->mitra_pengguna_id,
                    'riwayat_pekerjaan_id' => null,
                    'source_system' => (string) $source,
                    'external_id' => (string) Str::uuid(),
                    'status' => RiwayatPekerjaanSync::STATUS_SUCCESS,
                    'last_synced_at' => $now,
                    'payload' => [
                        'items' => 0,
                        'sync_mode' => $setting->sync_mode,
                        'message' => 'Tidak ada data riwayat pekerjaan untuk disinkronkan.',
                    ],
                ]);
            } else {
                foreach ($perawatNiks as $perawatNik) {
                    RiwayatPekerjaanSync::create([
                        'perawat_nik' => $perawatNik,
                        'mitra_pengguna_id' => $setting->mitra_pengguna_id,
                        'riwayat_pekerjaan_id' => null,
                        'source_system' => (string) $source,
                        'external_id' => (string) Str::uuid(),
                        'status' => RiwayatPekerjaanSync::STATUS_SUCCESS,
                        'last_synced_at' => $now,
                        'payload' => [
                            'items' => 1,
                            'sync_mode' => $setting->sync_mode,
                            'note' => 'Mock sync sukses.',
                        ],
                    ]);
                }
            }
        } catch (\Throwable $error) {
            RiwayatPekerjaanSync::create([
                'perawat_nik' => null,
                'mitra_pengguna_id' => $setting->mitra_pengguna_id,
                'riwayat_pekerjaan_id' => null,
                'source_system' => (string) $source,
                'external_id' => (string) Str::uuid(),
                'status' => RiwayatPekerjaanSync::STATUS_FAILED,
                'last_synced_at' => $now,
                'payload' => [
                    'items' => 0,
                    'sync_mode' => $setting->sync_mode,
                ],
                'error_message' => $error->getMessage(),
            ]);
        } finally {
            $setting->update(['last_synced_at' => $now]);
            $this->notifyFailureStreak($setting);
        }
    }

    private function notifyFailureStreak(ExperienceSyncSetting $setting): void
    {
        $threshold = (int) config('experience_sync.failure_threshold', 3);
        if ($threshold < 1) {
            $threshold = 1;
        }

        $logs = RiwayatPekerjaanSync::query()
            ->where('mitra_pengguna_id', $setting->mitra_pengguna_id)
            ->orderByDesc('created_at')
            ->limit($threshold)
            ->get();

        if ($logs->count() < $threshold) {
            return;
        }

        $allFailed = $logs->every(function (RiwayatPekerjaanSync $log) {
            return $log->status === RiwayatPekerjaanSync::STATUS_FAILED;
        });

        if (!$allFailed) {
            return;
        }

        $oldestFailed = $logs->last();
        $lastNotified = $setting->last_failure_notified_at;
        if ($lastNotified && $oldestFailed && $lastNotified->greaterThanOrEqualTo($oldestFailed->created_at)) {
            return;
        }

        $setting->loadMissing('mitraPengguna.users');
        $users = $setting->mitraPengguna?->users;
        if (!$users || $users->isEmpty()) {
            return;
        }

        $latestError = $logs->first()?->error_message;
        Notification::send($users, new ExperienceSyncFailedNotification($setting->mitraPengguna, $threshold, $latestError));
        $setting->update(['last_failure_notified_at' => now()]);
    }
}
