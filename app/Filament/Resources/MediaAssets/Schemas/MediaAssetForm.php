<?php

namespace App\Filament\Resources\MediaAssets\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Filament\Actions\Action;

class MediaAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('preview')
                    ->label('Image Preview')
                    ->content(function ($record) {
                        if (! $record?->file) {
                            return new HtmlString(
                                '<div class="text-sm text-gray-500">No image uploaded.</div>'
                            );
                        }

                        $url = Storage::disk('public')->url($record->file);

                        return new HtmlString(
                            '<div class="w-full max-w-xl overflow-hidden rounded-xl border bg-white">
                                <img src="' . e($url) . '" class="block w-full h-auto object-cover" />
                            </div>'
                        );
                    })
                    ->columnSpanFull()
                    ->visible(fn ($operation) => $operation === 'edit'),
                TextInput::make('title')
                    ->required(),
                TextInput::make('file')
                    ->label('Storage Path')
                    ->disabled()
                    ->dehydrated(false)
                    ->suffixAction(
                        Action::make('open')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->url(fn ($record) =>
                                $record?->file
                                    ? Storage::disk('public')->url($record->file)
                                    : null
                            , shouldOpenInNewTab: true)
                            ->visible(fn ($record) => filled($record?->file))
                    ),
                TextInput::make('extension')
                    ->required()
                    ->hidden(),
                TextInput::make('alt_text')
                    ->nullable()
                    ->maxLength(255),
                TextInput::make('content')
                    ->nullable(),
            ]);
    }
}
