<?php

namespace App\Filament\Resources\ImageCollections\Pages;

use App\Filament\Resources\ImageCollections\ImageCollectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListImageCollections extends ListRecords
{
    protected static string $resource = ImageCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
