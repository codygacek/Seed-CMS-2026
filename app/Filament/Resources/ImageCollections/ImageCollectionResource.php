<?php

namespace App\Filament\Resources\ImageCollections;

use App\Filament\Resources\ImageCollections\Pages\CreateImageCollection;
use App\Filament\Resources\ImageCollections\Pages\EditImageCollection;
use App\Filament\Resources\ImageCollections\Pages\ListImageCollections;
use App\Filament\Resources\ImageCollections\Schemas\ImageCollectionForm;
use App\Filament\Resources\ImageCollections\Tables\ImageCollectionsTable;
use App\Models\ImageCollection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ImageCollectionResource extends Resource
{
    protected static ?string $model = ImageCollection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $modelLabel = 'Photo Album';
    protected static ?string $recordTitleAttribute = 'title';
    protected static ?int $navigationSort = 4;  

    public static function form(Schema $schema): Schema
    {
        return ImageCollectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ImageCollectionsTable::configure($table);
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
            'index' => ListImageCollections::route('/'),
            'create' => CreateImageCollection::route('/create'),
            'edit' => EditImageCollection::route('/{record}/edit'),
        ];
    }
}
