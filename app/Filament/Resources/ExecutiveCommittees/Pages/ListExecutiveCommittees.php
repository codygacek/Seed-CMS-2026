<?php

namespace App\Filament\Resources\ExecutiveCommittees\Pages;

use App\Filament\Resources\ExecutiveCommittees\ExecutiveCommitteeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExecutiveCommittees extends ListRecords
{
    protected static string $resource = ExecutiveCommitteeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
