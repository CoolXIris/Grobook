<?php

namespace Tests\Concerns;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\UploadedFile;

trait InteractsWithPublications
{
    /**
     * @return array{0: Team, 1: User, 2: User}
     */
    private function makeTeam(): array
    {
        $team = Team::factory()->create();
        $ketuaTim = $team->ketuaTim;
        $ketuaTim->update(['team_id' => $team->id]);
        $author = User::factory()->inTeam($team)->create();

        return [$team, $ketuaTim, $author];
    }

    private function pdfUpload(string $name = 'naskah.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF"
        );
    }
}
