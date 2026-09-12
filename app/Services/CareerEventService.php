<?php

namespace App\Services;

use App\Models\CareerEvent;
use App\Models\Perawat;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CareerEventService
{
    /**
     * @param Perawat|string $perawat
     * @param array{
     *     description?:string|null,
     *     status?:string|null,
     *     source_system?:string|null,
     *     source_actor?:string|null,
     *     actor?:User|string|null,
     *     actor_user_id?:string|null,
     *     actor_name?:string|null,
     *     actor_role?:string|null,
     *     subject?:Model|null,
     *     payload?:array<string,mixed>,
     *     happened_at?:mixed,
     *     state?:array<string,mixed>,
     *     fingerprint?:string|null,
     * } $options
     */
    public function record(Perawat|string $perawat, string $eventKey, string $category, string $title, array $options = []): ?CareerEvent
    {
        $perawatModel = $perawat instanceof Perawat
            ? $perawat
            : Perawat::query()->where('nik', $perawat)->first();

        if (!$perawatModel) {
            return null;
        }

        $subject = ($options['subject'] ?? null);
        if ($subject !== null && !$subject instanceof Model) {
            $subject = null;
        }

        $happenedAt = $this->normalizeDate($options['happened_at'] ?? null) ?? now();
        $payload = (array) ($options['payload'] ?? []);
        $status = $options['status'] ?? null;

        $actor = $this->resolveActor($options);

        $state = (array) ($options['state'] ?? []);
        if ($status !== null && !array_key_exists('status', $state)) {
            $state['status'] = $status;
        }

        $fingerprint = $options['fingerprint']
            ?? $this->buildFingerprint($perawatModel->nik, $eventKey, $subject, $state);

        return CareerEvent::updateOrCreate(
            ['fingerprint' => $fingerprint],
            [
                'perawat_id' => $perawatModel->id,
                'perawat_nik' => $perawatModel->nik,
                'category' => $category,
                'event_key' => $eventKey,
                'title' => $title,
                'description' => $options['description'] ?? null,
                'status' => $status,
                'source_system' => $options['source_system'] ?? null,
                'source_actor' => $options['source_actor'] ?? null,
                'actor_user_id' => $actor['actor_user_id'],
                'actor_name' => $actor['actor_name'],
                'actor_role' => $actor['actor_role'],
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'payload' => $payload,
                'happened_at' => $happenedAt,
            ]
        );
    }

    private function buildFingerprint(string $nik, string $eventKey, ?Model $subject, array $state): string
    {
        $signature = [
            'nik' => $nik,
            'event_key' => $eventKey,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'state' => $state,
        ];

        return hash('sha256', json_encode($signature, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function normalizeDate(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param array<string,mixed> $options
     * @return array{actor_user_id:?string,actor_name:?string,actor_role:?string}
     */
    private function resolveActor(array $options): array
    {
        $actor = $options['actor'] ?? null;

        if ($actor instanceof User) {
            return [
                'actor_user_id' => $actor->id,
                'actor_name' => $actor->name,
                'actor_role' => $actor->role,
            ];
        }

        $actorUserId = $options['actor_user_id'] ?? (is_string($actor) ? $actor : null);
        if (!$actorUserId) {
            return [
                'actor_user_id' => null,
                'actor_name' => $options['actor_name'] ?? null,
                'actor_role' => $options['actor_role'] ?? null,
            ];
        }

        $actorUser = User::query()->find($actorUserId);

        return [
            'actor_user_id' => $actorUser?->id,
            'actor_name' => $options['actor_name'] ?? $actorUser?->name,
            'actor_role' => $options['actor_role'] ?? $actorUser?->role,
        ];
    }
}
