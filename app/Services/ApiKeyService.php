<?php

namespace App\Services;

use App\Models\ApiKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKeyService
{
    public function create(Model $owner, string $name, array $scopes = [], bool $requireSignature = false): array
    {
        $token = Str::random(48).Str::random(16);
        $hash = hash('sha256', $token);
        $signatureSecret = $requireSignature ? Str::random(64) : null;

        $apiKey = ApiKey::create([
            'name' => $name,
            'token_hash' => $hash,
            'owner_type' => get_class($owner),
            'owner_id' => $owner->getKey(),
            'scopes' => $scopes,
            'require_signature' => $requireSignature,
            'signature_secret' => $signatureSecret,
            'signature_last_rotated_at' => $requireSignature ? now() : null,
        ]);

        return [$apiKey, $token, $signatureSecret];
    }

    public function revoke(ApiKey $apiKey): void
    {
        $apiKey->revoked_at = now();
        $apiKey->save();
    }

    public function rotateSignatureSecret(ApiKey $apiKey): string
    {
        $signatureSecret = Str::random(64);
        $apiKey->signature_secret = $signatureSecret;
        $apiKey->signature_last_rotated_at = now();
        $apiKey->require_signature = true;
        $apiKey->save();

        return $signatureSecret;
    }
}
