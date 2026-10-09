<?php

namespace App\Services;

use App\Enums\CalendarSlotStatus;
use App\Models\BookingRequest;
use App\Models\ScheduleBlock;
use App\Models\SiteSetting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The teacher's calendar of the weekly availability: every slot with
 * whether it is free, blocked or already taken, and one-click toggling of
 * blocks for a single slot.
 */
class ScheduleBlockCalendar
{
    public function __construct(private AvailabilityService $availability) {}

    /**
     * @return Collection<int, array{date: CarbonImmutable, slots: Collection<int, array{start: CarbonImmutable, status: CalendarSlotStatus, note: string|null}>}>
     */
    public function days(CarbonImmutable $firstDay, int $dayCount): Collection
    {
        $slotMinutes = $this->availability->slotMinutes();
        $days = $this->availability->scheduledDays($firstDay, $dayCount);
        $from = $days->first()['date'];
        $until = $days->last()['date']->addDay();
        $now = CarbonImmutable::now();

        $blocks = ScheduleBlock::query()->overlapping($from, $until)->get();

        $bookings = BookingRequest::query()
            ->blocking(SiteSetting::current()->pending_hold_hours)
            ->overlapping($from, $until)
            ->get();

        return $days->map(fn (array $day): array => [
            'date' => $day['date'],
            'slots' => $day['slots']->map(function (CarbonImmutable $start) use ($slotMinutes, $now, $blocks, $bookings): array {
                $end = $start->addMinutes($slotMinutes);
                $booking = $bookings->first(fn (BookingRequest $booking): bool => $booking->starts_at->lt($end) && $booking->ends_at->gt($start));
                $block = $blocks->first(fn (ScheduleBlock $block): bool => $block->starts_at->lt($end) && $block->ends_at->gt($start));

                [$status, $note] = match (true) {
                    $start->lt($now) => [CalendarSlotStatus::Past, null],
                    $booking !== null => [CalendarSlotStatus::Booked, $booking->name],
                    $block !== null => [CalendarSlotStatus::Blocked, $block->reason],
                    default => [CalendarSlotStatus::Free, null],
                };

                return ['start' => $start, 'status' => $status, 'note' => $note];
            }),
        ]);
    }

    /**
     * Blocks a free slot or frees a blocked one. Returns false when the time
     * is not a slot of the weekly availability, has passed or is taken by a
     * trial lesson.
     */
    public function toggle(CarbonImmutable $start): bool
    {
        $slot = $this->days($start, 1)
            ->first()['slots']
            ->first(fn (array $slot): bool => $slot['start']->equalTo($start));

        $end = $start->addMinutes($this->availability->slotMinutes());

        match ($slot['status'] ?? null) {
            CalendarSlotStatus::Free => ScheduleBlock::query()->create(['starts_at' => $start, 'ends_at' => $end]),
            CalendarSlotStatus::Blocked => $this->unblock($start, $end),
            default => null,
        };

        return $slot !== null && $slot['status']->isToggleable();
    }

    /**
     * Cuts the period out of every block that covers it, so a longer block
     * such as a vacation keeps covering the time before and after it.
     */
    private function unblock(CarbonImmutable $start, CarbonImmutable $end): void
    {
        DB::transaction(function () use ($start, $end): void {
            ScheduleBlock::query()
                ->overlapping($start, $end)
                ->get()
                ->each(function (ScheduleBlock $block) use ($start, $end): void {
                    $coversBefore = $block->starts_at->lt($start);
                    $coversAfter = $block->ends_at->gt($end);

                    if ($coversBefore && $coversAfter) {
                        $block->replicate()->fill(['starts_at' => $end])->save();
                    }

                    match (true) {
                        $coversBefore => $block->update(['ends_at' => $start]),
                        $coversAfter => $block->update(['starts_at' => $end]),
                        default => $block->delete(),
                    };
                });
        });
    }
}
