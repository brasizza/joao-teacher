<?php

namespace Tests\Feature\Services;

use App\Models\BookingRequest;
use App\Models\SiteSetting;
use App\Services\WhatsAppLink;
use Tests\TestCase;

class WhatsAppLinkTest extends TestCase
{
    public function test_booking_link_fills_the_message_placeholders_in_portuguese(): void
    {
        $settings = SiteSetting::factory()->make([
            'whatsapp_number' => '5511987654321',
            'whatsapp_booking_message' => 'Sou {nome}: {data} às {hora}?',
        ]);
        $booking = BookingRequest::factory()->startingAt('2026-10-07 15:30')->make(['name' => 'Ana & Bia']);

        $url = app(WhatsAppLink::class)->forBooking($settings, $booking);

        $this->assertSame(
            'https://wa.me/5511987654321?text=Sou%20Ana%20%26%20Bia%3A%20quarta-feira%2C%2007%20de%20outubro%20%C3%A0s%2015%3A30%3F',
            $url,
        );
    }

    public function test_returns_no_link_when_the_teacher_has_no_whatsapp(): void
    {
        $settings = SiteSetting::factory()->withoutWhatsApp()->make();
        $booking = BookingRequest::factory()->make();

        $whatsAppLink = app(WhatsAppLink::class);

        $this->assertNull($whatsAppLink->toTeacher($settings));
        $this->assertNull($whatsAppLink->forBooking($settings, $booking));
    }

    public function test_student_link_targets_the_student_phone(): void
    {
        $booking = BookingRequest::factory()->startingAt('2026-10-07 15:30')->make([
            'name' => 'Ana',
            'phone' => '(11) 91234-5678',
        ]);

        $url = app(WhatsAppLink::class)->toStudent($booking);

        $this->assertStringStartsWith('https://wa.me/5511912345678?text=Ol%C3%A1%2C%20Ana%21', $url);
    }

    public function test_testimonial_request_lets_the_teacher_choose_the_contact(): void
    {
        $url = app(WhatsAppLink::class)->testimonialRequest();

        $this->assertStringStartsWith('https://wa.me/?text=Ol%C3%A1%21', $url);
        $this->assertStringContainsString(rawurlencode(route('testimonials.create')), $url);
    }
}
