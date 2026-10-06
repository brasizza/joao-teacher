<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingRequests\BookingRequestResource;
use App\Models\BookingRequest;
use App\Services\WhatsAppLink;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class UpcomingBookings extends TableWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Próximas aulas experimentais')
            ->query(fn (): Builder => BookingRequest::query()
                ->whereIn('status', [BookingStatus::Pending, BookingStatus::Confirmed])
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(8))
            ->paginated(false)
            ->columns([
                TextColumn::make('starts_at')
                    ->label('Aula')
                    ->dateTime('D, d/m · H:i'),
                TextColumn::make('name')
                    ->label('Aluno'),
                TextColumn::make('status')
                    ->label('Situação')
                    ->badge(),
            ])
            ->recordActions([
                BookingRequestResource::confirmAction(),
                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->color('success')
                    ->url(fn (BookingRequest $record, WhatsAppLink $whatsAppLink): string => $whatsAppLink->toStudent($record))
                    ->openUrlInNewTab(),
            ])
            ->headerActions([
                Action::make('all')
                    ->label('Ver todos os pedidos')
                    ->link()
                    ->url(BookingRequestResource::getUrl()),
            ])
            ->emptyStateHeading('Nenhuma aula agendada')
            ->emptyStateDescription('Os pedidos feitos pelo site aparecem aqui.');
    }
}
