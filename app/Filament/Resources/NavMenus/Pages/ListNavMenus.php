<?php

namespace App\Filament\Resources\NavMenus\Pages;

use App\Filament\Resources\NavMenus\NavMenuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNavMenus extends ListRecords
{
    protected static string $resource = NavMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
