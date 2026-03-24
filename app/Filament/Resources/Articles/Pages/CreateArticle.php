<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\MediaAsset;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    /**
     * Handle new image upload before creating
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle new image upload
        if (!empty($data['upload_new_image']) && $data['upload_new_image'] instanceof TemporaryUploadedFile) {
            $file = $data['upload_new_image'];
            
            $originalName = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension());
            
            $baseName = pathinfo($originalName, PATHINFO_FILENAME);
            $cleanName = Str::slug($baseName) . '-' . Str::random(8) . '.' . $ext;
            $storedPath = 'media/' . $cleanName;
            
            // Store the file
            $file->storeAs('media', $cleanName, 'public');
            
            // Get clean title from filename
            $cleanTitle = Str::of($baseName)
                ->replace(['-', '_'], ' ')
                ->title()
                ->toString();

            // Create MediaAsset
            $mediaAsset = MediaAsset::create([
                'title' => $cleanTitle,
                'file' => $storedPath,
                'extension' => $ext,
                'alt_text' => null,
                'content' => null,
            ]);

            // Set both fields for dual compatibility
            $data['media_asset_id'] = $mediaAsset->id;
            $data['image'] = $storedPath;
        }

        // Remove the upload field from data being saved
        unset($data['upload_new_image']);
        unset($data['current_featured_image']);

        return $data;
    }
}