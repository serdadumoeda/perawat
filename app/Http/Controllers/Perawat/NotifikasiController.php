<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Models\DataLegalitas;
use App\Support\NotificationQuery;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $tipe = (string) $request->query('tipe', 'all');
        $legalitasStatus = (string) $request->query('legalitas_status', 'all');

        $notifications = $user->notifications()
            ->when($tipe === 'legalitas', function ($query) {
                NotificationQuery::whereDataIn($query, 'tipe', ['legalitas_document_status', 'legalitas_data_status']);
            })
            ->when($legalitasStatus !== 'all', function ($query) use ($legalitasStatus) {
                NotificationQuery::whereDataEquals($query, 'status', $legalitasStatus);
                NotificationQuery::whereDataIn($query, 'tipe', ['legalitas_document_status', 'legalitas_data_status']);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $unreadCount = $user->unreadNotifications()->count();

        return view('perawat.notifikasi', [
            'notifications' => $notifications,
            'tipe' => $tipe,
            'legalitasStatus' => $legalitasStatus,
            'legalitasStatusOptions' => array_values(array_unique(array_merge(
                DataLegalitas::dataStatusOptions(),
                DataLegalitas::docStatusOptions()
            ))),
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $scope = (string) $request->input('scope', 'all');

        if ($scope === 'legalitas') {
            $unreadLegalitas = $request->user()->unreadNotifications();
            NotificationQuery::whereDataIn($unreadLegalitas, 'tipe', ['legalitas_document_status', 'legalitas_data_status']);
            $unreadLegalitas->update(['read_at' => now()]);
        } else {
            $request->user()->unreadNotifications()->update(['read_at' => now()]);
        }

        return back()->with('status', 'Notifikasi ditandai sudah dibaca.');
    }
}
