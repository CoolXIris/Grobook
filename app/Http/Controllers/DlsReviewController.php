<?php

namespace App\Http\Controllers;

use App\Enums\PublicationStatus;
use App\Models\Publication;
use App\Services\PublicationWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DlsReviewController extends Controller
{
    public function index(Request $request): View
    {
        $publications = Publication::withStatus(PublicationStatus::ReviewDls)
            ->with(['user', 'team', 'latestFile', 'latestQualityCard'])
            ->latest()
            ->orderByDesc('id')
            ->paginate(15);

        return view('reviews.dls-index', ['publications' => $publications]);
    }

    public function forward(Request $request, Publication $publication, PublicationWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('reviewDls', $publication);

        $workflow->forwardToFinal($publication, $request->user());

        return redirect()->route('publications.show', $publication)->with('success', 'Naskah diteruskan ke Ketua DLS untuk pengecekan akhir.');
    }
}
