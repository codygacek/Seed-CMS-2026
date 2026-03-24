<?php

namespace App\Filament\Resources\WidgetContainers\Pages;

use App\Filament\Resources\WidgetContainers\WidgetContainerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWidgetContainer extends EditRecord
{
    protected static string $resource = WidgetContainerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}