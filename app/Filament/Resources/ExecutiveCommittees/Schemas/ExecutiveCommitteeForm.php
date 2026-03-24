<?php

namespace App\Filament\Resources\ExecutiveCommittees\Schemas;

use App\Models\MediaAsset;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ExecutiveCommitteeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('exec_tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Info')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(191)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($operation, $state, $set) {
                                        if ($operation === 'create') {
                                            $set('slug', Str::slug((string) $state));
                                        }
                                    }),

                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(191),

                                Select::make('status')
                                    ->required()
                                    ->options([
                                        'current' => 'Current',
                                        'previous' => 'Previous',
                                    ])
                                    ->default('current'),
                            ]),

                        Tab::make('Profile Image')
                            ->schema([
                                Select::make('image')
                                    ->label('Profile Image')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->helperText('Select an existing image from the media library.')
                                    ->getSearchResultsUsing(function (string $search): array {
                                        return MediaAsset::query()
                                            ->where('title', 'like', "%{$search}%")
                                            ->orWhere('file', 'like', "%{$search}%")
                                            ->orderBy('title')
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(fn ($asset) => [
                                                $asset->file => ($asset->title ?: basename($asset->file)),
                                            ])
                                            ->toArray();
                                    })
                                    ->getOptionLabelUsing(fn ($value): ?string =>
                                        $value
                                            ? (MediaAsset::where('file', $value)->value('title') ?: basename($value))
                                            : null
                                    )
                                    // Your DB column is NOT NULL right now—keep it safe:
                                    ->dehydrateStateUsing(fn ($state) => $state ?? ''),

                                Placeholder::make('image_preview')
                                    ->label('Preview')
                                    ->content(function ($get) {
                                        $path = $get('image');

                                        if (! filled($path)) {
                                            return 'No image selected.';
                                        }

                                        $url = Storage::disk('public')->url($path);

                                        return new HtmlString(
                                            '<img src="' . e($url) . '" style="width: 240px; aspect-ratio: 1 / 1; object-fit: cover; border-radius: 12px;" />'
                                        );
                                    }),
                            ]),

                        Tab::make('Details')
                            ->schema([
                                TextInput::make('position')
                                    ->label('Position (Role)')
                                    ->maxLength(191)
                                    ->nullable()
                                    ->dehydrateStateUsing(fn ($state) => $state ?? ''),

                                TextInput::make('other_position')
                                    ->label('Other Position')
                                    ->maxLength(191)
                                    ->nullable()
                                    ->dehydrateStateUsing(fn ($state) => $state ?? ''),

                                TextInput::make('date')
                                    ->label('Year (e.g. 1980)')
                                    ->placeholder('1980')
                                    ->maxLength(4)
                                    ->minLength(4)
                                    ->numeric()
                                    ->nullable()
                                    ->dehydrateStateUsing(fn ($state) => $state ?? ''),

                                TextInput::make('major')
                                    ->label('Major')
                                    ->maxLength(191)
                                    ->nullable()
                                    ->dehydrateStateUsing(fn ($state) => $state ?? ''),
                            ]),
                    ]),
            ]);
    }
}