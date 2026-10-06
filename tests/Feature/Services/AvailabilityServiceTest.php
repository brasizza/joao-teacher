<?php

namespace Tests\Feature\Services;

use App\Enums\Weekday;
use App\Models\AvailabilityWindow;
use App\Models\BookingRequest;
use App\Models\ScheduleBlock;
use App\Models\SiteSetting;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * Monday, 5 October 2026, early morning in São Paulo.
     */
    private const string Now = '2026-10-05 07:00:00';

    private const string Today = '2026-10-05';

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(self::Now);
    }

    public function test_generates_slots_at_the_configured_interval_inside_a_window(): void
    {
        SiteSetting::factory()->create(['slot_interval_minutes' => 30]);
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '09:00', '11:00')->create();

        $slots = $this->slotTimesOn(self::Today);

        $this->assertSame(['09:00', '09:30', '10:00', '10:30'], $slots);
    }

    public function test_does_not_offer_a_slot_that_would_end_after_the_window(): void
    {
        SiteSetting::factory()->create(['slot_interval_minutes' => 45]);
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '09:00', '11:00')->create();

        $slots = $this->slotTimesOn(self::Today);

        $this->assertSame(['09:00', '09:45'], $slots);
    }

    public function test_skips_slots_inside_the_minimum_notice_period(): void
    {
        SiteSetting::factory()->create(['minimum_notice_hours' => 3]);
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '09:00', '11:00')->create();

        $slots = $this->slotTimesOn(self::Today);

        $this->assertSame(['10:00', '10:30'], $slots);
    }

    public function test_lists_only_days_with_slots_inside_the_booking_window(): void
    {
        SiteSetting::factory()->create(['booking_window_days' => 3]);
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday)->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Wednesday)->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Thursday)->create();

        $days = app(AvailabilityService::class)->daysWithSlots();

        $this->assertSame(
            ['2026-10-05', '2026-10-07'],
            $days->map(fn (array $day): string => $day['date']->toDateString())->all(),
        );
    }

    public function test_removes_slots_overlapping_a_schedule_block(): void
    {
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '09:00', '11:00')->create();
        ScheduleBlock::factory()->between('2026-10-05 09:15', '2026-10-05 10:00')->create();

        $slots = $this->slotTimesOn(self::Today);

        $this->assertSame(['10:00', '10:30'], $slots);
    }

    public function test_removes_slots_held_by_pending_and_confirmed_requests(): void
    {
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '09:00', '11:00')->create();
        BookingRequest::factory()->pending()->startingAt('2026-10-05 09:00')->create();
        BookingRequest::factory()->confirmed()->startingAt('2026-10-05 10:00')->create();

        $slots = $this->slotTimesOn(self::Today);

        $this->assertSame(['09:30', '10:30'], $slots);
    }

    public function test_offers_again_slots_of_released_and_expired_pending_requests(): void
    {
        SiteSetting::factory()->create(['pending_hold_hours' => 24]);
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '09:00', '10:00')->create();
        BookingRequest::factory()->cancelled()->startingAt('2026-10-05 09:00')->create();
        BookingRequest::factory()->pending()->startingAt('2026-10-05 09:30')->create(['created_at' => now()->subHours(25)]);

        $slots = $this->slotTimesOn(self::Today);

        $this->assertSame(['09:00', '09:30'], $slots);
    }

    public function test_keeps_pending_requests_reserved_when_no_hold_period_is_set(): void
    {
        SiteSetting::factory()->create(['pending_hold_hours' => null]);
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '09:00', '10:00')->create();
        BookingRequest::factory()->pending()->startingAt('2026-10-05 09:30')->create(['created_at' => now()->subMonth()]);

        $slots = $this->slotTimesOn(self::Today);

        $this->assertSame(['09:00'], $slots);
    }

    public function test_ignores_inactive_windows(): void
    {
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '09:00', '10:00')->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '14:00', '15:00')->inactive()->create();

        $slots = $this->slotTimesOn(self::Today);

        $this->assertSame(['09:00', '09:30'], $slots);
    }

    public function test_does_not_duplicate_slots_of_overlapping_windows(): void
    {
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '09:30', '10:30')->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '09:00', '10:00')->create();

        $slots = $this->slotTimesOn(self::Today);

        $this->assertSame(['09:00', '09:30', '10:00'], $slots);
    }

    public function test_a_longer_request_blocks_every_slot_it_overlaps(): void
    {
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '09:00', '11:00')->create();
        BookingRequest::factory()->confirmed()->startingAt('2026-10-05 09:00', durationMinutes: 90)->create();

        $slots = $this->slotTimesOn(self::Today);

        $this->assertSame(['10:30'], $slots);
    }

    public function test_does_not_offer_a_time_outside_the_availability(): void
    {
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '09:00', '10:00')->create();
        $service = app(AvailabilityService::class);

        $this->assertTrue($service->isOffered(CarbonImmutable::parse('2026-10-05 09:30')));
        $this->assertFalse($service->isOffered(CarbonImmutable::parse('2026-10-05 09:15')));
        $this->assertFalse($service->isOffered(CarbonImmutable::parse('2026-10-05 13:00')));
    }

    public function test_reports_a_period_as_busy_unless_the_clashing_request_is_ignored(): void
    {
        SiteSetting::factory()->create();
        $booking = BookingRequest::factory()->confirmed()->startingAt('2026-10-05 09:00')->create();
        $service = app(AvailabilityService::class);
        $start = CarbonImmutable::parse('2026-10-05 09:15');
        $end = CarbonImmutable::parse('2026-10-05 09:45');

        $this->assertFalse($service->isFree($start, $end));
        $this->assertTrue($service->isFree($start, $end, ignoring: $booking));
    }

    /**
     * @return list<string>
     */
    private function slotTimesOn(string $date): array
    {
        $day = app(AvailabilityService::class)
            ->daysWithSlots()
            ->first(fn (array $day): bool => $day['date']->toDateString() === $date);

        return $day === null ? [] : $day['slots']->map(fn (CarbonImmutable $slot): string => $slot->format('H:i'))->all();
    }
}
