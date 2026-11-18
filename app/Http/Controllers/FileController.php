<?php

namespace App\Http\Controllers;

use App\Models\PublicationFile;
use App\Models\QualityCard;
use App\Models\Revision;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController extends Controller
{
    public function publicationFile(PublicationFile $publicationFile): BinaryFileResponse
    {
        Gate::authorize('view', $publicationFile->publication);

        return $this->serve($publicationFile->file_path, $publicationFile->original_name);
    }

    public function revisionFile(Revision $revision): BinaryFileResponse
    {
        Gate::authorize('view', $revision->publication);

        abort_if($revision->file_path === null, 404);

        return $this->serve($revision->file_path, $revision->original_name ?? 'detail-revisi.pdf');
    }

    public function qualityCard(QualityCard $qualityCard): BinaryFileResponse
    {
        Gate::authorize('view', $qualityCard->publication);

        abort_if($qualityCard->pdf_path === null, 404);

        return $this->serve($qualityCard->pdf_path, 'Kartu Kendali '.$qualityCard->publication->number.'.pdf');
    }

    private function serve(string $path, string $name): BinaryFileResponse
    {
        abort_unless(Storage::disk('local')->exists($path), 404, 'File tidak ditemukan.');

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.addslashes($name).'"',
        ]);
    }
}
