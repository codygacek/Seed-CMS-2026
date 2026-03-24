<?php

namespace App\Filament\Resources\Members\Schemas;

use App\Models\MediaAsset;
use Filament\Forms\Components\FileUpload;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('member_tabs')
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
                                    ->maxLength(191)
                                    ->helperText(fn ($state) => $state
                                        ? url('members/' . $state)
                                        : 'Will be generated from name'
                                    ),

                                Select::make('status')
                                    ->required()
                                    ->options([
                                        'prospective' => 'Prospective',
                                        'current' => 'Current',
                                    ])
                                    ->default('prospective'),
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
                                            ->mapWithKeys(fn ($asset) => [$asset->file => ($asset->title ?: basename($asset->file))])
                                            ->toArray();
                                    })
                                    ->getOptionLabelUsing(fn ($value): ?string =>
                                        $value
                                            ? (MediaAsset::where('file', $value)->value('title') ?: basename($value))
                                            : null
                                    )
                                    // If your DB column is NOT NULL, keep this so saving doesn't error:
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
                                TextInput::make('date')
                                    ->label('Year (e.g. 1980)')
                                    ->placeholder('1980')
                                    ->maxLength(4)
                                    ->minLength(4)
                                    ->numeric()
                                    ->nullable()
                                    // DB column is NOT NULL in your migration — keep it safe:
                                    ->dehydrateStateUsing(fn ($state) => $state ?? ''),

                                TextInput::make('major')
                                    ->label('Major')
                                    ->maxLength(191)
                                    ->nullable()
                                    ->dehydrateStateUsing(fn ($state) => $state ?? ''),

                                TextInput::make('current_position')
                                    ->label('Position / Role')
                                    ->maxLength(191)
                                    ->nullable()
                                    ->dehydrateStateUsing(fn ($state) => $state ?? ''),

                                RichEditor::make('alt_info')
                                    ->label('Alt Info')
                                    ->helperText('Optional rich text alternative if year/major/position aren’t filled out.')
                                    ->columnSpanFull()
                                    ->nullable()
                                    ->dehydrateStateUsing(fn ($state) => $state ?? '')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'strike',
                                        'link',
                                        'bulletList',
                                        'orderedList',
                                        'blockquote',
                                        'h2',
                                        'h3',
                                        'undo',
                                        'redo',
                                    ]),
                            ]),
                    ]),
            ]);
    }
}