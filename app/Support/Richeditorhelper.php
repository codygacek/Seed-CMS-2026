<?php

namespace App\Support;

use App\Models\MediaAsset;
use Filament\Forms\Components\RichEditor;
use Illuminate\Support\Str;

class RichEditorHelper
{
    /**
     * Create a RichEditor with MediaAsset creation on upload
     * 
     * @param string $name Field name
     * @return RichEditor
     */
    public static function make(string $name): RichEditor
    {
        return RichEditor::make($name)
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsDirectory('media')
            ->fileAttachmentsVisibility('public')
            ->saveUploadedFileAttachmentsUsing(function ($file): string {
                // Generate our custom filename
                $original = $file->getClientOriginalName();
                $base = Str::slug(pathinfo($original, PATHINFO_FILENAME));
                $ext = strtolower($file->getClientOriginalExtension());
                $filename = ($base ?: 'image') . '-' . Str::random(8) . ".{$ext}";
                
                // Store the file with our custom name
                $storedPath = $file->storePubliclyAs('media', $filename, 'public');
                
                // Create MediaAsset record
                MediaAsset::create([
                    'title' => Str::of($base ?: 'image')->replace(['-', '_'], ' ')->title(),
                    'file' => $filename,
                    'extension' => $ext,
                    'alt_text' => null,
                    'content' => null,
                ]);
                
                // Return the path (Filament converts to URL)
                return $storedPath;
            });
    }
}