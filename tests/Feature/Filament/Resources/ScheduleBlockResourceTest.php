<?php

namespace Tests\Feature\Filament\Resources;

use App\Enums\Weekday;
use App\Filament\Resources\ScheduleBlocks\Pages\ManageScheduleBlocks;
use App\Filament\Resources\ScheduleBlocks\ScheduleBlockResource;
use App\Models\AvailabilityWindow;
use App\Models\BookingRequest;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScheduleBlockResourceTest extends TestCase
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

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(ScheduleBlockResource::getUrl());

        $response->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_calendar_opens_on_today_and_shows_its_slots(): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '09:00', '10:00')->create();

        $component = Livewire::test(ManageScheduleBlocks::class);

        $component
            ->assertSet('weekStart', '2026-10-05')
            ->assertSet('selectedDate', '2026-10-05')
            ->assertSeeInOrder(['segunda-feira, 05 de outubro', '09:00', '09:30']);
    }

    public function test_first_click_blocks_a_slot_and_second_click_frees_it(): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Tuesday, '18:00', '20:00')->create();
        $component = Livewire::test(ManageScheduleBlocks::class);

        $component->call('toggleSlot', '2026-10-06 19:00');

        $component->assertNotNotified();
        $this->assertDatabaseHas('schedule_blocks', ['starts_at' => '2026-10-06 19:00:00', 'ends_at' => '2026-10-06 19:30:00']);

        $component->call('toggleSlot', '2026-10-06 19:00');

        $this->assertDatabaseEmpty('schedule_blocks');
    }

    public function test_warns_when_the_slot_is_held_by_a_trial_lesson(): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Tuesday, '18:00', '20:00')->create();
        BookingRequest::factory()->confirmed()->startingAt('2026-10-06 19:00')->create();

        $component = Livewire::test(ManageScheduleBlocks::class)->call('toggleSlot', '2026-10-06 19:00');

        $component->assertNotified('Este horário não pode ser alterado');
        $this->assertDatabaseEmpty('schedule_blocks');
    }

    public function test_rejects_a_malformed_slot_time(): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create();
        AvailabilityWindow::factory()->forWeekday(Weekday::Tuesday, '18:00', '20:00')->create();

        $component = Livewire::test(ManageScheduleBlocks::class)->call('toggleSlot', 'amanhã às 19h');

        $component->assertNotified('Este horário não pode ser alterado');
        $this->assertDatabaseEmpty('schedule_blocks');
    }

    public function test_next_week_opens_on_its_monday(): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create();

        $component = Livewire::test(ManageScheduleBlocks::class)->call('nextWeek');

        $component
            ->assertSet('weekStart', '2026-10-12')
            ->assertSet('selectedDate', '2026-10-12');
    }

    public function test_cannot_go_back_before_the_current_week(): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create();

        $component = Livewire::test(ManageScheduleBlocks::class)
            ->call('nextWeek')
            ->call('previousWeek')
            ->call('previousWeek');

        $component
            ->assertSet('weekStart', '2026-10-05')
            ->assertSet('selectedDate', '2026-10-05');
    }
}
