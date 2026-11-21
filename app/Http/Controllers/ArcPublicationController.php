<?php

namespace App\Http\Controllers;

use App\Models\ArcPublication;
use App\Models\Publication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArcPublicationController extends Controller
{
    public function index(): View
    {
        $arcPublications = ArcPublication::with('team')->orderBy('title')->paginate(20);

        return view('arc.index', ['arcPublications' => $arcPublications]);
    }

    public function create(): View
    {
        return view('arc.form', ['arcPublication' => new ArcPublication(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        ArcPublication::create($this->validated($request));

        return redirect()->route('arc-publications.index')->with('success', 'Judul publikasi ARC berhasil ditambahkan.');
    }

    public function edit(ArcPublication $arcPublication): View
    {
        return view('arc.form', ['arcPublication' => $arcPublication]);
    }

    public function update(Request $request, ArcPublication $arcPublication): RedirectResponse
    {
        $arcPublication->update($this->validated($request));

        return redirect()->route('arc-publications.index')->with('success', 'Judul publikasi ARC berhasil diperbarui.');
    }

    public function destroy(ArcPublication $arcPublication): RedirectResponse
    {
        if (Publication::where('arc_publication_id', $arcPublication->id)->exists()) {
            return redirect()->route('arc-publications.index')->with('error', 'Judul sudah dipakai pada pengajuan publikasi sehingga tidak dapat dihapus. Nonaktifkan saja.');
        }

        $arcPublication->delete();

        return redirect()->route('arc-publications.index')->with('success', 'Judul publikasi ARC berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:500'],
            'title_en' => ['required', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
