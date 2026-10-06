<?php

namespace App\Filament\Resources\LessonExamples\Pages;

use App\Filament\Resources\LessonExamples\LessonExampleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLessonExamples extends ManageRecords
{
    protected static string $resource = LessonExampleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
