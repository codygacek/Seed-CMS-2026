<?php

namespace App\Filament\Resources\MediaAssets;

use App\Filament\Resources\MediaAssets\Pages\CreateMediaAsset;
use App\Filament\Resources\MediaAssets\Pages\EditMediaAsset;
use App\Filament\Resources\MediaAssets\Pages\ListMediaAssets;
use App\Filament\Resources\MediaAssets\Schemas\MediaAssetForm;
use App\Models\Article;
use App\Models\Event;
use App\Models\MediaAsset;
use App\Models\Slide;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaAssetResource extends Resource
{
    protected static ?string $model = MediaAsset::class;
    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolderOpen;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return MediaAssetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    ImageColumn::make('file')
                        ->disk('public')
                        ->square()
                        ->height(250)
                        ->extraImgAttributes(['class' => 'w-full']),

                    TextColumn::make('title')
                        ->searchable()
                        ->limit(40),
                ])->space(2),
            ])
            ->contentGrid([
                'sm' => 2,
                'lg' => 3,
            ])
            ->actions([
                EditAction::make(),

                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete media asset?')
                    ->modalDescription('Deleting media removes the file from storage. This cannot be undone.')
                    ->before(function (MediaAsset $record, DeleteAction $action): void {
                        $counts = [
                            'Slides'   => Slide::query()->where('media_asset_id', $record->id)->count(),
                            'Articles' => Article::query()->where('media_asset_id', $record->id)->count(),
                            'Events'   => Event::query()->where('media_asset_id', $record->id)->count(),
                        ];

                        $used = array_filter($counts);

                        if ($used === []) {
                            return;
                        }

                        $lines = collect($used)
                            ->map(fn ($count, $label) => "{$label}: {$count}")
                            ->implode("\n");

                        Notification::make()
                            ->title('Media asset is in use')
                            ->body(
                                "This file is currently referenced by:\n\n{$lines}\n\n" .
                                "Remove those references first. Deleting media cannot be undone."
                            )
                            ->danger()
                            ->send();

                        $action->cancel();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMediaAssets::route('/'),
            'create' => CreateMediaAsset::route('/create'),
            'edit' => EditMediaAsset::route('/{record}/edit'),
        ];
    }
}