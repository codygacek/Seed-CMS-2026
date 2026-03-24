<?php

namespace App\Filament\Resources\MediaAssets\Widgets;

use App\Models\MediaAsset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaUploader extends Widget implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.resources.media-assets.widgets.media-uploader';

    protected int|string|array $columnSpan = 'full';

    public array $data = [
        'files' => [],
    ];

    /**
     * Track Livewire tmp files already processed to prevent duplicates.
     * Keyed by TemporaryUploadedFile::getFilename()
     */
    public array $processedTmpKeys = [];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                FileUpload::make('files')
                    ->label('Drop images to upload')
                    ->multiple()
                    ->image()
                    ->panelLayout('grid')
                    ->storeFiles(false) // we store manually so no "submit" needed
                    ->maxParallelUploads(8)
                    ->reorderable(false)
                    ->downloadable(false)
                    ->openable(false)
                    ->acceptedFileTypes(['image/*'])
                    ->live()
                    ->helperText('Uploads start automatically.')
                    ->afterStateUpdated(function ($state): void {
                        if (! is_array($state) || $state === []) {
                            return;
                        }

                        $savedAny = false;

                        foreach ($state as $file) {
                            if (! $file instanceof TemporaryUploadedFile) {
                                continue;
                            }

                            // ✅ Stable key for this temp upload
                            $tmpKey = $file->getFilename();

                            // Skip if we've already stored/created a record for this exact upload
                            if (isset($this->processedTmpKeys[$tmpKey])) {
                                continue;
                            }

                            $original = $file->getClientOriginalName();
                            $base = Str::slug(pathinfo($original, PATHINFO_FILENAME));
                            $ext  = strtolower($file->getClientOriginalExtension() ?: pathinfo($original, PATHINFO_EXTENSION) ?: '');

                            $filename = ($base ?: 'image')
                                . '-' . Str::random(8)
                                . ($ext ? ".{$ext}" : '');

                            $storedPath = $file->storePubliclyAs('media', $filename, 'public');

                            MediaAsset::create([
                                'title'     => Str::of($base ?: 'image')->replace(['-', '_'], ' ')->title(),
                                'file'      => basename($storedPath),
                                'extension' => $ext ?: '',
                                'alt_text'  => null,
                                'content'   => null,
                            ]);

                            $this->processedTmpKeys[$tmpKey] = true;
                            $savedAny = true;
                        }

                        if ($savedAny) {
                            // Refresh the grid (your List page listener will resetTable)
                            $this->dispatch('media-assets-uploaded');

                            // Ask the browser to clear after uploads settle (debounced)
                            $this->dispatch('schedule-media-clear');
                        }
                    }),
            ]);
    }

    /**
     * Called from Alpine after a quiet period.
     * Clears uploader UI and resets processed keys so the next batch works cleanly.
     */
    public function clearUploader(): void
    {
        $this->data['files'] = [];
        $this->processedTmpKeys = [];
    }
}