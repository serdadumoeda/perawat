<?php

namespace App\Http\Controllers\Kampus;

use App\Http\Controllers\Controller;
use App\Models\InstitusiPendidikan;
use App\Services\AuditLogService;
use App\Services\KampusGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GatewayController extends Controller
{
    public function __construct(
        private KampusGatewayService $gatewayService,
        private AuditLogService $auditLog
    ) {
    }

    /**
     * Display Gateway NIK Search UI.
     */
    public function index(Request $request): View
    {
        $nik = $request->query('nik');
        $result = null;

        if ($nik) {
            $result = $this->gatewayService->lookupByNik((string)$nik);
        }

        $institusiList = InstitusiPendidikan::query()->orderBy('nama')->get();

        return view('kampus.gateway', [
            'nik' => $nik,
            'result' => $result,
            'institusiList' => $institusiList,
        ]);
    }

    /**
     * AJAX/Form Lookup by NIK.
     */
    public function lookup(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'nik' => ['required', 'string', 'min:16', 'max:16'],
        ]);

        $nik = (string) $request->input('nik');
        $result = $this->gatewayService->lookupByNik($nik);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('kampus.gateway', ['nik' => $nik]);
    }

    /**
     * Import / Provision Graduate record from Gateway by NIK.
     */
    public function ingest(Request $request): RedirectResponse
    {
        $request->validate([
            'nik' => ['required', 'string', 'size:16'],
            'nama' => ['required', 'string', 'max:255'],
            'institusi_id' => ['nullable', 'exists:institusi_pendidikan,id'],
            'jenjang' => ['nullable', 'string'],
            'program_studi' => ['nullable', 'string'],
            'nomor_ijazah' => ['nullable', 'string'],
            'ipk' => ['nullable', 'numeric'],
        ]);

        $nik = (string) $request->input('nik');
        $institusi = $request->input('institusi_id') ? InstitusiPendidikan::find($request->input('institusi_id')) : null;

        $res = $this->gatewayService->ingestByNik($nik, $request->all(), $institusi, $request->user());

        $this->auditLog->log($request->user(), 'kampus.gateway.ingest', null, [
            'nik' => $nik,
            'action' => $res['action'],
        ], $request);

        return redirect()
            ->route('kampus.gateway', ['nik' => $nik])
            ->with('status', 'Data lulusan perawat dengan NIK '.$nik.' berhasil diimpor & terverifikasi.');
    }
}
