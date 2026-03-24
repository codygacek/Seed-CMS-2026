<?php

namespace App\Filament\Resources\ImageCollections\Pages;

use App\Filament\Resources\ImageCollections\ImageCollectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateImageCollection extends CreateRecord
{
    protected static string $resource = ImageCollectionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['collection_type'] = 'photo_album';

        return $data;
    }
}
