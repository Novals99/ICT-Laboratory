<?php

namespace App\Filament\Resources\LabRequests\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;


class LabRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('request_id')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('total_request')
                    ->required()
                    ->numeric()
                    ->default(0),
                DatePicker::make('request_date')
                    ->required(),
                Select::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ])
                    ->default('Pending')
                    ->required(),
            ]);
    }
}
