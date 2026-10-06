<?php

namespace App\Services;

use App\Models\AvailabilityWindow;
use App\Models\BookingRequest;
use App\Models\ScheduleBlock;
use App\Models\SiteSetting;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Builds the bookable trial-lesson slots from the teacher's weekly
 * availability, removing blocked periods and slots already requested.
 */
class AvailabilityService
{
    /**
     * Upcoming days that still have at least one free slot.
     *
     * @return Collection<int, array{date: CarbonImmutable, slots: Collection<int, CarbonImmutable>}>
     */
    public function daysWithSlots(): Collection
    {
        $settings = SiteSetting::current();
        $intervalMinutes = max(5, $settings->slot_interval_minutes);
        $earliestStart = CarbonImmutable::now()->addHours($settings->minimum_notice_hours);
        $windowDays = max(1, $settings->booking_window_days);
        $firstDay = CarbonImmutable::today();
        $endOfRange = $firstDay->addDays($windowDays);

        $windowsByWeekday = AvailabilityWindow::query()
            ->active()
            ->get()
            ->toBase()
            ->groupBy(fn (AvailabilityWindow $window): int => $window->weekday->value);

        $busyPeriods = $this->busyPeriods($firstDay, $endOfRange, $settings->pending_hold_hours);

        return collect(range(0, $windowDays - 1))
            ->map(fn (int $offset): CarbonImmutable => $firstDay->addDays($offset))
            ->map(fn (CarbonImmutable $date): array => [
                'date' => $date,
                'slots' => $windowsByWeekday->get($date->dayOfWeek, collect())
                    ->flatMap(fn (AvailabilityWindow $window): array => $this->slotsInWindow($date, $window, $intervalMinutes))
                    ->unique(fn (CarbonImmutable $slot): int => $slot->getTimestamp())
                    ->sortBy(fn (CarbonImmutable $slot): int => $slot->getTimestamp())
                    ->filter(fn (CarbonImmutable $slot): bool => $slot->gte($earliestStart)
                        && ! $this->overlapsAny($slot, $slot->addMinutes($intervalMinutes), $busyPeriods))
                    ->values(),
            ])
            ->filter(fn (array $day): bool => $day['slots']->isNotEmpty())
            ->values();
    }

    /**
     * Whether the given start time is one of the slots currently offered
     * on the public schedule.
     */
    public function isOffered(CarbonImmutable $start): bool
    {
        return $this->daysWithSlots()
            ->flatMap(fn (array $day): Collection => $day['slots'])
            ->contains(fn (CarbonImmutable $slot): bool => $slot->equalTo($start));
    }

    /**
     * Whether the period clashes with no schedule block and no request that
     * still holds its slot, optionally ignoring one request.
     */
    public function isFree(CarbonImmutable $start, CarbonImmutable $end, ?BookingRequest $ignoring = null): bool
    {
        $pendingHoldHours = SiteSetting::current()->pending_hold_hours;

        $clashesWithBlock = ScheduleBlock::query()->overlapping($start, $end)->exists();

        $clashesWithBooking = BookingRequest::query()
            ->blocking($pendingHoldHours)
            ->overlapping($start, $end)
            ->when($ignoring, fn (Builder $query, BookingRequest $booking): Builder => $query->whereKeyNot($booking->getKey()))
            ->exists();

        return ! $clashesWithBlock && ! $clashesWithBooking;
    }

    /**
     * @return list<CarbonImmutable>
     */
    private function slotsInWindow(CarbonImmutable $date, AvailabilityWindow $window, int $intervalMinutes): array
    {
        $cursor = CarbonImmutable::parse("{$date->toDateString()} {$window->start_time}");
        $windowEnd = CarbonImmutable::parse("{$date->toDateString()} {$window->end_time}");
        $slots = [];

        while ($cursor->addMinutes($intervalMinutes)->lte($windowEnd)) {
            $slots[] = $cursor;
            $cursor = $cursor->addMinutes($intervalMinutes);
        }

        return $slots;
    }

    /**
     * @return Collection<int, array{0: CarbonImmutable, 1: CarbonImmutable}>
     */
    private function busyPeriods(CarbonImmutable $from, CarbonImmutable $until, ?int $pendingHoldHours): Collection
    {
        $blocks = ScheduleBlock::query()
            ->overlapping($from, $until)
            ->get(['starts_at', 'ends_at']);

        $bookings = BookingRequest::query()
            ->blocking($pendingHoldHours)
            ->overlapping($from, $until)
            ->get(['starts_at', 'ends_at']);

        return $blocks->toBase()
            ->merge($bookings->toBase())
            ->map(fn (ScheduleBlock|BookingRequest $period): array => [$period->starts_at, $period->ends_at]);
    }

    /**
     * @param  Collection<int, array{0: CarbonImmutable, 1: CarbonImmutable}>  $busyPeriods
     */
    private function overlapsAny(CarbonImmutable $start, CarbonImmutable $end, Collection $busyPeriods): bool
    {
        return $busyPeriods->contains(
            fn (array $period): bool => $start->lt($period[1]) && $end->gt($period[0]),
        );
    }
}
