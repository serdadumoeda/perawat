<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class VerificationService
{
    public const ACTION_APPROVE = 'approve';
    public const ACTION_REJECT = 'reject';

    /**
     * Apply approve/reject payload to a model using a configurable mapping.
     *
     * Required config keys:
     * - status_field
     * - approved_value
     * - rejected_value
     *
     * Optional config keys:
     * - note_field
     * - reviewed_by_field
     * - reviewed_at_field
     * - approved_payload
     * - rejected_payload
     */
    public function apply(Model $model, string $action, array $config, array $context = []): Model
    {
        $action = $this->normalizeAction($action);

        $payload = $this->buildPayload($action, $config, $context);

        $model->fill($payload);
        $model->save();

        return $model;
    }

    private function normalizeAction(string $action): string
    {
        if (!in_array($action, [self::ACTION_APPROVE, self::ACTION_REJECT], true)) {
            throw new InvalidArgumentException('Action verifikasi tidak valid.');
        }

        return $action;
    }

    private function buildPayload(string $action, array $config, array $context): array
    {
        if (empty($config['status_field'])) {
            throw new InvalidArgumentException('status_field wajib diisi untuk verifikasi.');
        }

        $statusField = $config['status_field'];
        $approvedValue = $config['approved_value'] ?? null;
        $rejectedValue = $config['rejected_value'] ?? null;

        $payload = [
            $statusField => $action === self::ACTION_APPROVE ? $approvedValue : $rejectedValue,
        ];

        $noteField = $config['note_field'] ?? null;
        if ($noteField && array_key_exists('note', $context)) {
            $payload[$noteField] = $context['note'];
        }

        $reviewedByField = $config['reviewed_by_field'] ?? null;
        if ($reviewedByField) {
            $payload[$reviewedByField] = $context['reviewed_by'] ?? null;
        }

        $reviewedAtField = $config['reviewed_at_field'] ?? null;
        if ($reviewedAtField) {
            $payload[$reviewedAtField] = $context['reviewed_at'] ?? now();
        }

        $defaultExtra = $action === self::ACTION_APPROVE
            ? ($config['approved_payload'] ?? [])
            : ($config['rejected_payload'] ?? []);

        if (!empty($defaultExtra)) {
            $payload = array_merge($payload, $defaultExtra);
        }

        if (!empty($context['extra']) && is_array($context['extra'])) {
            $payload = array_merge($payload, $context['extra']);
        }

        return $payload;
    }
}
