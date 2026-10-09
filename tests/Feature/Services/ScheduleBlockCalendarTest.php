<?php

namespace Tests\Feature\Services;

use App\Enums\Weekday;
use App\Models\AvailabilityWindow;
use App\Models\BookingRequest;
use App\Models\ScheduleBlock;
use App\Models\SiteSetting;
use App\Services\ScheduleBlockCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ScheduleBlockCalendarTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * Monday, 5 October 2026, early morning in São Paulo.
     */
    private const string Now = '2026-10-05 07:00:00';

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(self::Now);
    }

    public function test_lists_every_slot_of_the_availability_with_its_status(): void
    {
        SiteSetting::factory()->create(['slot_interval_minutes' => 30, 'minimum_notice_hours' => 12]);
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '09:00', '11:00')->create();
        ScheduleBlock::factory()->between('2026-10-05 09:30', '2026-10-05 10:00')->create(['reason' => 'Aula fixa']);
        BookingRequest::factory()->confirmed()->startingAt('2026-10-05 10:00')->create(['name' => 'Maria Souza']);

        $slots = $this->slotsOn('2026-10-05');

        $this->assertSame([
            '09:00' => ['free', null],
            '09:30' => ['blocked', 'Aula fixa'],
            '10:00' => ['booked', 'Maria Souza'],
            '10:30' => ['free', null],
        ], $slots);
    }

    public function test_marks_slots_that_already_started_as_past(): void
    {
        $this->travelTo('2026-10-05 09:10:00');
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '09:00', '10:00')->create();

        $slots = $this->slotsOn('2026-10-05');

        $this->assertSame(['09:00' => ['past', null], '09:30' => ['free', null]], $slots);
    }

    public function test_shows_a_slot_of_a_released_request_as_free(): void
    {
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '09:00', '09:30')->create();
        BookingRequest::factory()->cancelled()->startingAt('2026-10-05 09:00')->create();

        $slots = $this->slotsOn('2026-10-05');

        $this->assertSame(['09:00' => ['free', null]], $slots);
    }

    public function test_blocking_a_free_slot_creates_a_block_lasting_one_slot(): void
    {
        SiteSetting::factory()->create(['slot_interval_minutes' => 60]);
        AvailabilityWindow::factory()->forWeekday(Weekday::Tuesday, '09:00', '12:00')->create();

        $toggled = app(ScheduleBlockCalendar::class)->toggle(CarbonImmutable::parse('2026-10-06 10:00'));

        $this->assertTrue($toggled);
        $this->assertDatabaseCount('schedule_blocks', 1);
        $this->assertDatabaseHas('schedule_blocks', [
            'starts_at' => '2026-10-06 10:00:00',
            'ends_at' => '2026-10-06 11:00:00',
            'reason' => null,
        ]);
    }

    public function test_freeing_a_slot_deletes_the_block_made_for_it(): void
    {
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Tuesday, '09:00', '12:00')->create();
        $block = ScheduleBlock::factory()->between('2026-10-06 10:00', '2026-10-06 10:30')->create();

        $toggled = app(ScheduleBlockCalendar::class)->toggle(CarbonImmutable::parse('2026-10-06 10:00'));

        $this->assertTrue($toggled);
        $this->assertModelMissing($block);
    }

    public function test_freeing_a_slot_inside_a_longer_block_keeps_the_rest_of_it_blocked(): void
    {
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Tuesday, '09:00', '12:00')->create();
        ScheduleBlock::factory()->between('2026-10-05 00:00', '2026-10-10 00:00')->create(['reason' => 'Férias']);

        app(ScheduleBlockCalendar::class)->toggle(CarbonImmutable::parse('2026-10-06 10:00'));

        $this->assertDatabaseCount('schedule_blocks', 2);
        $this->assertDatabaseHas('schedule_blocks', ['starts_at' => '2026-10-05 00:00:00', 'ends_at' => '2026-10-06 10:00:00', 'reason' => 'Férias']);
        $this->assertDatabaseHas('schedule_blocks', ['starts_at' => '2026-10-06 10:30:00', 'ends_at' => '2026-10-10 00:00:00', 'reason' => 'Férias']);
    }

    public function test_freeing_the_first_slot_of_a_longer_block_moves_its_start(): void
    {
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Tuesday, '09:00', '12:00')->create();
        $block = ScheduleBlock::factory()->between('2026-10-06 09:00', '2026-10-06 11:00')->create();

        app(ScheduleBlockCalendar::class)->toggle(CarbonImmutable::parse('2026-10-06 09:00'));

        $block->refresh();
        $this->assertSame('2026-10-06 09:30:00', $block->starts_at->toDateTimeString());
        $this->assertSame('2026-10-06 11:00:00', $block->ends_at->toDateTimeString());
        $this->assertDatabaseCount('schedule_blocks', 1);
    }

    public function test_does_not_toggle_a_slot_held_by_a_trial_lesson(): void
    {
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Tuesday, '09:00', '12:00')->create();
        BookingRequest::factory()->pending()->startingAt('2026-10-06 10:00')->create();

        $toggled = app(ScheduleBlockCalendar::class)->toggle(CarbonImmutable::parse('2026-10-06 10:00'));

        $this->assertFalse($toggled);
        $this->assertDatabaseEmpty('schedule_blocks');
    }

    public function test_does_not_toggle_a_slot_that_already_started(): void
    {
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '06:00', '09:00')->create();

        $toggled = app(ScheduleBlockCalendar::class)->toggle(CarbonImmutable::parse('2026-10-05 06:30'));

        $this->assertFalse($toggled);
        $this->assertDatabaseEmpty('schedule_blocks');
    }

    public function test_does_not_toggle_a_time_outside_the_availability(): void
    {
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Tuesday, '09:00', '12:00')->create();

        $toggled = app(ScheduleBlockCalendar::class)->toggle(CarbonImmutable::parse('2026-10-06 09:15'));

        $this->assertFalse($toggled);
        $this->assertDatabaseEmpty('schedule_blocks');
    }

    /**
     * @return array<string, array{0: string, 1: string|null}>
     */
    private function slotsOn(string $date): array
    {
        return app(ScheduleBlockCalendar::class)
            ->days(CarbonImmutable::parse($date), 1)
            ->first()['slots']
            ->mapWithKeys(fn (array $slot): array => [$slot['start']->format('H:i') => [$slot['status']->value, $slot['note']]])
            ->all();
    }
}
