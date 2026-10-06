<?php

namespace App\Filament\Resources\BookingRequests;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingRequests\Pages\ManageBookingRequests;
use App\Models\BookingRequest;
use App\Models\SiteSetting;
use App\Services\AvailabilityService;
use App\Services\WhatsAppLink;
use BackedEnum;
use Carbon\CarbonImmutable;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class BookingRequestResource extends Resource
{
    protected static ?string $model = BookingRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Agenda';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'pedido de aula';

    protected static ?string $pluralModelLabel = 'Pedidos de aula';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|Htmlable|null $navigationBadgeTooltip = 'Pedidos aguardando sua confirmação';

    public static function getNavigationBadge(): ?string
    {
        $pendingCount = BookingRequest::query()->pending()->where('starts_at', '>=', now())->count();

        return $pendingCount > 0 ? (string) $pendingCount : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    /**
     * Lesson length used for a request: its stored duration, or the current
     * slot interval for a new one.
     */
    public static function durationInMinutes(?BookingRequest $booking): int
    {
        if ($booking !== null) {
            return (int) $booking->starts_at->diffInMinutes($booking->ends_at);
        }

        return SiteSetting::current()->slot_interval_minutes;
    }

    /**
     * Fill `ends_at` from the chosen start time before saving the form.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function withEndTime(array $data, ?BookingRequest $booking): array
    {
        $data['ends_at'] = CarbonImmutable::parse($data['starts_at'])->addMinutes(static::durationInMinutes($booking));

        return $data;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(120),
                TextInput::make('phone')
                    ->label('WhatsApp')
                    ->tel()
                    ->telRegex('/^[\d\s()+.\-]+$/')
                    ->required()
                    ->rules([
                        fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                            $digitCount = strlen(BookingRequest::normalizePhone((string) $value));

                            if ($digitCount < 12 || $digitCount > 13) {
                                $fail('Informe o WhatsApp com DDD, por exemplo (11) 98765-4321.');
                            }
                        },
                    ]),
                DateTimePicker::make('starts_at')
                    ->label('Data e hora da aula')
                    ->seconds(false)
                    ->required()
                    ->rules([
                        fn (?BookingRequest $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                            if ($record?->status === BookingStatus::Cancelled) {
                                return;
                            }

                            $start = CarbonImmutable::parse($value);
                            $end = $start->addMinutes(static::durationInMinutes($record));

                            if (! app(AvailabilityService::class)->isFree($start, $end, ignoring: $record)) {
                                $fail('Este horário conflita com outro pedido ou com um bloqueio de agenda.');
                            }
                        },
                    ])
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->label('Observações (só você vê)')
                    ->rows(3)
                    ->maxLength(2000)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $pendingHoldHours = SiteSetting::current()->pending_hold_hours;

        return $table
            ->defaultSort('starts_at')
            ->columns([
                TextColumn::make('starts_at')
                    ->label('Aula')
                    ->dateTime('D, d/m/Y · H:i')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Aluno')
                    ->description(fn (BookingRequest $record): string => '+'.$record->phone)
                    ->searchable(['name', 'phone']),
                TextColumn::make('status')
                    ->label('Situação')
                    ->badge()
                    ->description(fn (BookingRequest $record): ?string => $record->isExpired($pendingHoldHours) ? 'Reserva expirada' : null),
                TextColumn::make('notes')
                    ->label('Observações')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Recebido')
                    ->since()
                    ->dateTimeTooltip('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('upcoming')
                    ->label('Somente aulas de hoje em diante')
                    ->query(fn (Builder $query): Builder => $query->where('starts_at', '>=', today()))
                    ->default(),
            ])
            ->recordActions([
                static::confirmAction(),
                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->color('success')
                    ->url(fn (BookingRequest $record, WhatsAppLink $whatsAppLink): string => $whatsAppLink->toStudent($record))
                    ->openUrlInNewTab(),
                ActionGroup::make([
                    EditAction::make()
                        ->mutateDataUsing(fn (array $data, BookingRequest $record): array => static::withEndTime($data, $record)),
                    static::releaseAction(),
                    DeleteAction::make(),
                ]),
            ])
            ->emptyStateHeading('Nenhum pedido por aqui')
            ->emptyStateDescription('Quando alguém escolher um horário no site, o pedido aparece nesta lista.');
    }

    public static function confirmAction(): Action
    {
        return Action::make('confirm')
            ->label('Confirmar')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (BookingRequest $record): bool => $record->status === BookingStatus::Pending)
            ->requiresConfirmation()
            ->modalHeading('Confirmar aula experimental?')
            ->modalDescription(fn (BookingRequest $record): string => "{$record->name} — {$record->starts_at->translatedFormat('l, d/m')} às {$record->starts_at->format('H:i')}")
            ->action(function (BookingRequest $record, AvailabilityService $availability): void {
                if (! $availability->isFree($record->starts_at, $record->ends_at, ignoring: $record)) {
                    Notification::make()
                        ->danger()
                        ->title('Horário indisponível')
                        ->body('Este horário já foi ocupado por outro pedido ou bloqueio. Combine uma nova data com o aluno.')
                        ->send();

                    return;
                }

                $record->confirm();

                Notification::make()->success()->title('Aula confirmada')->send();
            });
    }

    public static function releaseAction(): Action
    {
        return Action::make('release')
            ->label('Liberar horário')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (BookingRequest $record): bool => $record->status !== BookingStatus::Cancelled)
            ->requiresConfirmation()
            ->modalHeading('Liberar este horário?')
            ->modalDescription('O pedido fica marcado como liberado e o horário volta a aparecer no site.')
            ->action(function (BookingRequest $record): void {
                $record->release();

                Notification::make()->success()->title('Horário liberado')->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBookingRequests::route('/'),
        ];
    }
}
