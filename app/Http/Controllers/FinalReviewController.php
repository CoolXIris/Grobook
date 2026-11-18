<?php

namespace App\Http\Controllers;

use App\Enums\PublicationStatus;
use App\Models\Publication;
use App\Services\PublicationWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FinalReviewController extends Controller
{
    public function index(Request $request): View
    {
        $publications = Publication::withStatus(PublicationStatus::ReviewAkhir)
            ->with(['user', 'team', 'latestFile', 'latestQualityCard'])
            ->latest()
            ->orderByDesc('id')
            ->paginate(15);

        return view('reviews.final-index', ['publications' => $publications]);
    }

    public function approve(Request $request, Publication $publication, PublicationWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('reviewFinal', $publication);

        $workflow->approveFinal($publication, $request->user());

        return redirect()->route('publications.show', $publication)->with('success', 'Publikasi disetujui. Status naskah menjadi Selesai dan dokumen dapat diunduh oleh Petugas DLS.');
    }
}
