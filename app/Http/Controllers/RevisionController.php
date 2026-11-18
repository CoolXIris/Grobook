<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRevisionRequest;
use App\Models\Publication;
use App\Services\PublicationWorkflow;
use Illuminate\Http\RedirectResponse;

class RevisionController extends Controller
{
    public function store(StoreRevisionRequest $request, Publication $publication, PublicationWorkflow $workflow): RedirectResponse
    {
        $workflow->requestRevision(
            $publication,
            $request->user(),
            $request->string('comment')->toString(),
            $request->file('file'),
        );

        return redirect()->route('publications.show', $publication)->with('success', 'Permintaan revisi dikirim. Naskah dikembalikan ke tim pembuat naskah.');
    }
}
