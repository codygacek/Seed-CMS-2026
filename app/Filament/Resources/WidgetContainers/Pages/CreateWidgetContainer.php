<?php

namespace App\Filament\Resources\WidgetContainers\Pages;

use App\Filament\Resources\WidgetContainers\WidgetContainerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWidgetContainer extends CreateRecord
{
    protected static string $resource = WidgetContainerResource::class;

    /**
     * Mutate form data before creating
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Encode container options back to JSON (always provide a value)
        if (isset($data['container_options']) && is_array($data['container_options'])) {
            $data['options'] = json_encode($data['container_options']);
        } else {
            // Provide empty JSON object if no options set
            $data['options'] = '{}';
        }
        
        unset($data['container_options']);
        unset($data['widgets']); // No widgets on create

        return $data;
    }
}