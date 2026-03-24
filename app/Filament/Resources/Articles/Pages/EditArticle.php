<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\MediaAsset;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    public int $featuredUploadNonce = 0;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Handle new image upload before saving
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle new image upload
        if (!empty($data['upload_new_image']) && $data['upload_new_image'] instanceof TemporaryUploadedFile) {
            $file = $data['upload_new_image'];
            
            $originalName = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension());
            
            $baseName = pathinfo($originalName, PATHINFO_FILENAME);
            $cleanName = Str::slug($baseName) . '-' . Str::random(8) . '.' . $ext;
            $storedPath = '/media/' . $cleanName;
            
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