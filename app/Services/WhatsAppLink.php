<?php

namespace App\Services;

use App\Models\BookingRequest;
use App\Models\SiteSetting;

/**
 * Builds "click to chat" links (https://wa.me), which open the WhatsApp app
 * on phones and WhatsApp Web on desktops.
 */
class WhatsAppLink
{
    /**
     * Link to the teacher with the general "I want to know more" message.
     */
    public function toTeacher(SiteSetting $settings): ?string
    {
        if (! $settings->hasWhatsApp()) {
            return null;
        }

        return $this->build($settings->whatsapp_number, $settings->whatsapp_general_message);
    }

    /**
     * Link to the teacher announcing a trial-lesson request, filling the
     * {nome}, {data} and {hora} placeholders of the configured message.
     */
    public function forBooking(SiteSetting $settings, BookingRequest $booking): ?string
    {
        if (! $settings->hasWhatsApp()) {
            return null;
        }

        $message = strtr((string) $settings->whatsapp_booking_message, [
            '{nome}' => $booking->name,
            '{data}' => $booking->starts_at->translatedFormat('l, d \d\e F'),
            '{hora}' => $booking->starts_at->format('H:i'),
        ]);

        return $this->build($settings->whatsapp_number, $message);
    }

    /**
     * Link from the teacher to the student who made the request.
     */
    public function toStudent(BookingRequest $booking): string
    {
        $message = sprintf(
            'Olá, %s! Recebi seu pedido de aula experimental para %s às %s.',
            $booking->name,
            $booking->starts_at->translatedFormat('l, d \d\e F'),
            $booking->starts_at->format('H:i'),
        );

        return $this->build($booking->phone, $message);
    }

    /**
     * Link that opens WhatsApp with the message ready and lets the sender
     * pick which contact receives it.
     */
    public function share(string $message): string
    {
        return 'https://wa.me/?text='.rawurlencode($message);
    }

    /**
     * Message the teacher sends to ask a student for a testimonial.
     */
    public function testimonialRequest(): string
    {
        return $this->share(
            'Olá! Tudo bem? Se puder, deixe um depoimento sobre as nossas aulas. Leva só um minuto: '.route('testimonials.create'),
        );
    }

    private function build(string $phone, ?string $message): string
    {
        $url = 'https://wa.me/'.preg_replace('/\D+/', '', $phone);

        return filled($message) ? $url.'?text='.rawurlencode($message) : $url;
    }
}
