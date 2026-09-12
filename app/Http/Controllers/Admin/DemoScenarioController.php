<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoScenarioRun;
use App\Services\DemoScenarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class DemoScenarioController extends Controller
{
    public function index(Request $request): View
    {
        $runs = DemoScenarioRun::query()
            ->withCount('events')
            ->latest('started_at')
            ->paginate(12)
            ->withQueryString();

        $selectedRun = null;
        $selectedRunId = trim((string) $request->query('run'));

        if ($selectedRunId !== '') {
            $selectedRun = DemoScenarioRun::query()
                ->with('events')
                ->find($selectedRunId);
        }

        if (!$selectedRun) {
            $selectedRun = DemoScenarioRun::query()
                ->with('events')
                ->latest('started_at')
                ->first();
        }

        return view('admin.demo-scenario.index', [
            'runs' => $runs,
            'selectedRun' => $selectedRun,
            'defaultNik' => DemoScenarioService::DEFAULT_PERAWAT_NIK,
        ]);
    }

    public function store(Request $request, DemoScenarioService $service): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['nullable', 'digits:16'],
        ]);

        try {
            $run = $service->runSinglePerawatTimeline($validated['nik'] ?? null);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'nik' => 'Demo scenario gagal dijalankan: '.$exception->getMessage(),
                ]);
        }

        return redirect()
            ->route('admin.demo_scenario.index', ['run' => $run->id])
            ->with('status', 'Demo scenario berhasil dijalankan.');
    }
}

