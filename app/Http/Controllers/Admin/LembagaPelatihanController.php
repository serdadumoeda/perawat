<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLembagaPelatihanRequest;
use App\Models\LembagaPelatihan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LembagaPelatihanController extends Controller
{
    public function index(): View
    {
        $lembaga = LembagaPelatihan::latest()->paginate(15);

        return view('admin.organisasi.lembaga', compact('lembaga'));
    }

    public function create(): View
    {
        return view('admin.organisasi.lembaga_create');
    }

    public function store(StoreLembagaPelatihanRequest $request): RedirectResponse
    {
        LembagaPelatihan::create($request->validated());

        return redirect()->route('admin.lembaga.index')->with('status', 'Lembaga pelatihan ditambahkan.');
    }
}
