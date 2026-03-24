<?php

namespace App\Filament\Resources\PageResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns;
use Filament\Tables\Filters;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Columns\TextColumn::make('layout')
                    ->badge()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Columns\IconColumn::make('has_password')
                    ->label('Protected')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->token !== null)
                    ->toggleable(isToggledHiddenByDefault: true),

                Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filters\SelectFilter::make('layout')
                    ->options(self::getLayoutOptions()),
                
                Filters\TernaryFilter::make('has_password')
                    ->label('Password Protected')
                    ->queries(
                        true: fn ($query) => $query->has('token'),
                        false: fn ($query) => $query->doesntHave('token'),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getLayoutOptions(): array
    {
        $layoutsPath = resource_path('views/layouts');
        
        if (!is_dir($layoutsPath)) {
            return ['default' => 'Default'];
        }

        $layouts = [];
        $files = glob($layoutsPath . '/*.blade.php');
        
        foreach ($files as $file) {
            $name = basename($file, '.blade.php');
            $layouts[$name] = ucwords(str_replace(['-', '_'], ' ', $name));
        }

        return $layouts ?: ['default' => 'Default'];
    }
}