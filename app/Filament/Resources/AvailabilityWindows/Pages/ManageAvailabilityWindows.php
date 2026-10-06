<?php

namespace App\Filament\Resources\AvailabilityWindows\Pages;

use App\Filament\Resources\AvailabilityWindows\AvailabilityWindowResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAvailabilityWindows extends ManageRecords
{
    protected static string $resource = AvailabilityWindowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
