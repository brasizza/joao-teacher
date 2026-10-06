<?php

namespace Tests\Feature\Livewire;

use App\Enums\BookingStatus;
use App\Enums\Weekday;
use App\Livewire\BookingScheduler;
use App\Models\AvailabilityWindow;
use App\Models\BookingRequest;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class BookingSchedulerTest extends TestCase
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

    public function test_lists_the_available_days_and_slots(): void
    {
        $this->createScheduleWithMondayMorning();

        $component = Livewire::test(BookingScheduler::class);

        $component
            ->assertSet('selectedDate', '2026-10-05')
            ->assertSeeInOrder(['09:00', '09:30']);
    }

    public function test_valid_request_is_stored_as_pending_and_redirects_to_whatsapp(): void
    {
        $this->createScheduleWithMondayMorning();

        $component = $this->submitBooking(time: '09:00', name: 'Maria Souza', phone: '(11) 91234-5678');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('https://wa.me/5511987654321?text=Ol%C3%A1%21%20Sou%20Maria%20Souza%20e%20quero%20uma%20aula%20experimental%20em%20segunda-feira%2C%2005%20de%20outubro%20%C3%A0s%2009%3A00.');
        $this->assertDatabaseHas('booking_requests', [
            'name' => 'Maria Souza',
            'phone' => '5511912345678',
            'starts_at' => '2026-10-05 09:00:00',
            'ends_at' => '2026-10-05 09:30:00',
            'status' => BookingStatus::Pending->value,
            'ip_address' => '127.0.0.1',
        ]);
    }

    public function test_rejects_a_slot_taken_after_the_page_was_loaded(): void
    {
        $this->createScheduleWithMondayMorning();
        $component = Livewire::test(BookingScheduler::class);
        BookingRequest::factory()->confirmed()->startingAt('2026-10-05 09:00')->create();
        $this->travel(10)->seconds();

        $component
            ->set('selectedTime', '09:00')
            ->set('name', 'Maria Souza')
            ->set('phone', '11912345678')
            ->call('book');

        $component
            ->assertHasErrors(['selectedTime'])
            ->assertNoRedirect();
        $this->assertDatabaseCount('booking_requests', 1);
    }

    public function test_rejects_a_time_outside_the_availability(): void
    {
        $this->createScheduleWithMondayMorning();

        $component = $this->submitBooking(time: '13:00');

        $component->assertHasErrors(['selectedTime']);
        $this->assertDatabaseCount('booking_requests', 0);
    }

    public function test_requires_a_name_and_a_whatsapp_with_area_code(): void
    {
        $this->createScheduleWithMondayMorning();

        $component = $this->submitBooking(name: '', phone: '98765-4321');

        $component->assertHasErrors(['name', 'phone']);
        $this->assertDatabaseCount('booking_requests', 0);
    }

    public function test_rejects_a_second_pending_request_from_the_same_whatsapp(): void
    {
        $this->createScheduleWithMondayMorning();
        BookingRequest::factory()->pending()->startingAt('2026-10-05 10:00')->create(['phone' => '5511912345678']);

        $component = $this->submitBooking(time: '09:00', phone: '(11) 91234-5678');

        $component->assertHasErrors(['phone']);
        $this->assertDatabaseCount('booking_requests', 1);
    }

    public function test_blocks_requests_beyond_the_hourly_limit_per_visitor(): void
    {
        $this->createScheduleWithMondayMorning();

        foreach (['09:00', '09:30', '10:00'] as $index => $time) {
            $this->submitBooking(time: $time, phone: "1191234567{$index}")->assertHasNoErrors();
        }

        $component = $this->submitBooking(time: '10:30', phone: '11912345679');

        $component
            ->assertHasErrors(['form'])
            ->assertNoRedirect();
        $this->assertDatabaseCount('booking_requests', 3);
    }

    public function test_ignores_submissions_that_fill_the_hidden_field(): void
    {
        $this->createScheduleWithMondayMorning();

        $component = $this->submitBooking(extra: ['website' => 'https://spam.example']);

        $component->assertNoRedirect();
        $this->assertDatabaseCount('booking_requests', 0);
    }

    public function test_ignores_submissions_sent_faster_than_a_person_could_type(): void
    {
        $this->createScheduleWithMondayMorning();

        $component = Livewire::test(BookingScheduler::class)
            ->set('selectedTime', '09:00')
            ->set('name', 'Maria Souza')
            ->set('phone', '11912345678')
            ->call('book');

        $component->assertNoRedirect();
        $this->assertDatabaseCount('booking_requests', 0);
    }

    public function test_shows_the_confirmation_when_the_teacher_has_no_whatsapp(): void
    {
        $this->createScheduleWithMondayMorning(SiteSetting::factory()->withoutWhatsApp()->create());

        $component = $this->submitBooking(time: '09:00');

        $component
            ->assertNoRedirect()
            ->assertSee('Pedido registrado');
        $this->assertDatabaseCount('booking_requests', 1);
    }

    public function test_offers_whatsapp_contact_when_there_are_no_free_slots(): void
    {
        SiteSetting::factory()->create(['whatsapp_number' => '5511987654321']);

        $component = Livewire::test(BookingScheduler::class);

        $component
            ->assertSee('Agenda online sem horários no momento')
            ->assertSee('https://wa.me/5511987654321', escape: false);
    }

    private function createScheduleWithMondayMorning(?SiteSetting $settings = null): void
    {
        $settings ?? SiteSetting::factory()->create([
            'whatsapp_number' => '5511987654321',
            'whatsapp_booking_message' => 'Olá! Sou {nome} e quero uma aula experimental em {data} às {hora}.',
            'slot_interval_minutes' => 30,
        ]);

        AvailabilityWindow::factory()->forWeekday(Weekday::Monday, '09:00', '11:00')->create();
    }

    /**
     * @param  array<string, string>  $extra
     */
    private function submitBooking(
        string $time = '09:00',
        string $name = 'Maria Souza',
        string $phone = '(11) 91234-5678',
        array $extra = [],
    ): Testable {
        $component = Livewire::test(BookingScheduler::class);

        $this->travel(10)->seconds();

        return $component
            ->set('selectedDate', '2026-10-05')
            ->set('selectedTime', $time)
            ->set('name', $name)
            ->set('phone', $phone)
            ->set($extra)
            ->call('book');
    }
}
