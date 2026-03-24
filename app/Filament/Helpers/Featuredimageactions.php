<?php

namespace App\Filament\Helpers;

use App\Models\MediaAsset;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FeaturedImageActions
{
    /**
     * Get the complete Featured Image tab schema
     * 
     * @return array
     */
    public static function getTabSchema(): array
    {
        return [
            ViewField::make('current_featured_image')
                ->view('filament.forms.components.featured-image-preview')
                ->visible(fn ($get) => filled($get('media_asset_id')) || filled($get('image')))
                ->columnSpanFull(),

            Actions::make([
                self::selectFromLibraryAction(),
                self::uploadNewAction(),
                self::removeImageAction(),
            ])->columnSpanFull(),
        ];
    }
    
    /**
     * Select from Media Library action
     */
    protected static function selectFromLibraryAction(): Action
    {
        return Action::make('select_from_library')
            ->label('Select from Media Library')
            ->icon('heroicon-o-photo')
            ->modalHeading('Select Featured Image')
            ->modalWidth('xl')
            ->form([
                Select::make('selected_media')
                    ->label('Select Image')
                    ->searchable()
                    ->options(function () {
                        return MediaAsset::query()
                            ->whereIn('extension', ['jpg', 'jpeg', 'png', 'gif', 'webp'])
                            ->orderByDesc('created_at')
                            ->limit(200)
                            ->get()
                            ->mapWithKeys(function ($media) {
                                $url = Storage::disk('public')->url($media->file);
                                $label = view('filament.forms.components.media-select-option', [
                                    'url' => $url,
                                    'title' => $media->title,
                                    'created' => optional($media->created_at)->format('M d, Y'),
                                ])->render();

                                return [$media->id => $label];
                            });
                    })
                    ->allowHtml()
                    ->preload()
                    ->native(false)
                    ->required(),
            ])
            ->action(function (array $data, $livewire): void {
                $mediaId = $data['selected_media'] ?? null;
                if (!$mediaId) return;

                $media = MediaAsset::find($mediaId);
                if (!$media) return;

                $record = $livewire->getRecord();
                $legacyImage = $media->getRawOriginal('file') ?: basename($media->file);

                $record->update([
                    'media_asset_id' => $media->id,
                    'image' => $legacyImage,
                ]);

                $livewire->form->fill([
                    ...$livewire->form->getState(),
                    'media_asset_id' => $media->id,
                    'image' => $legacyImage,
                ]);

                Notification::make()
                    ->title('Featured image selected')
                    ->success()
                    ->send();
            });
    }
    
    /**
     * Upload New Image action with custom naming
     */
    protected static function uploadNewAction(): Action
    {
        return Action::make('upload_featured_image')
            ->label('Upload New Image')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('info')
            ->modalHeading('Upload Featured Image')
            ->modalWidth('xl')
            ->form([
                FileUpload::make('file')
                    ->label('Drop an image')
                    ->image()
                    ->disk('public')
                    ->directory('media')
                    ->visibility('public')
                    ->maxFiles(1)
                    ->imageEditor()
                    ->required()
                    // THIS IS THE KEY FIX - Custom filename generation
                    ->saveUploadedFileUsing(function ($file) {
                        // Generate custom filename: slug-random8.ext
                        $original = $file->getClientOriginalName();
                        $base = Str::slug(pathinfo($original, PATHINFO_FILENAME));
                        $ext = strtolower($file->getClientOriginalExtension());
                        $filename = ($base ?: 'image') . '-' . Str::random(8) . '.' . $ext;
                        
                        // Store file with custom name
                        $path = $file->storePubliclyAs('media', $filename, 'public');
                        
                        // Return path for the form field
                        return $path;
                    }),
            ])
            ->action(function (array $data, $livewire): void {
                $path = $data['file'] ?? null;
                if (!$path) {
                    return;
                }

                $filename = basename($path);
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION) ?: '');
                $base = Str::slug(pathinfo($filename, PATHINFO_FILENAME));

                // Create MediaAsset
                $asset = MediaAsset::create([
                    'title'     => Str::of($base ?: 'image')->replace(['-', '_'], ' ')->title(),
                    'file'      => $filename,
                    'extension' => $ext,
                    'alt_text'  => null,
                    'content'   => null,
                ]);

                $basename = $asset->getRawOriginal('file') ?: basename($asset->file);

                $record = $livewire->getRecord();
                $record->update([
                    'media_asset_id' => $asset->id,
                    'image'          => $basename,
                ]);

                $livewire->form->fill([
                    ...$livewire->form->getState(),
                    'media_asset_id' => $asset->id,
                    'image'          => $basename,
                ]);

                Notification::make()
                    ->title('Featured image uploaded')
                    ->success()
                    ->send();
            });
    }
    
    /**
     * Remove Featured Image action
     */
    protected static function removeImageAction(): Action
    {
        return Action::make('remove_image')
            ->label('Remove Featured Image')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Remove featured image?')
            ->modalDescription(fn ($get) => filled($get('media_asset_id')) || filled($get('image'))
                ? 'This will remove the featured image from this record.'
                : null)
            ->visible(fn ($get) => filled($get('media_asset_id')) || filled($get('image')))
            ->action(function ($livewire): void {
                $record = $livewire->getRecord();

                $record->update([
                    'media_asset_id' => null,
                    'image' => '',
                ]);

                $livewire->form->fill([
                    ...$livewire->form->getState(),
                    'media_asset_id' => null,
                    'image' => null,
                ]);

                Notification::make()
                    ->title('Featured image removed')
                    ->success()
                    ->send();
            });
    }
}