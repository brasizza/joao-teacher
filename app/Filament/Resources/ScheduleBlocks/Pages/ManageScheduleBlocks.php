<?php

namespace App\Filament\Resources\ScheduleBlocks\Pages;

use App\Enums\CalendarSlotStatus;
use App\Filament\Resources\ScheduleBlocks\ScheduleBlockResource;
use App\Services\ScheduleBlockCalendar;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

/**
 * Lists the schedule blocks below a week calendar in which the teacher
 * blocks and frees single slots with one click each.
 */
class ManageScheduleBlocks extends ManageRecords
{
    protected static string $resource = ScheduleBlockResource::class;

    /**
     * Monday of the week shown in the calendar, as "Y-m-d".
     */
    public string $weekStart = '';

    /**
     * Day whose slots are shown in the calendar, as "Y-m-d".
     */
    public string $selectedDate = '';

    public function mount(): void
    {
        parent::mount();

        $this->weekStart = $this->currentWeekStart()->toDateString();
        $this->selectedDate = CarbonImmutable::today()->toDateString();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.resources.schedule-blocks.calendar'),
                $this->getTabsContentComponent(),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
                EmbeddedTable::make(),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
            ]);
    }

    /**
     * @return Collection<int, array{date: CarbonImmutable, slots: Collection<int, array{start: CarbonImmutable, status: CalendarSlotStatus, note: string|null}>, blockedCount: int}>
     */
    #[Computed]
    public function calendarDays(): Collection
    {
        return app(ScheduleBlockCalendar::class)
            ->days($this->shownWeekStart(), 7)
            ->map(fn (array $day): array => [
                ...$day,
                'blockedCount' => $day['slots']->where('status', CalendarSlotStatus::Blocked)->count(),
            ]);
    }

    /**
     * @return array{date: CarbonImmutable, slots: Collection<int, array{start: CarbonImmutable, status: CalendarSlotStatus, note: string|null}>, blockedCount: int}
     */
    #[Computed]
    public function selectedCalendarDay(): array
    {
        return $this->calendarDays->first(fn (array $day): bool => $day['date']->toDateString() === $this->selectedDate)
            ?? $this->calendarDays->first(fn (array $day): bool => ! $day['date']->isPast() || $day['date']->isToday())
            ?? $this->calendarDays->first();
    }

    #[Computed]
    public function isShowingCurrentWeek(): bool
    {
        return $this->shownWeekStart()->equalTo($this->currentWeekStart());
    }

    public function previousWeek(): void
    {
        $this->showWeek($this->shownWeekStart()->subWeek());
    }

    public function nextWeek(): void
    {
        $this->showWeek($this->shownWeekStart()->addWeek());
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        unset($this->selectedCalendarDay);
    }

    public function toggleSlot(string $start, ScheduleBlockCalendar $calendar): void
    {
        $slotStart = CarbonImmutable::canBeCreatedFromFormat($start, '!Y-m-d H:i')
            ? CarbonImmutable::createFromFormat('!Y-m-d H:i', $start)
            : null;

        if ($slotStart === null || ! $calendar->toggle($slotStart)) {
            Notification::make()
                ->title('Este horário não pode ser alterado')
                ->body('Ele já passou ou está reservado para uma aula experimental.')
                ->warning()
                ->send();
        }

        unset($this->calendarDays, $this->selectedCalendarDay);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Bloquear período'),
        ];
    }

    private function showWeek(CarbonImmutable $weekStart): void
    {
        $weekStart = $weekStart->max($this->currentWeekStart());

        $this->weekStart = $weekStart->toDateString();
        $this->selectedDate = $weekStart->equalTo($this->currentWeekStart())
            ? CarbonImmutable::today()->toDateString()
            : $weekStart->toDateString();

        unset($this->calendarDays, $this->selectedCalendarDay, $this->isShowingCurrentWeek);
    }

    /**
     * The week in the `weekStart` property, never earlier than the current one.
     */
    private function shownWeekStart(): CarbonImmutable
    {
        $weekStart = CarbonImmutable::canBeCreatedFromFormat($this->weekStart, '!Y-m-d')
            ? CarbonImmutable::createFromFormat('!Y-m-d', $this->weekStart)->startOfWeek(CarbonInterface::MONDAY)
            : $this->currentWeekStart();

        return $weekStart->max($this->currentWeekStart());
    }

    private function currentWeekStart(): CarbonImmutable
    {
        return CarbonImmutable::today()->startOfWeek(CarbonInterface::MONDAY);
    }
}
