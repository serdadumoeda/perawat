<?php

use App\Http\Controllers\Api\ApiDocumentationController;
use App\Http\Controllers\Api\LegalitasApiController;
use App\Http\Controllers\Api\LulusanApiController;
use App\Http\Controllers\Api\SertifikasiApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('openapi.json', [ApiDocumentationController::class, 'openApi'])->name('api.v1.openapi');

    Route::post('lulusan', [LulusanApiController::class, 'store'])
        ->middleware(['api', 'api_request_log', 'api_key:lulusan:write', 'api_idempotency', 'throttle:api_key']);

    Route::post('sertifikasi', [SertifikasiApiController::class, 'store'])
        ->middleware(['api', 'api_request_log', 'api_key:sertifikasi:write', 'api_idempotency', 'throttle:api_key']);

    Route::post('legalitas', [LegalitasApiController::class, 'store'])
        ->middleware(['api', 'api_request_log', 'api_key:legalitas:write', 'api_idempotency', 'throttle:api_key']);

    Route::get('kampus/gateway/{nik}', [LulusanApiController::class, 'gatewayLookup'])
        ->middleware(['api', 'api_request_log', 'api_key:lulusan:read', 'throttle:api_key'])
        ->name('api.v1.kampus.gateway');
});

