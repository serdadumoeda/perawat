<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreApiKeyRequest;
use App\Models\ApiKey;
use App\Models\InstitusiPendidikan;
use App\Models\LembagaPelatihan;
use App\Models\RegulatorOrganisasi;
use App\Models\User;
use App\Services\ApiDocumentationService;
use App\Services\ApiKeyService;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function index(): View
    {
        $apiKeys = ApiKey::with('owner')->latest()->paginate(25);

        return view('admin.api_keys.index', compact('apiKeys'));
    }

    public function create(): View
    {
        $institusi = InstitusiPendidikan::orderBy('nama')->get();
        $lembaga = LembagaPelatihan::orderBy('nama')->get();
        $regulator = User::where('role', User::ROLE_REGULATOR)->orderBy('name')->get();
        $regulatorOrganisasi = RegulatorOrganisasi::orderBy('nama')->get();

        return view('admin.api_keys.create', compact('institusi', 'lembaga', 'regulator', 'regulatorOrganisasi'));
    }

    public function store(StoreApiKeyRequest $request, ApiKeyService $service, AuditLogService $auditLog): RedirectResponse
    {
        $ownerType = $request->validated('owner_type');
        $ownerId = $request->validated('owner_id');
        $scopes = $request->validated('scopes');
        $requireSignature = (bool) $request->boolean('require_signature');

        [$owner, $allowedScopes] = $this->resolveOwner($ownerType, $ownerId);

        foreach ($scopes as $scope) {
            if (!in_array($scope, $allowedScopes, true)) {
                return back()->withErrors(['scopes' => 'Scope tidak sesuai dengan tipe owner.'])->withInput();
            }
        }

        [$apiKey, $token, $signatureSecret] = $service->create(
            $owner,
            $request->validated('name'),
            $scopes,
            $requireSignature
        );

        $auditLog->log($request->user(), 'api_key.create', $apiKey, [
            'owner_type' => get_class($owner),
            'owner_id' => $owner->getKey(),
            'scopes' => $scopes,
        ], $request);

        return redirect()
            ->route('admin.api_keys.index')
            ->with('status', 'API key berhasil dibuat.')
            ->with('token', $token)
            ->with('signature_secret', $signatureSecret)
            ->with('created_id', $apiKey->id);
    }

    public function revoke(ApiKey $apiKey, ApiKeyService $service, AuditLogService $auditLog): RedirectResponse
    {
        $service->revoke($apiKey);

        $auditLog->log(request()->user(), 'api_key.revoke', $apiKey, [
            'owner_type' => $apiKey->owner_type,
            'owner_id' => $apiKey->owner_id,
        ], request());

        return back()->with('status', 'API key berhasil dicabut.');
    }

    public function rotateSignature(ApiKey $apiKey, ApiKeyService $service, AuditLogService $auditLog): RedirectResponse
    {
        $signatureSecret = $service->rotateSignatureSecret($apiKey);

        $auditLog->log(request()->user(), 'api_key.rotate_signature', $apiKey, [
            'owner_type' => $apiKey->owner_type,
            'owner_id' => $apiKey->owner_id,
        ], request());

        return back()
            ->with('status', 'Signature secret berhasil dirotasi.')
            ->with('signature_secret', $signatureSecret);
    }

    public function downloadPostmanCollection(ApiDocumentationService $apiDocumentationService): StreamedResponse
    {
        $fileName = 'platform-keperawatan-api-v1.postman_collection.json';

        return response()->streamDownload(function () use ($apiDocumentationService): void {
            echo json_encode(
                $apiDocumentationService->postmanCollectionV1(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
        }, $fileName, [
            'Content-Type' => 'application/json',
        ]);
    }

    private function resolveOwner(string $type, string $id): array
    {
        return match ($type) {
            'institusi' => [
                InstitusiPendidikan::findOrFail($id),
                ['lulusan:write'],
            ],
            'lembaga' => [
                LembagaPelatihan::findOrFail($id),
                ['sertifikasi:write'],
            ],
            'regulator' => [
                User::where('role', User::ROLE_REGULATOR)->findOrFail($id),
                ['legalitas:write'],
            ],
            'regulator_org' => [
                RegulatorOrganisasi::findOrFail($id),
                ['legalitas:write'],
            ],
        };
    }
}
