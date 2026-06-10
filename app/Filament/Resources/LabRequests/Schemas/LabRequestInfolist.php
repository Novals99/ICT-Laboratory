<?php

namespace App\Filament\Resources\LabRequests\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LabRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('request_id'),
                TextEntry::make('name'),
                TextEntry::make('total_request')
                    ->numeric(),
                TextEntry::make('request_date')
                    ->date(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('approved_by')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
