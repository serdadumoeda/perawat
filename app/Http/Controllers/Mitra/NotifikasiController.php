<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $user->unreadNotifications->markAsRead();

        return view('mitra.notifikasi', [
            'notifications' => $user->notifications()->latest()->paginate(15),
        ]);
    }
}
