<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRegulatorOrganisasiRequest;
use App\Models\RegulatorOrganisasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegulatorOrganisasiController extends Controller
{
    public function index(): View
    {
        $organisasi = RegulatorOrganisasi::latest()->paginate(15);

        return view('admin.organisasi.regulator', compact('organisasi'));
    }

    public function create(): View
    {
        return view('admin.organisasi.regulator_create');
    }

    public function store(StoreRegulatorOrganisasiRequest $request): RedirectResponse
    {
        RegulatorOrganisasi::create(array_merge($request->validated(), [
            'is_active' => $request->boolean('is_active', true),
        ]));

        return redirect()->route('admin.regulator_org.index')->with('status', 'Organisasi regulator ditambahkan.');
    }
}
