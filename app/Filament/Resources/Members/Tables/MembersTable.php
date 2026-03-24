<?php

namespace App\Filament\Resources\Members\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('position')
            ->defaultSort('position')
            ->columns([
                ImageColumn::make('image')
                    ->label('Photo')
                    ->disk('public')
                    ->square()
                    ->getStateUsing(fn ($record) => filled($record->image) ? $record->image : null)
                    ->toggleable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('date')
                    ->label('Year')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('major')
                    ->toggleable()
                    ->wrap(),

                TextColumn::make('current_position')
                    ->label('Position')
                    ->toggleable()
                    ->wrap(),

                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'current',
                        'warning' => 'prospective',
                    ])
                    ->formatStateUsing(fn ($state) =>
                        $state === 'current' ? 'Current' : 'Prospective'
                    )
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'current' => 'Current',
                        'prospective' => 'Prospective',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}