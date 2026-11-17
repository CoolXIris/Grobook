<?php

namespace App\Models;

use Database\Factories\PublicationFileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['publication_id', 'version', 'file_path', 'original_name', 'drive_link', 'uploaded_by'])]
class PublicationFile extends Model
{
    /** @use HasFactory<PublicationFileFactory> */
    use HasFactory;

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}