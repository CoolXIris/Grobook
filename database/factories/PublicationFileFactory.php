<?php

namespace Database\Factories;

use App\Models\Publication;
use App\Models\PublicationFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PublicationFile>
 */
class PublicationFileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'publication_id' => Publication::factory(),
            'version' => 1,
            'file_path' => 'publications/'.fake()->uuid().'.pdf',
            'original_name' => 'naskah-publikasi.pdf',
            'drive_link' => 'https://drive.google.com/drive/folders/'.fake()->uuid(),
            'uploaded_by' => User::factory(),
        ];
    }
}