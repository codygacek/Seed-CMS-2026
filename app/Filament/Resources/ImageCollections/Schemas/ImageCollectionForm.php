<?php

namespace App\Filament\Resources\ImageCollections\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\MediaAsset;
use App\Models\ImageCollectionItem;

class ImageCollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('image_collection_tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Info')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($operation, $state, $set) {
                                        if ($operation === 'create') {
                                            $set('slug', Str::slug((string) $state));
                                        }
                                    }),

                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(191),

                                RichEditor::make('description')
                                    ->columnSpanFull()
                                    ->nullable(),
                            ]),

                        Tab::make('Images')
                            ->visible(fn (string $operation) => $operation === 'edit')
                            ->schema([
                                Actions::make([
                                    Action::make('select_from_library')
                                        ->label('Select from Media Library')
                                        ->icon('heroicon-o-photo')
                                        ->modalHeading('Select Images from Media Library')
                                        ->modalWidth('5xl')
                                        ->form([
                                            \Filament\Forms\Components\Select::make('selected_media')
                                                ->label('Select Images')
                                                ->multiple()
                                                ->searchable()
                                                ->options(function () {
                                                    return MediaAsset::query()
                                                        ->whereIn('extension', ['jpg', 'jpeg', 'png', 'gif', 'webp'])
                                                        ->orderBy('created_at', 'desc')
                                                        ->limit(100)
                                                        ->get()
                                                        ->mapWithKeys(function ($media) {
                                                            $url = Storage::disk('public')->url($media->file);
                                                            $label = view('filament.forms.components.media-select-option', [
                                                                'url' => $url,
                                                                'title' => $media->title,
                                                                'created' => $media->created_at->format('M d, Y')
                                                            ])->render();
                                                            return [$media->id => $label];
                                                        });
                                                })
                                                ->allowHtml()  // ✅ This allows HTML in the options
                                                ->preload()
                                                ->native(false),
                                        ])
                                        ->action(function (array $data, $livewire) {
                                            if (empty($data['selected_media'])) {
                                                return;
                                            }

                                            $collection = $livewire->getRecord();
                                            $maxPosition = (int) $collection->image_items()->max('position');
                                            
                                            $addedCount = 0;

                                            foreach ($data['selected_media'] as $mediaId) {
                                                $media = MediaAsset::find($mediaId);
                                                
                                                if (!$media) {
                                                    continue;
                                                }

                                                // Skip if already in collection
                                                if ($collection->image_items()->where('media_asset_id', $mediaId)->exists()) {
                                                    continue;
                                                }

                                                $maxPosition++;
                                                ImageCollectionItem::create([
                                                    'image_collection_id' => $collection->id,
                                                    'media_asset_id' => $media->id,
                                                    'image' => $media->file,
                                                    'title' => $media->title,
                                                    'position' => $maxPosition,
                                                    'content' => null,
                                                    'link' => null,
                                                ]);
                                                
                                                $addedCount++;
                                            }

                                            if ($addedCount > 0) {
                                                // Reload the record with relationships
                                                $livewire->record->load('image_items');
                                                
                                                // Force JavaScript to reload the page
                                                $livewire->js('window.location.reload()');
                                                
                                                \Filament\Notifications\Notification::make()
                                                    ->title("{$addedCount} image(s) added successfully")
                                                    ->success()
                                                    ->send();
                                            }
                                        }),
                                ])
                                ->columnSpanFull(),

                                FileUpload::make('bulk_images')
                                    ->label('Or Upload New Images')
                                    ->multiple()
                                    ->image()
                                    ->disk('public')
                                    ->directory('media')
                                    ->visibility('public')
                                    ->storeFiles(false)
                                    ->maxFiles(50)
                                    ->reorderable()
                                    ->imageEditor()
                                    ->imagePreviewHeight('250')
                                    ->panelLayout('grid')
                                    ->helperText('Upload new images here. They will be added to the collection when you click Save.')
                                    ->dehydrated(true),

                                Repeater::make('image_items')
                                    ->relationship('image_items')
                                    ->label('Current Images')
                                    ->orderColumn('position')
                                    ->collapsible()
                                    ->collapsed(false)
                                    ->columnSpanFull()
                                    ->itemLabel(fn (?array $state) => $state['title'] ?? 'Image')
                                    ->schema([
                                        ViewField::make('image_thumbnail')
                                            ->label(false)
                                            ->view('filament.forms.components.image-thumbnail')
                                            ->statePath('image'),
                                        
                                        TextInput::make('title')
                                            ->nullable()
                                            ->maxLength(191),
                                        
                                        TextInput::make('link')
                                            ->nullable()
                                            ->maxLength(255),
                                        
                                        TextArea::make('content')
                                            ->rows(2)
                                            ->nullable()
                                            ->columnSpanFull(),
                                    ])
                                    ->grid(4)
                                    ->addable(false)
                                    ->deletable(true),
                            ]),
                    ]),
            ]);
    }
}