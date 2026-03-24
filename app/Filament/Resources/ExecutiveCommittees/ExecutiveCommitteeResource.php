<?php

namespace App\Filament\Resources\ExecutiveCommittees;

use App\Filament\Resources\ExecutiveCommittees\Pages\CreateExecutiveCommittee;
use App\Filament\Resources\ExecutiveCommittees\Pages\EditExecutiveCommittee;
use App\Filament\Resources\ExecutiveCommittees\Pages\ListExecutiveCommittees;
use App\Filament\Resources\ExecutiveCommittees\Schemas\ExecutiveCommitteeForm;
use App\Filament\Resources\ExecutiveCommittees\Tables\ExecutiveCommitteesTable;
use App\Models\ExecutiveCommittee;
use App\Support\CmsFeatures;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ExecutiveCommitteeResource extends Resource
{
    protected static ?string $model = ExecutiveCommittee::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'EC Member';
    protected static ?string $navigationLabel = 'Exectutive Committee';
    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return CmsFeatures::resourceEnabled('executive_committee');
    }

    public static function canAccess(): bool
    {
        return \App\Support\CmsFeatures::resourceEnabled('executive_committee');
    }

    public static function form(Schema $schema): Schema
    {
        return ExecutiveCommitteeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExecutiveCommitteesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExecutiveCommittees::route('/'),
            'create' => CreateExecutiveCommittee::route('/create'),
            'edit' => EditExecutiveCommittee::route('/{record}/edit'),
        ];
    }
}