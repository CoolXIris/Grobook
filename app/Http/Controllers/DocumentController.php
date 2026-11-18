<?php

namespace App\Http\Controllers;

use App\Enums\PublicationStatus;
use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class DocumentController extends Controller
{
    public function index(): View
    {
        $publications = Publication::withStatus(PublicationStatus::Selesai)
            ->with(['user', 'team', 'latestFile', 'latestQualityCard'])
            ->latest('completed_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('documents.index', ['publications' => $publications]);
    }

    public function download(Request $request, Publication $publication): BinaryFileResponse
    {
        Gate::authorize('downloadDocuments', $publication);

        $publication->load(['latestFile', 'latestQualityCard']);

        $files = [];

        if ($publication->latestFile !== null && Storage::disk('local')->exists($publication->latestFile->file_path)) {
            $files['Naskah Final - '.$publication->number.'.pdf'] = Storage::disk('local')->path($publication->latestFile->file_path);
        }

        if ($publication->latestQualityCard?->pdf_path !== null && Storage::disk('local')->exists($publication->latestQualityCard->pdf_path)) {
            $files['Kartu Kendali - '.$publication->number.'.pdf'] = Storage::disk('local')->path($publication->latestQualityCard->pdf_path);
        }

        abort_if($files === [], 404, 'Dokumen final tidak ditemukan.');

        $zipPath = tempnam(sys_get_temp_dir(), 'grobook-').'.zip';
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($files as $name => $path) {
            $zip->addFile($path, $name);
        }

        $zip->close();

        return response()->download($zipPath, 'Dokumen Publikasi '.$publication->number.'.zip')->deleteFileAfterSend();
    }
}
