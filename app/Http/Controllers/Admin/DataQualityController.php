<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DataQualityMetricsService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataQualityController extends Controller
{
    public function __construct(private readonly DataQualityMetricsService $metricsService)
    {
    }

    public function index(): View
    {
        $snapshot = $this->metricsService->snapshot();

        return view('admin.data_quality', [
            'summary' => $snapshot['summary'],
            'completeness' => $snapshot['completeness'],
            'verification' => $snapshot['verification'],
            'actorCoverage' => $snapshot['actorCoverage'],
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $snapshot = $this->metricsService->snapshot();

        $filename = 'data-quality-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($snapshot) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Summary Metric', 'Value']);
            foreach ($snapshot['summary'] as $key => $value) {
                fputcsv($handle, [$key, $value]);
            }

            fputcsv($handle, []);

            fputcsv($handle, ['Completeness KPI', 'Filled', 'Population', 'Missing', 'Rate (%)']);
            foreach ($snapshot['completeness'] as $row) {
                fputcsv($handle, [
                    $row['label'],
                    $row['filled'],
                    $row['population'],
                    $row['missing'],
                    $row['rate'],
                ]);
            }

            fputcsv($handle, []);

            fputcsv($handle, ['Verified Rate KPI', 'Verified', 'Pending', 'Rejected', 'Total', 'Rate (%)']);
            foreach ($snapshot['verification'] as $row) {
                fputcsv($handle, [
                    $row['label'],
                    $row['verified'],
                    $row['pending'],
                    $row['rejected'],
                    $row['total'],
                    $row['rate'],
                ]);
            }

            fputcsv($handle, []);

            fputcsv($handle, ['Actor Coverage KPI', 'Records', 'Covered Perawat', 'Coverage Rate (%)']);
            foreach ($snapshot['actorCoverage'] as $row) {
                fputcsv($handle, [
                    $row['label'],
                    $row['records'],
                    $row['covered_perawat'],
                    $row['coverage_rate'],
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
