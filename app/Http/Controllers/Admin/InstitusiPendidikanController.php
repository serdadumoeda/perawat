<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInstitusiPendidikanRequest;
use App\Models\InstitusiPendidikan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InstitusiPendidikanController extends Controller
{
    public function index(): View
    {
        $institusi = InstitusiPendidikan::latest()->paginate(15);

        return view('admin.organisasi.institusi', compact('institusi'));
    }

    public function create(): View
    {
        return view('admin.organisasi.institusi_create');
    }

    public function store(StoreInstitusiPendidikanRequest $request): RedirectResponse
    {
        InstitusiPendidikan::create($request->validated());

        return redirect()->route('admin.institusi.index')->with('status', 'Institusi pendidikan ditambahkan.');
    }
}
