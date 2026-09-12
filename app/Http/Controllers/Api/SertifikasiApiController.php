<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSertifikasiApiRequest;
use App\Models\ApiKey;
use App\Models\LembagaPelatihan;
use App\Services\AuditLogService;
use App\Services\SertifikasiIngestService;
use Illuminate\Http\JsonResponse;

class SertifikasiApiController extends Controller
{
    public function store(StoreSertifikasiApiRequest $request, SertifikasiIngestService $service, AuditLogService $auditLog): JsonResponse
    {
        /** @var ApiKey|null $apiKey */
        $apiKey = $request->attributes->get('apiKey');
        $owner = $apiKey?->owner;

        if (!$owner || !$owner instanceof LembagaPelatihan) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $result = $service->ingest($request->validated('data'), $owner->id);

        $auditLog->log(null, 'api.sertifikasi.ingest', null, [
            'api_key_id' => $apiKey?->id,
            'lembaga_pelatihan_id' => $owner->id,
            'processed' => $result['processed'],
            'created' => $result['created'],
            'updated' => $result['updated'],
            'errors' => count($result['errors']),
        ], $request);

        return response()->json($result);
    }
}
