<?php

namespace App\Filament\Resources\NavMenus\Schemas;

use App\Models\Article;
use App\Models\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Component as Livewire;

class NavMenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // Force vertical stacking: Meta row, then Builder row
            Group::make()
                ->columnSpanFull()
                ->schema([
                    /* ===============================
                     * Row 1: Menu meta (title + slug)
                     * =============================== */
                    Grid::make()
                        ->columnSpanFull()
                        ->columns([
                            'default' => 1,
                            'md' => 2,
                        ])
                        ->schema([
                            TextInput::make('title')
                                ->required()
                                ->maxLength(191)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                    if ($operation === 'create') {
                                        $set('slug', Str::slug((string) $state));
                                    }
                                }),

                            TextInput::make('slug')
                                ->required()
                                ->maxLength(191)
                                ->helperText('Unique identifier for this menu'),
                        ]),

                    /* ===============================
                     * Row 2: Builder (edit only)
                     * =============================== */
                    Grid::make()
                        ->columns(12)
                        ->schema([
                            /* ---------- LEFT: Add items ---------- */
                            Section::make('Add Menu Items')
                                ->schema([
                                    // Pages
                                    Section::make('Pages')
                                        ->schema([
                                            Select::make('add_page_id')
                                                ->label('Page')
                                                ->options(fn () => Page::query()->orderBy('title')->pluck('title', 'id'))
                                                ->searchable()
                                                ->preload()
                                                ->dehydrated(false),
                                        ])
                                        ->footer([
                                            Action::make('addPage')
                                                ->label('Pages')
                                                ->button()
                                                ->action(function (? \App\Models\NavMenu $record, Get $get, Set $set) {
                                                    if (! $record) {
                                                        return;
                                                    }

                                                    $pageId = $get('add_page_id');
                                                    if (! $pageId) {
                                                        return;
                                                    }

                                                    $page = Page::query()->find($pageId);
                                                    if (! $page) {
                                                        return;
                                                    }

                                                    $position = ((int) ($record->items()->where('parent_id', 0)->max('position') ?? 0)) + 1;

                                                    $record->items()->create([
                                                        'label' => $page->title,
                                                        'link' => '/' . ltrim((string) $page->slug, '/'),
                                                        'parent_id' => 0,
                                                        'position' => $position,
                                                        'new_window' => false,
                                                    ]);

                                                    $set('add_page_id', null);

                                                    // Refresh the repeater state so the UI updates immediately.
                                                    $record->refresh();

                                                    $set('top_level_items', $record->top_level_items()
                                                        ->with('children')
                                                        ->orderBy('position')
                                                        ->get()
                                                        ->toArray());
                                                })
                                        ])
                                        ->collapsed(),

                                    // Articles (“News Links”)
                                    Section::make('Articles')
                                        ->schema([
                                            Select::make('add_article_id')
                                                ->label('Article')
                                                ->options(fn () => Article::query()->orderBy('title')->pluck('title', 'id'))
                                                ->searchable()
                                                ->preload()
                                                ->dehydrated(false),
                                        ])
                                        ->footer([
                                            Action::make('addArticle')
                                                ->label('Articles')
                                                ->button()
                                                ->action(function (? \App\Models\NavMenu $record, Get $get, Set $set) {
                                                    if (! $record) {
                                                        return;
                                                    }

                                                    $articleId = $get('add_article_id');
                                                    if (! $articleId) {
                                                        return;
                                                    }

                                                    $article = Article::query()->find($articleId);
                                                    if (! $article) {
                                                        return;
                                                    }

                                                    $position = ((int) ($record->items()
                                                        ->where('parent_id', 0)
                                                        ->max('position') ?? 0)) + 1;

                                                    $record->items()->create([
                                                        'label' => $article->title,
                                                        'link' => '/news/' . ltrim((string) $article->slug, '/'), // adjust if needed
                                                        'parent_id' => 0,
                                                        'position' => $position,
                                                        'new_window' => false,
                                                    ]);

                                                    $set('add_article_id', null);

                                                    // Refresh the repeater state so the UI updates immediately.
                                                    $record->refresh();

                                                    $set('top_level_items', $record->top_level_items()
                                                        ->with('children')
                                                        ->orderBy('position')
                                                        ->get()
                                                        ->toArray());

                                                }),
                                        ])
                                        ->collapsed(),

                                    // Custom link
                                    Section::make('Custom Link')
                                        ->schema([
                                            TextInput::make('custom_label')
                                                ->label('Label')
                                                ->maxLength(255)
                                                ->dehydrated(false),

                                            TextInput::make('custom_link')
                                                ->label('Link (URL or path)')
                                                ->maxLength(255)
                                                ->dehydrated(false),

                                            Toggle::make('custom_new_window')
                                                ->label('Open in new window')
                                                ->default(false)
                                                ->dehydrated(false),
                                        ])
                                        ->footer([
                                            Action::make('addCustom')
                                                ->label('Custom Link')
                                                ->button()
                                                ->action(function (? \App\Models\NavMenu $record, Get $get, Set $set) {
                                                    if (! $record) {
                                                        return;
                                                    }

                                                    $label = trim((string) $get('custom_label'));
                                                    $link  = trim((string) $schemaGet('custom_link'));

                                                    if ($label === '' || $link === '') {
                                                        return;
                                                    }

                                                    $position = ((int) ($record->items()
                                                        ->where('parent_id', 0)
                                                        ->max('position') ?? 0)) + 1;

                                                    $record->items()->create([
                                                        'label' => $label,
                                                        'link' => $link,
                                                        'parent_id' => 0,
                                                        'position' => $position,
                                                        'new_window' => (bool) $get('custom_new_window'),
                                                    ]);

                                                    $set('custom_label', '');
                                                    $set('custom_link', '');
                                                    $set('custom_new_window', false);

                                                    // Refresh the repeater state so the UI updates immediately.
                                                    $record->refresh();

                                                    $set('top_level_items', $record->top_level_items()
                                                        ->with('children')
                                                        ->orderBy('position')
                                                        ->get()
                                                        ->toArray());

                                                }),
                                        ])
                                        ->collapsed(),
                                ])
                                ->columnSpan(12)
                                ->columnSpan([
                                    'lg' => 3,
                                ]),

                            /* ---------- RIGHT: Menu structure ---------- */
                            Section::make('Menu Structure')
                                ->schema([
                                    Repeater::make('top_level_items')
                                        ->relationship('top_level_items')
                                        ->orderColumn('position')
                                        ->reorderable()
                                        ->collapsible()
                                        ->collapsed()
                                        ->itemLabel(function (array $state): string {
                                            $label = $state['label'] ?? '(no label)';
                                            $id = $state['id'] ?? null;

                                            if (! $id) {
                                                return $label;
                                            }

                                            $childCount = \App\Models\NavMenuItem::query()
                                                ->where('parent_id', $id)
                                                ->count();

                                            if ($childCount > 0) {
                                                return "{$label}  ({$childCount} sub-item" . ($childCount === 1 ? ')' : 's)');
                                            }

                                            return $label;
                                        })
                                        ->deleteAction(function (Action $action): Action {
                                            return $action
                                                ->requiresConfirmation()
                                                ->modalHeading(function (Repeater $component, array $arguments): string {
                                                    $state = $component->getRawItemState($arguments['item']);

                                                    return 'Delete menu item: ' . ($state['label'] ?? '(no label)') . '?';
                                                })
                                                ->modalSubmitActionLabel('Delete');
                                        })
                                        ->schema([
                                            Hidden::make('parent_id')->default(0),

                                            Grid::make()
                                                ->columns([
                                                    'default' => 1,
                                                    'md' => 2,
                                                ])
                                                ->schema([
                                                    TextInput::make('label')
                                                        ->required()
                                                        ->maxLength(255),

                                                    TextInput::make('link')
                                                        ->required()
                                                        ->maxLength(255),
                                                ]),

                                            Toggle::make('new_window')
                                                ->label('Open in new window')
                                                ->default(false),

                                            // Level 2 only (children). No further nesting exists in the UI.
                                            Repeater::make('children')
                                                ->relationship('children')
                                                ->orderColumn('position')
                                                ->reorderable()
                                                ->collapsible()
                                                ->collapsed()
                                                ->itemLabel(fn (array $state) => $state['label'] ?? 'Sub-item')
                                                ->deleteAction(function (Action $action): Action {
                                                    return $action
                                                        ->requiresConfirmation()
                                                        ->modalHeading(function (Repeater $component, array $arguments): string {
                                                            $state = $component->getRawItemState($arguments['item']);

                                                            return 'Delete menu item: ' . ($state['label'] ?? '(no label)') . '?';
                                                        })
                                                        ->modalSubmitActionLabel('Delete');
                                                })
                                                ->addActionLabel('Add Sub-Item')
                                                ->schema([
                                                    Grid::make()
                                                        ->columns([
                                                            'default' => 1,
                                                            'md' => 2,
                                                        ])
                                                        ->schema([
                                                            TextInput::make('label')
                                                                ->required()
                                                                ->maxLength(255),

                                                            TextInput::make('link')
                                                                ->required()
                                                                ->maxLength(255),
                                                        ]),

                                                    Toggle::make('new_window')
                                                        ->label('Open in new window')
                                                        ->default(false),
                                                ]),
                                        ]),
                                ])
                                ->columnSpan(12)
                                ->columnSpan([
                                    'lg' => 9,
                                ]),
                        ])
                        ->visible(fn (string $operation) => $operation === 'edit'),
                ]),
        ]);
    }
}