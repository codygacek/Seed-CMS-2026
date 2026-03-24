<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
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
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use App\Models\MediaAsset;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

use App\Filament\Helpers\FeaturedImageActions;
use App\Filament\Helpers\SeoFields;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('event_tabs')
                    ->tabs([
                        Tab::make('Info')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Set $set, $get) {
                                        if ($operation !== 'create') return;
                                        if (filled($get('slug'))) return;
                                        $set('slug', Str::slug($state));
                                    }),

                                TextInput::make('slug')
                                    ->required()
                                    ->helperText(fn ($state) => $state ? url('events/' . $state) : 'Will be generated from title')
                                    ->unique(ignoreRecord: true),

                                Toggle::make('has_dates')
                                    ->label('Date/time confirmed')
                                    ->helperText('Turn this off if the date is TBD.')
                                    ->default(false)
                                    ->live(),

                                DateTimePicker::make('starts_at')
                                    ->label('Starts')
                                    ->seconds(false)
                                    ->nullable()
                                    ->native(false)
                                    ->required(fn ($get) => (bool) $get('has_dates'))
                                    ->visible(fn ($get) => (bool) $get('has_dates'))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Set $set, $get) {
                                        // If they haven't set an end, default end to start to reduce friction
                                        if (blank($get('ends_at')) && filled($state)) {
                                            $set('ends_at', $state);
                                        }
                                    }),

                                DateTimePicker::make('ends_at')
                                    ->label('Ends')
                                    ->seconds(false)
                                    ->nullable()
                                    ->native(false)
                                    ->visible(fn ($get) => (bool) $get('has_dates'))
                                    ->rule('after_or_equal:starts_at'),

                                TextInput::make('venue_name')
                                    ->label('Venue name')
                                    ->maxLength(191)
                                    ->nullable()
                                    ->visible(fn ($get) => (bool) $get('has_dates')),

                                TextInput::make('venue_address')
                                    ->label('Venue address')
                                    ->maxLength(191)
                                    ->nullable()
                                    ->visible(fn ($get) => (bool) $get('has_dates')),

                                TextInput::make('venue_website')
                                    ->label('Venue website')
                                    ->url()
                                    ->maxLength(191)
                                    ->nullable()
                                    ->visible(fn ($get) => (bool) $get('has_dates')),
                            ])  ,

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
                    ->columnSpanFull()
            ]);
    }
}
