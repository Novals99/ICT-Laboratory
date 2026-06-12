<?php

namespace App\Filament\Resources\LabRequests\Pages;

use App\Filament\Resources\LabRequests\LabRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLabRequest extends ViewRecord
{
    protected static string $resource = LabRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
