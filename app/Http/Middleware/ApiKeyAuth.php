<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    /**
     * @param  array<int, string>  $scopes
     */
    public function handle(Request $request, Closure $next, ...$scopes): Response
    {
        $token = $this->extractToken($request);
        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $hash = hash('sha256', $token);
        $apiKey = ApiKey::where('token_hash', $hash)
            ->whereNull('revoked_at')
            ->first();

        if (!$apiKey) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!empty($scopes) && !$this->hasScopes($apiKey->scopes ?? [], $scopes)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($this->shouldVerifySignature($request, $apiKey) && !$this->verifySignature($request, $apiKey)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $apiKey->last_used_at = now();
        $apiKey->save();

        $request->attributes->set('apiKey', $apiKey);
        $request->attributes->set('apiOwner', $apiKey->owner);

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $header = $request->header('X-API-KEY');
        if ($header) {
            return trim($header);
        }

        $auth = $request->header('Authorization');
        if ($auth && str_starts_with($auth, 'Bearer ')) {
            return trim(substr($auth, 7));
        }

        return null;
    }

    private function shouldVerifySignature(Request $request, ApiKey $apiKey): bool
    {
        if ($apiKey->require_signature) {
            return true;
        }

        return $request->hasHeader('X-API-SIGNATURE') || $request->hasHeader('X-API-TIMESTAMP');
    }

    private function verifySignature(Request $request, ApiKey $apiKey): bool
    {
        $secret = (string) ($apiKey->signature_secret ?? '');
        if ($secret === '') {
            return false;
        }

        $timestampHeader = (string) $request->header('X-API-TIMESTAMP', '');
        if ($timestampHeader === '' || !ctype_digit($timestampHeader)) {
            return false;
        }

        $timestamp = (int) $timestampHeader;
        $allowedSkew = (int) config('api.signature_ttl_seconds', 300);

        if (abs(now()->timestamp - $timestamp) > $allowedSkew) {
            return false;
        }

        $providedSignature = trim((string) $request->header('X-API-SIGNATURE', ''));
        if ($providedSignature === '') {
            return false;
        }

        if (str_starts_with($providedSignature, 'sha256=')) {
            $providedSignature = substr($providedSignature, 7);
        }

        $rawBody = (string) $request->getContent();
        $expectedSignature = hash_hmac('sha256', $timestampHeader.'.'.$rawBody, $secret);

        return hash_equals($expectedSignature, strtolower($providedSignature));
    }

    /**
     * @param  array<int, string>  $granted
     * @param  array<int, string>  $required
     */
    private function hasScopes(array $granted, array $required): bool
    {
        if (in_array('*', $granted, true)) {
            return true;
        }

        foreach ($required as $scope) {
            if (!in_array($scope, $granted, true)) {
                return false;
            }
        }

        return true;
    }
}
