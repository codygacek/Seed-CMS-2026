<?php

namespace App\Filament\Resources\WidgetContainers\Schemas;

use App\Models\Widget;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class WidgetContainerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('widget_container_tabs')
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
                                    ->maxLength(191)
                                    ->helperText('Unique identifier for this widget container'),
                            ]),

                        Tab::make('Options')
                            ->schema([
                                TextInput::make('options.container_class')
                                    ->label('Container CSS Class')
                                    ->nullable(),

                                TextInput::make('options.title_class')
                                    ->label('Title CSS Class')
                                    ->nullable(),

                                Toggle::make('options.show_title')
                                    ->label('Show Title')
                                    ->default(false),
                            ]),

                        Tab::make('Widgets')
                            ->visible(fn (string $operation) => $operation === 'edit')
                            ->schema([
                                Actions::make([
                                    Action::make('add_events_widget')
                                        ->label('Add Events Feed')
                                        ->icon('heroicon-o-calendar')
                                        ->color('success')
                                        ->action(function ($livewire) {
                                            $container = $livewire->getRecord();
                                            $maxPosition = (int) $container->widgets()->max('position');

                                            Widget::create([
                                                'widget_container_id' => $container->id,
                                                'title' => 'Upcoming Events',
                                                'type' => 'events-feed',
                                                'options' => [
                                                    'quantity' => 5,
                                                    'view_more_text' => 'View All Events',
                                                ],
                                                'position' => $maxPosition + 1,
                                            ]);

                                            $livewire->record->refresh();

                                            $livewire->form->fill($livewire->record->attributesToArray());

                                            $livewire->dispatch('$refresh');
                                        }),

                                    Action::make('add_news_widget')
                                        ->label('Add News Feed')
                                        ->icon('heroicon-o-newspaper')
                                        ->color('info')
                                        ->action(function ($livewire) {
                                            $container = $livewire->getRecord();
                                            $maxPosition = (int) $container->widgets()->max('position');

                                            Widget::create([
                                                'widget_container_id' => $container->id,
                                                'title' => 'Recent News',
                                                'type' => 'news-feed',
                                                'options' => [
                                                    'quantity' => 5,
                                                    'view_more_text' => 'View All News',
                                                ],
                                                'position' => $maxPosition + 1,
                                            ]);

                                            $livewire->record->refresh();

                                            $livewire->form->fill($livewire->record->attributesToArray());

                                            $livewire->dispatch('$refresh');
                                        }),

                                    Action::make('add_text_widget')
                                        ->label('Add Text Widget')
                                        ->icon('heroicon-o-document-text')
                                        ->color('warning')
                                        ->action(function ($livewire) {
                                            $container = $livewire->getRecord();
                                            $maxPosition = (int) $container->widgets()->max('position');

                                            Widget::create([
                                                'widget_container_id' => $container->id,
                                                'title' => 'Text Widget',
                                                'type' => 'text',
                                                'options' => [
                                                    'content' => '',
                                                ],
                                                'position' => $maxPosition + 1,
                                            ]);

                                            $livewire->record->refresh();

                                            $livewire->form->fill($livewire->record->attributesToArray());

                                            $livewire->dispatch('$refresh');
                                        }),

                                    Action::make('add_social_media_widget')
                                        ->label('Add Social Media Widget')
                                        ->icon('heroicon-o-signal')
                                        ->color('gray')
                                        ->action(function ($livewire) {
                                            $container = $livewire->getRecord();
                                            $maxPosition = (int) $container->widgets()->max('position');

                                            Widget::create([
                                                'widget_container_id' => $container->id,
                                                'title' => 'Social Media Widget',
                                                'type' => 'social-media',
                                                'options' => [
                                                    'content' => '',
                                                ],
                                                'position' => $maxPosition + 1,
                                            ]);

                                            $livewire->record->refresh();

                                            $livewire->form->fill($livewire->record->attributesToArray());

                                            $livewire->dispatch('$refresh');
                                        }),
                                ])->columnSpanFull(),

                                Repeater::make('widgets')
                                    ->relationship('widgets')
                                    ->orderColumn('position')
                                    ->collapsible()
                                    ->collapsed(true)
                                    ->columnSpanFull()
                                    ->itemLabel(fn (?array $state) => ($state['title'] ?? 'Widget') . ' (' . ($state['type'] ?? '') . ')')
                                    ->schema([
                                        Hidden::make('type'),

                                        TextInput::make('title')
                                            ->required()
                                            ->maxLength(191)
                                            ->columnSpanFull(),

                                        // ONE quantity field (used by both feeds)
                                        TextInput::make('options.quantity')
                                            ->label('Number of Items')
                                            ->numeric()
                                            ->default(5)
                                            ->visible(fn ($get) => in_array($get('type'), ['events-feed', 'news-feed'], true)),

                                        // ONE view_more_text field (used by both feeds)
                                        TextInput::make('options.view_more_text')
                                            ->label('View More Button Text')
                                            ->visible(fn ($get) => in_array($get('type'), ['events-feed', 'news-feed'], true))
                                            ->nullable(),

                                        // Text widget
                                        RichEditor::make('options.content')
                                            ->label('Content')
                                            ->columnSpanFull()
                                            ->visible(fn ($get) => $get('type') === 'text')
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
                                                'horizontalRule',
                                                'undo',
                                                'redo',
                                            ])
                                            ->extraAttributes(['style' => 'min-height: 15vh;']),
                                    ])
                                    ->columns(2)
                                    ->addable(false),
                            ]),
                    ]),
            ]);
    }
}