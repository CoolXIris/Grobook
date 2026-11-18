<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQualityCardRequest;
use App\Models\ChecklistSection;
use App\Models\Publication;
use App\Services\PublicationWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class QualityCardController extends Controller
{
    public function create(Request $request, Publication $publication): View
    {
        Gate::authorize('reviewContent', $publication);

        $publication->load(['user', 'team', 'latestFile']);

        $sections = ChecklistSection::orderBy('position')
            ->with(['activeItems'])
            ->get()
            ->filter(fn (ChecklistSection $section) => $section->activeItems->isNotEmpty());

        return view('reviews.kartu-kendali', [
            'publication' => $publication,
            'sections' => $sections,
        ]);
    }

    public function store(StoreQualityCardRequest $request, Publication $publication, PublicationWorkflow $workflow): RedirectResponse
    {
        $workflow->approveContent($publication, $request->user(), $request->input('answers'));

        return redirect()->route('publications.show', $publication)->with('success', 'Kartu kendali berhasil dibuat dan publikasi diteruskan ke Petugas DLS.');
    }
}
