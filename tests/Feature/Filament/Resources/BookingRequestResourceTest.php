<?php

namespace Tests\Feature\Filament\Resources;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingRequests\BookingRequestResource;
use App\Filament\Resources\BookingRequests\Pages\ManageBookingRequests;
use App\Models\BookingRequest;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookingRequestResourceTest extends TestCase
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

    public function test_confirming_a_pending_request_marks_it_as_confirmed(): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create();
        $booking = BookingRequest::factory()->pending()->startingAt('2026-10-06 09:00')->create();

        $component = Livewire::test(ManageBookingRequests::class)
            ->callAction(TestAction::make('confirm')->table($booking));

        $component->assertNotified('Aula confirmada');
        $booking->refresh();
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertSame('2026-10-05 07:00:00', $booking->confirmed_at?->toDateTimeString());
    }

    public function test_releasing_a_request_frees_its_slot(): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create();
        $booking = BookingRequest::factory()->confirmed()->startingAt('2026-10-06 09:00')->create();

        Livewire::test(ManageBookingRequests::class, ['activeTab' => 'confirmed'])
            ->callAction(TestAction::make('release')->table($booking));

        $this->assertSame(BookingStatus::Cancelled, $booking->refresh()->status);
        $this->assertDatabaseMissing('booking_requests', ['status' => BookingStatus::Confirmed->value]);
    }

    public function test_confirm_action_is_hidden_for_released_requests(): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create();
        $booking = BookingRequest::factory()->cancelled()->startingAt('2026-10-06 09:00')->create();

        $component = Livewire::test(ManageBookingRequests::class, ['activeTab' => 'all']);

        $component->assertActionHidden(TestAction::make('confirm')->table($booking));
    }

    public function test_confirming_an_expired_request_fails_when_its_slot_was_taken(): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create(['pending_hold_hours' => 24]);
        $expired = BookingRequest::factory()->pending()->startingAt('2026-10-06 09:00')->create(['created_at' => now()->subHours(30)]);
        BookingRequest::factory()->confirmed()->startingAt('2026-10-06 09:00')->create();

        $component = Livewire::test(ManageBookingRequests::class)
            ->callAction(TestAction::make('confirm')->table($expired));

        $component->assertNotified('Horário indisponível');
        $this->assertSame(BookingStatus::Pending, $expired->refresh()->status);
    }

    public function test_registering_a_lesson_stores_it_as_confirmed_with_the_slot_length(): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create(['slot_interval_minutes' => 45]);

        $component = Livewire::test(ManageBookingRequests::class)
            ->callAction('create', data: [
                'name' => 'Lucas Pereira',
                'phone' => '(11) 91234-5678',
                'starts_at' => '2026-10-07 14:00:00',
            ]);

        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('booking_requests', [
            'name' => 'Lucas Pereira',
            'phone' => '5511912345678',
            'starts_at' => '2026-10-07 14:00:00',
            'ends_at' => '2026-10-07 14:45:00',
            'status' => BookingStatus::Confirmed->value,
        ]);
    }

    public function test_registering_a_lesson_on_a_taken_slot_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create(['slot_interval_minutes' => 30]);
        BookingRequest::factory()->pending()->startingAt('2026-10-07 14:00')->create();

        $component = Livewire::test(ManageBookingRequests::class)
            ->callAction('create', data: [
                'name' => 'Lucas Pereira',
                'phone' => '(11) 91234-5678',
                'starts_at' => '2026-10-07 14:15:00',
            ]);

        $component->assertHasFormErrors(['starts_at']);
        $this->assertDatabaseCount('booking_requests', 1);
    }

    public function test_navigation_badge_counts_upcoming_pending_requests(): void
    {
        BookingRequest::factory()->pending()->count(2)->sequence(
            ['starts_at' => '2026-10-06 09:00', 'ends_at' => '2026-10-06 09:30'],
            ['starts_at' => '2026-10-07 09:00', 'ends_at' => '2026-10-07 09:30'],
        )->create();
        BookingRequest::factory()->pending()->startingAt('2026-10-02 09:00')->create();
        BookingRequest::factory()->confirmed()->startingAt('2026-10-08 09:00')->create();

        $badge = BookingRequestResource::getNavigationBadge();

        $this->assertSame('2', $badge);
    }
}
