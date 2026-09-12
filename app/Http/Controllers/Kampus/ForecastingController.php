<?php

namespace App\Http\Controllers\Kampus;

use App\Http\Controllers\Controller;
use App\Models\InstitusiPendidikan;
use App\Services\ForecastingSupplyDemandService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForecastingController extends Controller
{
    public function __construct(private ForecastingSupplyDemandService $forecastingService)
    {
    }

    /**
     * Render Supply vs Demand & Graduate Forecasting Analytics Dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $institusiList = $user->institusiPendidikan()->orderBy('nama')->get();
        $selectedId = $request->query('institusi_id') ?: $institusiList->first()?->id;

        // If user is Admin or Regulator without explicit institusi, allow overall ecosystem view
        if (!$selectedId && in_array($user->role, ['admin', 'regulator'])) {
            $selectedId = null;
        }

        $analytics = $this->forecastingService->analyze($selectedId);

        return view('kampus.forecasting', [
            'institusiList' => $institusiList,
            'selectedId' => $selectedId,
            'analytics' => $analytics,
        ]);
    }
}
