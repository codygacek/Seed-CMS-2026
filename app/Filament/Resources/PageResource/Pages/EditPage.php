<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\AuthToken;
use App\Models\MediaAsset;
use App\Models\Slide;
use App\Models\Slider;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;
    
    private $passwordToUpdate;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        \Log::info('=== mutateFormDataBeforeFill START ===');
        
        // Don't show the real password in the form
        unset($data['password']);
        
        // Load slides if slider exists
        if ($this->record->slider) {
            $slides = $this->record->slider->slides()->orderBy('position')->get();
            
            \Log::info('Loading existing slides', ['count' => $slides->count()]);
            
            $data['slides'] = $slides->map(function ($slide) {
                return [
                    'id' => $slide->id,
                    'slider_id' => $slide->slider_id,
                    'media_asset_id' => $slide->media_asset_id,
                    'image' => $slide->image,
                    'title' => $slide->title,
                    'content' => $slide->content,
                    'link' => $slide->link,
                    'position' => $slide->position,
                ];
            })->toArray();
        } else {
            $data['slides'] = [];
        }
        
        \Log::info('=== mutateFormDataBeforeFill END ===');
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        \Log::info('=== mutateFormDataBeforeSave START ===');
        \Log::info('Received data keys:', array_keys($data));
        
        // Handle password separately
        if (isset($data['password']) && $data['password'] !== '********') {
            $this->passwordToUpdate = $data['password'];
            \Log::info('Password will be updated');
        }
        unset($data['password']);

        // Handle bulk image uploads for slider
        \Log::info('Checking for bulk_images...', [
            'isset' => isset($data['bulk_images']),
            'is_array' => isset($data['bulk_images']) && is_array($data['bulk_images']),
            'count' => isset($data['bulk_images']) && is_array($data['bulk_images']) ? count($data['bulk_images']) : 0,
        ]);
        
        if (!empty($data['bulk_images']) && is_array($data['bulk_images'])) {
            \Log::info('>>> PROCESSING BULK IMAGES <<<');
            
            $page = $this->getRecord();
            \Log::info('Page ID:', ['id' => $page->id]);
            
            // Get or create slider
            $slider = $page->slider;
            if (!$slider) {
                \Log::info('Creating new slider...');
                $slider = Slider::create([
                    'title' => $page->title . ' Slider',
                    'slug' => Str::slug($page->title) . '-slider',
                    'sliderable_id' => $page->id,
                    'sliderable_type' => 'App\\Models\\Page',
                ]);
                \Log::info('Created slider:', ['id' => $slider->id]);
            } else {
                \Log::info('Using existing slider:', ['id' => $slider->id]);
            }
            
            $maxPosition = (int) $slider->slides()->max('position');
            \Log::info('Max position:', ['max' => $maxPosition]);

            foreach ($data['bulk_images'] as $index => $file) {
                \Log::info("--- Processing file {$index} ---");
                \Log::info('File data:', [
                    'type' => gettype($file),
                    'value' => $file,
                ]);
                
                // File path is "media/image-a8b3c9d2.jpg"
                $filename = basename($file);
                \Log::info('Extracted filename:', ['filename' => $filename]);
                
                // MediaAsset was created in saveUploadedFileUsing
                $mediaAsset = MediaAsset::where('file', $filename)->first();
                
                if (!$mediaAsset) {
                    \Log::warning('MediaAsset NOT FOUND! Skipping.', ['filename' => $filename]);
                    
                    // Try alternative searches
                    $withPath = MediaAsset::where('file', $file)->first();
                    \Log::info('Search with full path:', ['found' => $withPath ? 'YES' : 'NO', 'path' => $file]);
                    
                    $likeSearch = MediaAsset::where('file', 'like', "%{$filename}%")->first();
                    \Log::info('Search with LIKE:', ['found' => $likeSearch ? 'YES' : 'NO']);
                    
                    continue;
                }
                
                \Log::info('Found MediaAsset!', ['id' => $mediaAsset->id, 'file' => $mediaAsset->file]);

                // Skip if slide already exists
                $existingSlide = $slider->slides()->where('image', $filename)->first();
                if ($existingSlide) {
                    \Log::info('Slide already exists, skipping', ['slide_id' => $existingSlide->id]);
                    continue;
                }

                $maxPosition++;
                
                \Log::info('Creating slide...', [
                    'slider_id' => $slider->id,
                    'media_asset_id' => $mediaAsset->id,
                    'image' => $filename,
                    'position' => $maxPosition,
                ]);
                
                try {
                    $slide = Slide::create([
                        'slider_id' => $slider->id,
                        'media_asset_id' => $mediaAsset->id,
                        'image' => $file,
                        'title' => $mediaAsset->title,
                        'position' => $maxPosition,
                        'content' => null,
                        'link' => null,
                    ]);
                    
                    \Log::info('✓ Slide created successfully!', ['slide_id' => $slide->id]);
                } catch (\Exception $e) {
                    \Log::error('✗ Failed to create slide!', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
            
            \Log::info('>>> FINISHED PROCESSING BULK IMAGES <<<');

            // Refresh record and form state so repeater reflects new slides
            $this->record->refresh();

            $formData = $this->mutateFormDataBeforeFill($this->record->toArray());

            $this->form->fill($formData);
        } else {
            \Log::info('No bulk_images to process (empty or not set)');
        }

        // Remove slider-related data from page save
        \Log::info('Removing bulk_images and slides from data before page save');
        unset($data['bulk_images']);
        unset($data['slides']);

        \Log::info('=== mutateFormDataBeforeSave END ===');
        
        return $data;
    }

    protected function afterSave(): void
    {
        \Log::info('=== afterSave START ===');
        
        // Update or create auth token if password was provided
        if (!empty($this->passwordToUpdate)) {
            AuthToken::updateOrCreate(
                [
                    'tokenable_id' => $this->record->id,
                    'tokenable_type' => get_class($this->record),
                ],
                [
                    'token' => bcrypt($this->passwordToUpdate),
                ]
            );
            \Log::info('Password updated');
        }

        // Handle slide updates from the repeater
        $state = $this->form->getState();
        \Log::info('Form state:', ['has_slides' => isset($state['slides'])]);
        
        if (isset($state['slides']) && is_array($state['slides']) && $this->record->slider) {
            \Log::info('Processing slide updates from repeater', ['count' => count($state['slides'])]);
            
            $slideIds = [];
            
            foreach ($state['slides'] as $index => $slideData) {
                // Skip if no ID
                if (empty($slideData['id'])) {
                    \Log::info("Skipping slide {$index} (no ID)");
                    continue;
                }
                
                $payload = [
                    'title' => $slideData['title'] ?? '',
                    'content' => $slideData['content'] ?? null,
                    'link' => $slideData['link'] ?? null,
                    'position' => $index,
                ];
                
                $slide = Slide::find($slideData['id']);
                if ($slide) {
                    $slide->update($payload);
                    $slideIds[] = $slide->id;
                    \Log::info("Updated slide {$slideData['id']}");
                }
            }
            
            // Delete slides that were removed from repeater
            if (!empty($slideIds)) {
                $deleted = Slide::where('slider_id', $this->record->slider->id)
                    ->whereNotIn('id', $slideIds)
                    ->delete();
                    
                if ($deleted > 0) {
                    \Log::info("Deleted {$deleted} removed slides");
                }
            }
        }
        
        // ALWAYS refresh the form with latest slides
        \Log::info('Refreshing form with latest slides...');
        $this->record->refresh();
        
        if ($this->record->slider) {
            $slides = $this->record->slider->fresh()->slides()->orderBy('position')->get();
            \Log::info('Refreshing with slides:', ['count' => $slides->count()]);
            
            $this->form->fill([
                ...$this->record->toArray(),
                'bulk_images' => [],
                'slides' => $slides->map(function ($slide) {
                    return [
                        'id' => $slide->id,
                        'slider_id' => $slide->slider_id,
                        'media_asset_id' => $slide->media_asset_id,
                        'image' => $slide->image,
                        'title' => $slide->title,
                        'content' => $slide->content,
                        'link' => $slide->link,
                        'position' => $slide->position,
                    ];
                })->toArray(),
            ]);
            
            \Log::info('Form refreshed successfully');
        }
        
        \Log::info('=== afterSave END ===');
    }
}