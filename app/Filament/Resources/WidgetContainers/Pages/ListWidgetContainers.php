<?php

namespace App\Filament\Resources\WidgetContainers\Pages;

use App\Filament\Resources\WidgetContainers\WidgetContainerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWidgetContainers extends ListRecords
{
    protected static string $resource = WidgetContainerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
