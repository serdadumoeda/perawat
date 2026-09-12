<?php

return [
    'signature_ttl_seconds' => (int) env('API_SIGNATURE_TTL_SECONDS', 300),
    'idempotency_ttl_seconds' => (int) env('API_IDEMPOTENCY_TTL_SECONDS', 86400),
    'idempotency_purge_batch_size' => max(100, (int) env('API_IDEMPOTENCY_PURGE_BATCH_SIZE', 1000)),
    'idempotency_stale_in_progress_minutes' => max(5, (int) env('API_IDEMPOTENCY_STALE_MINUTES', 120)),
    'webhook_timeout_seconds' => (int) env('WEBHOOK_TIMEOUT_SECONDS', 8),
    'webhook_retry_attempts' => (int) env('WEBHOOK_RETRY_ATTEMPTS', 3),
    'webhook_retry_backoff_seconds' => (function (): array {
        $parsed = array_values(array_filter(
            array_map('intval', explode(',', (string) env('WEBHOOK_RETRY_BACKOFF_SECONDS', '30,120,300'))),
            static fn (int $value): bool => $value >= 0
        ));

        return $parsed === [] ? [30, 120, 300] : $parsed;
    })(),
];
