<?php

declare(strict_types=1);

namespace App\Support\Media;

use App\Models\MediaAsset;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class EditorAttachments
{
    /**
     * Stores an uploaded file for a Filament RichEditor attachment, creates a MediaAsset record,
     * and returns the URL that should be inserted into the editor content.
     */
    public static function storeAndCreateAsset(TemporaryUploadedFile $file, string $directory = 'media'): string
    {
        $original = $file->getClientOriginalName();

        $ext = strtolower(
            $file->getClientOriginalExtension()
            ?: pathinfo($original, PATHINFO_EXTENSION)
            ?: 'jpg'
        );

        $base = Str::slug(pathinfo($original, PATHINFO_FILENAME));
        $filename = ($base ?: 'image') . '-' . Str::random(8) . '.' . $ext;

        // Store to: storage/app/public/{directory}/{filename}
        // Public URL becomes: /storage/{directory}/{filename}
        $storedPath = $file->storePubliclyAs($directory, $filename, 'public'); // e.g. "media/foo.jpg"

        // Create media asset row (DB stores basename like legacy CMS)
        MediaAsset::create([
            'title'     => Str::of($base ?: 'image')->replace(['-', '_'], ' ')->title(),
            'file'      => basename($storedPath), // ✅ "foo.jpg"
            'extension' => $ext,
            'alt_text'  => null,
            'content'   => null,
        ]);

        // Return the URL to be inserted in the RichEditor HTML
        return $storedPath;
    }

    /**
     * Ensures RichEditor attachment previews use your consistent URL format.
     */
    public static function attachmentUrl(string $filePath): string
    {
        $filePath = ltrim($filePath, '/');

        // If already in /storage/... format, keep it
        if (Str::startsWith($filePath, 'storage/')) {
            return '/' . $filePath;
        }

        // Filament often passes "media/foo.jpg" here
        return '/storage/' . $filePath;
    }
}