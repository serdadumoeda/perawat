<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\ApiRequestLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApiRequestLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);

        try {
            $response = $next($request);
        } catch (Throwable $error) {
            $this->storeLog(
                request: $request,
                statusCode: $this->resolveExceptionStatusCode($error),
                responseTimeMs: $this->resolveDuration($startedAt),
                errorMessage: $error->getMessage()
            );

            throw $error;
        }

        $errorMessage = $response->getStatusCode() >= 400
            ? $this->resolveResponseErrorMessage($response)
            : null;

        $this->storeLog(
            request: $request,
            statusCode: $response->getStatusCode(),
            responseTimeMs: $this->resolveDuration($startedAt),
            errorMessage: $errorMessage
        );

        return $response;
    }

    private function storeLog(Request $request, int $statusCode, int $responseTimeMs, ?string $errorMessage): void
    {
        /** @var ApiKey|null $apiKey */
        $apiKey = $request->attributes->get('apiKey');

        ApiRequestLog::create([
            'api_key_id' => $apiKey?->id,
            'method' => $request->method(),
            'endpoint' => '/'.$request->path(),
            'payload_hash' => $this->resolvePayloadHash($request),
            'status_code' => $statusCode,
            'response_time_ms' => $responseTimeMs,
            'error_message' => $errorMessage,
        ]);
    }

    private function resolveDuration(float $startedAt): int
    {
        return (int) max(0, round((microtime(true) - $startedAt) * 1000));
    }

    private function resolvePayloadHash(Request $request): ?string
    {
        $payload = $request->all();
        if ($payload === []) {
            return null;
        }

        $normalized = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION
        );

        if ($normalized === false || $normalized === '{}') {
            return null;
        }

        return hash('sha256', $normalized);
    }

    private function resolveResponseErrorMessage(Response $response): ?string
    {
        $content = $response->getContent();
        if (!is_string($content) || trim($content) === '') {
            return null;
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            return null;
        }

        if (isset($decoded['message']) && is_string($decoded['message'])) {
            return mb_substr($decoded['message'], 0, 2000);
        }

        if (isset($decoded['errors']) && is_array($decoded['errors'])) {
            $flattened = collect($decoded['errors'])
                ->flatten()
                ->filter(fn ($value) => is_scalar($value))
                ->map(fn ($value) => (string) $value)
                ->implode('; ');

            return $flattened !== '' ? mb_substr($flattened, 0, 2000) : null;
        }

        return null;
    }

    private function resolveExceptionStatusCode(Throwable $error): int
    {
        if (method_exists($error, 'getStatusCode')) {
            return (int) $error->getStatusCode();
        }

        return 500;
    }
}
