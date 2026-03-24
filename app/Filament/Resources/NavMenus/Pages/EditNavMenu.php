<?php

namespace App\Filament\Resources\NavMenus\Pages;

use App\Filament\Resources\NavMenus\NavMenuResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNavMenu extends EditRecord
{
    protected static string $resource = NavMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
