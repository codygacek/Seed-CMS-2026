<?php

namespace App\Filament\Resources\Articles\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Support\Facades\Storage;
use App\Models\MediaAsset;
use Filament\Notifications\Notification;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Support\Media\EditorAttachments;

use App\Filament\Helpers\FeaturedImageActions;
use App\Filament\Helpers\SeoFields;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('article_tabs')
                ->tabs([
                    Tab::make('Info')
                        ->schema([
                            TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Set $set, $get) {
                                if ($operation !== 'create') {
                                    return;
                                }

                                if (filled($get('slug'))) {
                                    return;
                                }

                                $set('slug', Str::slug($state));
                            }),

                            TextInput::make('slug')
                                ->required()
                                ->maxLength(191)
                                ->helperText(fn ($state) => $state ? url('news/' . $state) : 'Will be generated from title')
                                ->unique(ignoreRecord: true),

                            Toggle::make('is_published')
                                ->required(),
                        
                            DateTimePicker::make('published_at')
                                ->label('Publish date')
                                ->helperText('Controls article ordering and scheduling')
                                ->nullable()
                                ->native(false)
                                ->format('M j, Y g:m')
                                ->default(now())
                                ->seconds(false)
                        ]),

                    Tab::make('Featured Image')
                        ->schema(FeaturedImageActions::getTabSchema()),

                    Tab::make('Content')
                        ->schema([
                            RichEditor::make('content')
                                ->required()
                                ->columnSpanFull(),
                        ]),

                    Tab::make('SEO')
                        ->schema([
                            SeoFields::make(),
                        ]),
                ])
                ->columnSpanFull(),
            ]);
    }
}