<?php

namespace App\Http\Controllers;

use App\Enums\PublicationStatus;
use App\Models\Publication;
use App\Services\DocumentStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class DocumentController extends Controller
{
    public function __construct(private DocumentStorage $documentStorage) {}

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

        $fileContents = [];

        if ($publication->latestFile !== null) {
            $content = $this->documentStorage->getContents($publication->latestFile->file_path);
            if ($content !== null) {
                $fileContents['Naskah Final - '.$publication->number.'.pdf'] = $content;
            }
        }

        if ($publication->latestQualityCard?->pdf_path !== null) {
            $content = $this->documentStorage->getContents($publication->latestQualityCard->pdf_path);
            if ($content !== null) {
                $fileContents['Kartu Kendali - '.$publication->number.'.pdf'] = $content;
            }
        }

        abort_if($fileContents === [], 404, 'Dokumen final tidak ditemukan.');

        $zipPath = tempnam(sys_get_temp_dir(), 'grobook-').'.zip';
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($fileContents as $name => $bytes) {
            $zip->addFromString($name, $bytes);
        }

        $zip->close();

        return response()->download($zipPath, 'Dokumen Publikasi '.$publication->number.'.zip')->deleteFileAfterSend();
    }
}
