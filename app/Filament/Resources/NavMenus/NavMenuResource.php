<?php

namespace App\Filament\Resources\NavMenus;

use App\Filament\Resources\NavMenus\Pages\CreateNavMenu;
use App\Filament\Resources\NavMenus\Pages\EditNavMenu;
use App\Filament\Resources\NavMenus\Pages\ListNavMenus;
use App\Filament\Resources\NavMenus\Schemas\NavMenuForm;
use App\Filament\Resources\NavMenus\Tables\NavMenusTable;
use App\Models\NavMenu;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NavMenuResource extends Resource
{
    protected static ?string $model = NavMenu::class;
    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3CenterLeft;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return NavMenuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NavMenusTable::configure($table);
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
            'index' => ListNavMenus::route('/'),
            'create' => CreateNavMenu::route('/create'),
            'edit' => EditNavMenu::route('/{record}/edit'),
        ];
    }
}
