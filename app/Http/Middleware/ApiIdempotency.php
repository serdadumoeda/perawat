<?php

namespace App\Http\Middleware;

use App\Models\ApiIdempotencyKey;
use App\Models\ApiKey;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApiIdempotency
{
    public function handle(Request $request, Closure $next): Response
    {
        $headerValue = trim((string) $request->header('Idempotency-Key', ''));
        if ($headerValue === '') {
            return $next($request);
        }

        if (mb_strlen($headerValue) > 120) {
            return response()->json(['message' => 'Invalid Idempotency-Key'], 422);
        }

        /** @var ApiKey|null $apiKey */
        $apiKey = $request->attributes->get('apiKey');
        if (!$apiKey) {
            return $next($request);
        }

        $method = strtoupper($request->method());
        $endpoint = '/'.$request->path();
        $requestHash = hash('sha256', (string) $request->getContent());

        $existing = $this->findExisting($apiKey->id, $method, $endpoint, $headerValue);
        if ($existing) {
            return $this->resolveExistingResponse($existing, $requestHash, $headerValue);
        }

        $ttlSeconds = max(60, (int) config('api.idempotency_ttl_seconds', 86400));
        try {
            $entry = ApiIdempotencyKey::create([
                'api_key_id' => $apiKey->id,
                'method' => $method,
                'endpoint' => $endpoint,
                'idempotency_key' => $headerValue,
                'request_hash' => $requestHash,
                'expires_at' => now()->addSeconds($ttlSeconds),
            ]);
        } catch (QueryException) {
            $existing = $this->findExisting($apiKey->id, $method, $endpoint, $headerValue);
            if ($existing) {
                return $this->resolveExistingResponse($existing, $requestHash, $headerValue);
            }

            return response()->json(['message' => 'Failed to process idempotency key'], 409);
        }

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $entry->delete();
            throw $exception;
        }

        if ($response->getStatusCode() >= 500) {
            $entry->delete();
            return $response;
        }

        $responseContent = $response->getContent();
        $entry->update([
            'response_status_code' => $response->getStatusCode(),
            'response_body' => is_string($responseContent) ? $responseContent : '',
            'completed_at' => now(),
        ]);

        $response->headers->set('X-Idempotency-Key', $headerValue);

        return $response;
    }

    private function resolveExistingResponse(
        ApiIdempotencyKey $entry,
        string $requestHash,
        string $headerValue
    ): Response {
        if ($entry->request_hash !== $requestHash) {
            return response()->json([
                'message' => 'Idempotency-Key conflict: payload tidak sama.',
            ], 409);
        }

        if (!$entry->completed_at) {
            return response()->json([
                'message' => 'Request dengan Idempotency-Key yang sama sedang diproses.',
            ], 409);
        }

        return response(
            $entry->response_body ?? '{}',
            $entry->response_status_code ?? 200,
            [
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => $headerValue,
                'X-Idempotency-Replayed' => 'true',
            ]
        );
    }

    private function findExisting(
        string $apiKeyId,
        string $method,
        string $endpoint,
        string $idempotencyKey
    ): ?ApiIdempotencyKey {
        $existing = ApiIdempotencyKey::query()
            ->where('api_key_id', $apiKeyId)
            ->where('method', $method)
            ->where('endpoint', $endpoint)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if (!$existing) {
            return null;
        }

        if ($existing->expires_at && $existing->expires_at->isPast()) {
            $existing->delete();
            return null;
        }

        return $existing;
    }
}

