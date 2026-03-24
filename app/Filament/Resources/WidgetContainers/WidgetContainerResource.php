<?php

namespace App\Filament\Resources\WidgetContainers;

use App\Filament\Resources\WidgetContainers\Pages\CreateWidgetContainer;
use App\Filament\Resources\WidgetContainers\Pages\EditWidgetContainer;
use App\Filament\Resources\WidgetContainers\Pages\ListWidgetContainers;
use App\Filament\Resources\WidgetContainers\Schemas\WidgetContainerForm;
use App\Filament\Resources\WidgetContainers\Tables\WidgetContainersTable;
use App\Models\WidgetContainer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WidgetContainerResource extends Resource
{
    protected static ?string $model = WidgetContainer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?string $recordTitleAttribute = 'title';
    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return WidgetContainerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WidgetContainersTable::configure($table);
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
            'index' => ListWidgetContainers::route('/'),
            'create' => CreateWidgetContainer::route('/create'),
            'edit' => EditWidgetContainer::route('/{record}/edit'),
        ];
    }
}
