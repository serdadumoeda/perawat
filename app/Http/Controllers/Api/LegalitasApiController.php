<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLegalitasApiRequest;
use App\Models\ApiKey;
use App\Models\RegulatorOrganisasi;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\LegalitasIngestService;
use App\Services\RegulatorOrganisasiResolver;
use Illuminate\Http\JsonResponse;

class LegalitasApiController extends Controller
{
    public function store(
        StoreLegalitasApiRequest $request,
        LegalitasIngestService $service,
        AuditLogService $auditLog,
        RegulatorOrganisasiResolver $organisasiResolver
    ): JsonResponse
    {
        /** @var ApiKey|null $apiKey */
        $apiKey = $request->attributes->get('apiKey');
        $owner = $apiKey?->owner;

        $regulatorOrganisasiId = null;
        if ($owner instanceof RegulatorOrganisasi) {
            $regulatorOrganisasiId = $owner->id;
        } elseif ($owner instanceof User && $owner->role === User::ROLE_REGULATOR) {
            $regulatorOrganisasiId = $organisasiResolver->forUser($owner)?->id;
        } else {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $result = $service->ingest($request->validated('data'), $regulatorOrganisasiId);

        $auditLog->log(null, 'api.legalitas.ingest', null, [
            'api_key_id' => $apiKey?->id,
            'owner_type' => $owner ? get_class($owner) : null,
            'owner_id' => $owner?->getKey(),
            'regulator_organisasi_id' => $regulatorOrganisasiId,
            'processed' => $result['processed'],
            'created' => $result['created'],
            'updated' => $result['updated'],
            'errors' => count($result['errors']),
        ], $request);

        return response()->json($result);
    }
}
