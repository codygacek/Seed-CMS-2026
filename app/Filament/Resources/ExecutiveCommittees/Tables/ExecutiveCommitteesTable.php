<?php

namespace App\Filament\Resources\ExecutiveCommittees\Tables;

use Filament\Tables;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExecutiveCommitteesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->defaultSort('order')
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

                TextColumn::make('position')
                    ->label('Role')
                    ->toggleable()
                    ->wrap(),

                TextColumn::make('major')
                    ->toggleable()
                    ->wrap(),

                TextColumn::make('date')
                    ->label('Year')
                    ->toggleable(),

                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'current',
                        'gray' => 'previous',
                    ])
                    ->formatStateUsing(fn ($state) =>
                        $state === 'current' ? 'Current' : 'Previous'
                    )
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'current' => 'Current',
                        'previous' => 'Previous',
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