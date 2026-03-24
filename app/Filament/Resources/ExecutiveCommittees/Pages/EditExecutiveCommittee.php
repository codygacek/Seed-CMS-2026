<?php

namespace App\Filament\Resources\ExecutiveCommittees\Pages;

use App\Filament\Resources\ExecutiveCommittees\ExecutiveCommitteeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExecutiveCommittee extends EditRecord
{
    protected static string $resource = ExecutiveCommitteeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
