<?php

namespace App\Filament\Resources\BookingRequests\Pages;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingRequests\BookingRequestResource;
use App\Models\BookingRequest;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManageBookingRequests extends ManageRecords
{
    protected static string $resource = BookingRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar aula')
                ->modalHeading('Registrar aula experimental')
                ->modalDescription('Use para aulas combinadas fora do site. O horário fica reservado como confirmado.')
                ->mutateDataUsing(fn (array $data): array => BookingRequestResource::withEndTime($data, null))
                ->after(fn (BookingRequest $record) => $record->confirm()),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('Pendentes')
                ->badge(fn (): int => BookingRequest::query()->pending()->where('starts_at', '>=', today())->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', BookingStatus::Pending)),
            'confirmed' => Tab::make('Confirmados')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', BookingStatus::Confirmed)),
            'cancelled' => Tab::make('Liberados')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', BookingStatus::Cancelled)),
            'all' => Tab::make('Todos'),
        ];
    }
}
