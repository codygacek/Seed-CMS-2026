<?php

namespace App\Filament\Resources\PageResource\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use App\Filament\Actions\InsertShortcodeAction;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;

use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;

use App\Filament\Helpers\SeoFields;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('page_tabs')
                    ->tabs([
                        Tab::make('Info')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(191)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                        if ($operation === 'create') {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),

                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(191)
                                    ->helperText(fn ($state) => $state ? url($state) : 'Will be generated from title')
                                    ->unique(ignoreRecord: true),

                                Select::make('layout')
                                    ->options(get_layouts())
                                    ->searchable()
                                    ->default('default')
                                    ->helperText('Select a layout template for this page'),

                                Select::make('sidebar')
                                    ->relationship('sidebar', 'title')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->helperText('Optional: Select a widget container for the sidebar'),
                            ]),

                        Tab::make('Content')
                            ->schema([
                                Actions::make([
                                    InsertShortcodeAction::make(),
                                ]),
                                RichEditor::make('content')
                                    ->required()
                                    ->columnSpanFull()
                            ]),

                        Tab::make('Slider')
                            ->visible(fn (string $operation) => $operation === 'edit')
                            ->schema([
                                Actions::make([
                                    Action::make('select_from_library')
                                        ->label('Select from Media Library')
                                        ->icon('heroicon-o-photo')
                                        ->modalHeading('Select Images from Media Library')
                                        ->modalWidth('5xl')
                                        ->form([
                                            Select::make('selected_media')
                                                ->label('Select Images')
                                                ->multiple()
                                                ->searchable()
                                                ->options(function () {
                                                    return \App\Models\MediaAsset::query()
                                                        ->whereIn('extension', ['jpg', 'jpeg', 'png', 'gif', 'webp'])
                                                        ->orderBy('created_at', 'desc')
                                                        ->limit(100)
                                                        ->get()
                                                        ->mapWithKeys(function ($media) {
                                                            $url = \Illuminate\Support\Facades\Storage::disk('public')->url($media->file);
                                                            $label = view('filament.forms.components.media-select-option', [
                                                                'url' => $url,
                                                                'title' => $media->title,
                                                                'created' => $media->created_at->format('M d, Y')
                                                            ])->render();
                                                            return [$media->id => $label];
                                                        });
                                                })
                                                ->allowHtml()
                                                ->preload()
                                                ->native(false),
                                        ])
                                        ->action(function (array $data, $livewire) {
                                            if (empty($data['selected_media'])) {
                                                return;
                                            }

                                            $page = $livewire->getRecord();
                                            
                                            // Get or create slider for this page
                                            $slider = $page->slider;
                                            if (!$slider) {
                                                $slider = \App\Models\Slider::create([
                                                    'title' => $page->title . ' Slider',
                                                    'slug' => Str::slug($page->title) . '-slider',
                                                    'sliderable_id' => $page->id,
                                                    'sliderable_type' => 'App\\Models\\Page',
                                                ]);
                                            }
                                            
                                            $maxPosition = (int) $slider->slides()->max('position');
                                            $addedCount = 0;

                                            foreach ($data['selected_media'] as $mediaId) {
                                                $media = \App\Models\MediaAsset::find($mediaId);
                                                
                                                if (!$media) {
                                                    continue;
                                                }

                                                // Skip if already in slider
                                                if ($slider->slides()->where('media_asset_id', $mediaId)->exists()) {
                                                    continue;
                                                }

                                                $maxPosition++;
                                                \App\Models\Slide::create([
                                                    'slider_id' => $slider->id,
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
                                                \Filament\Notifications\Notification::make()
                                                    ->title("{$addedCount} slide(s) added successfully")
                                                    ->success()
                                                    ->send();
                                                
                                                // Reload slides in the form
                                                $slides = $slider->fresh()->slides()->orderBy('position')->get();
                                                $livewire->form->fill([
                                                    ...$livewire->form->getState(),
                                                    'slides' => $slides->toArray(),
                                                ]);
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
                                    ->maxFiles(50)
                                    ->reorderable()
                                    ->imageEditor()
                                    ->imagePreviewHeight('250')
                                    ->panelLayout('grid')
                                    ->helperText('Upload new images here. They will be added to the slider when you click Save.')
                                    ->dehydrated(true)
                                    ->saveUploadedFileUsing(function ($file) {
                                        $original = $file->getClientOriginalName();
                                        $base = Str::slug(pathinfo($original, PATHINFO_FILENAME));
                                        $ext = strtolower($file->getClientOriginalExtension());
                                        $filename = ($base ?: 'image') . '-' . Str::random(8) . '.' . $ext;
                                        
                                        // Store file
                                        $path = $file->storePubliclyAs('media', $filename, 'public');
                                        
                                        // Create MediaAsset immediately
                                        \App\Models\MediaAsset::create([
                                            'title' => Str::of($base ?: 'image')->replace(['-', '_'], ' ')->title(),
                                            'file' => $filename,
                                            'extension' => $ext,
                                            'alt_text' => null,
                                            'content' => null,
                                        ]);
                                        
                                        return $path;
                                    }),

                                Repeater::make('slides')
                                    ->label('Current Slides')
                                    ->reorderable()
                                    ->collapsible()
                                    ->collapsed(false)
                                    ->columnSpanFull()
                                    ->itemLabel(fn (?array $state) => $state['title'] ?? 'Slide')
                                    ->schema([
                                        ViewField::make('image')
                                            ->view('filament.forms.components.image-thumbnail')
                                            ->columnSpanFull()
                                            ->label(''),
                                        
                                        TextInput::make('title')
                                            ->nullable()
                                            ->maxLength(191),
                                        
                                        TextInput::make('link')
                                            ->url()
                                            ->nullable()
                                            ->maxLength(255),
                                        
                                        \Filament\Forms\Components\Textarea::make('content')
                                            ->rows(3)
                                            ->nullable()
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->addable(false)
                                    ->deletable(true),
                            ]),

                        Tab::make('Security')
                            ->schema([
                                TextInput::make('password')
                                    ->password()
                                    ->revealable()
                                    ->maxLength(191)
                                    ->label('Password')
                                    ->helperText('Enter a new password to protect this page, or to change the existing password.')
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->dehydrateStateUsing(fn ($state) => $state)
                                    ->afterStateHydrated(function (TextInput $component, $record) {
                                        $component->state(null);
                                    }),

                                Placeholder::make('password_status')
                                    ->label('Status')
                                    ->content(function ($record) {
                                        if (! $record) {
                                            return 'Save this page first to manage password protection.';
                                        }

                                        return $record->token
                                            ? new HtmlString('<span class="text-success-600 font-medium">✓ Password protection is enabled</span>')
                                            : new HtmlString('<span class="text-gray-500">No password protection</span>');
                                    }),

                                Actions::make([
                                    Action::make('remove_password')
                                        ->label('Remove password')
                                        ->icon('heroicon-m-trash')
                                        ->color('danger')
                                        ->requiresConfirmation()
                                        ->modalHeading('Remove password protection?')
                                        ->modalDescription(fn ($record) => $record?->title
                                            ? "This will remove password protection for \"{$record->title}\"."
                                            : 'This will remove password protection for this page.'
                                        )
                                        ->modalSubmitActionLabel('Remove')
                                        ->visible(fn ($record) => filled($record) && (bool) $record->token)
                                        ->action(fn ($livewire) => $livewire->removePagePassword()),
                                ])->alignEnd(),
                            ]),

                        Tab::make('SEO')
                            ->schema([
                                SeoFields::make(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected static function getLayoutOptions(): array
    {
        $layoutsPath = resource_path('views/layouts');

        if (! is_dir($layoutsPath)) {
            return ['default' => 'Default'];
        }

        $layouts = [];
        $files = glob($layoutsPath . '/*.blade.php') ?: [];

        foreach ($files as $file) {
            $name = basename($file, '.blade.php');
            $layouts[$name] = ucwords(str_replace(['-', '_'], ' ', $name));
        }

        return $layouts ?: ['default' => 'Default'];
    }
}