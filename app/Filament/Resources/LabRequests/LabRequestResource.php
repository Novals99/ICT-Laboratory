<?php

namespace App\Filament\Resources\LabRequests;

use App\Filament\Resources\LabRequests\Pages\CreateLabRequest;
use App\Filament\Resources\LabRequests\Pages\EditLabRequest;
use App\Filament\Resources\LabRequests\Pages\ListLabRequests;
use App\Filament\Resources\LabRequests\Pages\ViewLabRequest;
use App\Filament\Resources\LabRequests\Schemas\LabRequestForm;
use App\Filament\Resources\LabRequests\Schemas\LabRequestInfolist;
use App\Filament\Resources\LabRequests\Tables\LabRequestsTable;
use App\Models\LabRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LabRequestResource extends Resource
{
    protected static ?string $navigationLabel = 'Lab Request';

    protected static ?string $pluralModelLabel = 'Request List';

    protected static ?string $modelLabel = 'Lab Request';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return LabRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LabRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LabRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLabRequests::route('/'),
            'create' => CreateLabRequest::route('/create'),
            'view' => ViewLabRequest::route('/{record}'),
            'edit' => EditLabRequest::route('/{record}/edit'),
        ];
    }
}
