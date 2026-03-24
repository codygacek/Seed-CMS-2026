<?php

namespace App\Filament\Resources\ImageCollections\Pages;

use App\Filament\Resources\ImageCollections\ImageCollectionResource;
use App\Models\ImageCollectionItem;
use App\Models\MediaAsset;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EditImageCollection extends EditRecord
{
    protected static string $resource = ImageCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle bulk image uploads
        if (!empty($data['bulk_images']) && is_array($data['bulk_images'])) {
            $collection = $this->getRecord();
            $maxPosition = (int) $collection->image_items()->max('position');

            foreach ($data['bulk_images'] as $file) {
                // If it's a TemporaryUploadedFile, we need to get the original name
                if ($file instanceof TemporaryUploadedFile) {
                    $originalName = $file->getClientOriginalName();
                    $ext = strtolower($file->getClientOriginalExtension());
                    
                    // Create clean filename
                    $baseName = pathinfo($originalName, PATHINFO_FILENAME);
                    $cleanName = Str::slug($baseName) . '-' . Str::random(8) . '.' . $ext;
                    $storedPath = 'media/' . $cleanName;
                    
                    // Store the file with our clean name
                    $file->storeAs('/media', $cleanName, 'public');
                    
                } else {
                    // It's already a stored path string
                    $storedPath = $file;
                    $originalName = basename($file);
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                }

                // Skip if already processed
                if ($collection->image_items()->where('image', $storedPath)->exists()) {
                    continue;
                }

                // Get clean title from original filename
                $baseName = pathinfo($originalName, PATHINFO_FILENAME);
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

                // Create ImageCollectionItem with BOTH fields for legacy support
                $maxPosition++;
                ImageCollectionItem::create([
                    'image_collection_id' => $collection->id,
                    'media_asset_id' => $mediaAsset->id,
                    'image' => $storedPath, // Legacy field
                    'title' => $cleanTitle,
                    'position' => $maxPosition,
                    'content' => null,
                    'link' => null,
                ]);
            }
        }

        // Remove bulk_images from data being saved to ImageCollection
        unset($data['bulk_images']);
        unset($data['image_items']); // Repeater handles its own saves

        return $data;
    }

    protected function afterSave(): void
    {
        // Refresh the record to get the new image_items
        $this->record->refresh();
        
        // Clear the bulk_images uploader and reload the form
        $this->form->fill([
            ...$this->record->toArray(),
            'bulk_images' => [], // Clear the uploader
        ]);
        
        // Force a page refresh to show updated repeater
        $this->dispatch('$refresh');
    }
}