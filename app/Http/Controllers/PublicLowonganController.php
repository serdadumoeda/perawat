<?php

namespace App\Http\Controllers;

use App\Models\Lowongan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicLowonganController extends Controller
{
    public function index(Request $request): View
    {
        $query = Lowongan::query()
            ->with('mitraPengguna')
            ->where('status', 'dibuka')
            ->whereNotNull('published_at');

        if ($request->filled('q')) {
            $query->where('judul', 'like', '%'.$request->string('q').'%');
        }

        if ($request->filled('tipe_penempatan')) {
            $query->where('tipe_penempatan', $request->string('tipe_penempatan'));
        }

        $lowongan = $query->latest()->paginate(12)->withQueryString();

        return view('lowongan.public_index', compact('lowongan'));
    }

    public function show(Lowongan $lowongan): View
    {
        if ($lowongan->status !== 'dibuka' || !$lowongan->published_at) {
            abort(404);
        }

        $lowongan->load('mitraPengguna');

        return view('lowongan.public_show', compact('lowongan'));
    }
}
