<?php

namespace App\Http\Controllers;

use App\Enums\PublicationStatus;
use App\Enums\PublicationType;
use App\Http\Requests\StorePublicationRequest;
use App\Http\Requests\UpdatePublicationRequest;
use App\Models\ArcPublication;
use App\Models\Publication;
use App\Services\PublicationWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PublicationController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Publication::class);

        $status = $request->string('status')->toString();
        $statusEnum = PublicationStatus::tryFrom($status);

        $publications = Publication::visibleTo($request->user())
            ->with(['user', 'team', 'latestFile'])
            ->when($statusEnum !== null, fn ($query) => $query->where('status', $statusEnum))
            ->latest()
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('publications.index', [
            'publications' => $publications,
            'statusFilter' => $statusEnum,
            'statuses' => PublicationStatus::cases(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->user()->can('create', Publication::class)) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum terdaftar di sebuah tim. Hubungi ketua tim untuk mendaftarkan akun Google Anda sebelum mengajukan publikasi.');
        }

        return view('publications.create');
    }

    public function createForm(Request $request, string $type): View|RedirectResponse
    {
        $typeEnum = PublicationType::tryFrom($type);
        abort_if($typeEnum === null, 404);

        if (! $request->user()->can('create', Publication::class)) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum terdaftar di sebuah tim. Hubungi ketua tim untuk mendaftarkan akun Google Anda sebelum mengajukan publikasi.');
        }

        return view('publications.form', [
            'type' => $typeEnum,
            'publication' => null,
            'arcTitles' => $typeEnum === PublicationType::Arc ? ArcPublication::active()->orderBy('title')->get() : collect(),
            'openRevision' => null,
        ]);
    }

    public function store(StorePublicationRequest $request, PublicationWorkflow $workflow): RedirectResponse
    {
        $data = $request->validated();
        $attributes = Arr::except($data, ['file', 'drive_link']);

        if ($request->enum('type', PublicationType::class) === PublicationType::Arc) {
            $arc = ArcPublication::active()->findOrFail($data['arc_publication_id']);
            $attributes['title'] = $arc->title;
            $attributes['title_en'] = $arc->title_en;
        } else {
            $attributes['arc_publication_id'] = null;
        }

        $publication = $workflow->submit(
            $request->user(),
            $attributes,
            $data['drive_link'],
            $request->file('file'),
        );

        return redirect()->route('publications.show', $publication)->with('success', 'Pengajuan publikasi berhasil disimpan dan diteruskan ke ketua tim untuk pemeriksaan konten.');
    }

    public function show(Request $request, Publication $publication): View
    {
        Gate::authorize('view', $publication);

        $publication->load([
            'user',
            'team',
            'arcPublication',
            'files.uploader',
            'latestFile',
            'revisions.requester',
            'openRevision',
            'qualityCards.ketuaTim',
            'latestQualityCard',
            'timelineEvents.user',
        ]);

        return view('publications.show', ['publication' => $publication]);
    }

    public function edit(Request $request, Publication $publication): View
    {
        Gate::authorize('update', $publication);

        $publication->load(['latestFile', 'openRevision.requester']);

        return view('publications.form', [
            'type' => $publication->type,
            'publication' => $publication,
            'arcTitles' => $publication->type === PublicationType::Arc ? ArcPublication::active()->orderBy('title')->get() : collect(),
            'openRevision' => $publication->openRevision,
        ]);
    }

    public function update(UpdatePublicationRequest $request, Publication $publication, PublicationWorkflow $workflow): RedirectResponse
    {
        $data = $request->validated();
        $attributes = Arr::except($data, ['file', 'drive_link']);

        if ($publication->type === PublicationType::Arc) {
            $arc = ArcPublication::active()->findOrFail($data['arc_publication_id']);
            $attributes['title'] = $arc->title;
            $attributes['title_en'] = $arc->title_en;
        } else {
            $attributes['arc_publication_id'] = null;
        }

        $workflow->resubmit(
            $publication,
            $request->user(),
            $attributes,
            $data['drive_link'],
            $request->file('file'),
        );

        return redirect()->route('publications.show', $publication)->with('success', 'Naskah hasil revisi berhasil diajukan ulang.');
    }
}
