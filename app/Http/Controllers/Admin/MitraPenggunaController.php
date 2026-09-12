<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMitraPenggunaRequest;
use App\Models\MitraPengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MitraPenggunaController extends Controller
{
    public function index(): View
    {
        $mitra = MitraPengguna::latest()->paginate(15);

        return view('admin.organisasi.mitra', compact('mitra'));
    }

    public function create(): View
    {
        return view('admin.organisasi.mitra_create');
    }

    public function store(StoreMitraPenggunaRequest $request): RedirectResponse
    {
        MitraPengguna::create($request->validated());

        return redirect()->route('admin.mitra.index')->with('status', 'Mitra pengguna ditambahkan.');
    }
}
