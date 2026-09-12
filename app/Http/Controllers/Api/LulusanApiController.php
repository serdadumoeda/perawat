<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLulusanApiRequest;
use App\Models\ApiKey;
use App\Models\InstitusiPendidikan;
use App\Services\AuditLogService;
use App\Services\LulusanIngestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LulusanApiController extends Controller
{
    public function store(StoreLulusanApiRequest $request, LulusanIngestService $service, AuditLogService $auditLog): JsonResponse
    {
        /** @var ApiKey|null $apiKey */
        $apiKey = $request->attributes->get('apiKey');
        $owner = $apiKey?->owner;

        if (!$owner || !$owner instanceof InstitusiPendidikan) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $result = $service->ingest($request->validated('data'), $owner->id);

        $auditLog->log(null, 'api.lulusan.ingest', null, [
            'api_key_id' => $apiKey?->id,
            'institusi_pendidikan_id' => $owner->id,
            'processed' => $result['processed'],
            'created' => $result['created'],
            'updated' => $result['updated'],
            'errors' => count($result['errors']),
        ], $request);

        return response()->json($result);
    }

    public function gatewayLookup(string $nik, \App\Services\KampusGatewayService $gatewayService): JsonResponse
    {
        $result = $gatewayService->lookupByNik($nik);

        if (!$result['found']) {
            return response()->json($result, 404);
        }

        return response()->json($result);
    }
}

