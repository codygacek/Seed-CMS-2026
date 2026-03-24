<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\AuthToken;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle password separately - don't store in page table
        if (isset($data['password'])) {
            $this->passwordToCreate = $data['password'];
            unset($data['password']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Create auth token if password was provided
        if (!empty($this->passwordToCreate)) {
            AuthToken::create([
                'token' => bcrypt($this->passwordToCreate),
                'tokenable_id' => $this->record->id,
                'tokenable_type' => get_class($this->record),
            ]);
        }
    }

    private $passwordToCreate;
}