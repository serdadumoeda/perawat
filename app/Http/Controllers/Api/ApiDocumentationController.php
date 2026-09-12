<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApiDocumentationService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ApiDocumentationController extends Controller
{
    public function openApi(ApiDocumentationService $apiDocumentationService): JsonResponse
    {
        return response()->json(
            $apiDocumentationService->openApiV1(),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function swagger(): View
    {
        return view('api.docs', [
            'openApiEndpoint' => route('api.v1.openapi'),
        ]);
    }
}
