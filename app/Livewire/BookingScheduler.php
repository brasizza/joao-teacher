<?php

namespace App\Livewire;

use App\Livewire\Concerns\ProtectsAgainstSpam;
use App\Models\BookingRequest;
use App\Models\SiteSetting;
use App\Services\AvailabilityService;
use App\Services\WhatsAppLink;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Public trial-lesson scheduler: the visitor picks a day and a slot, leaves
 * a name and WhatsApp number, and is sent to WhatsApp after the request is
 * stored as pending.
 */
class BookingScheduler extends Component
{
    use ProtectsAgainstSpam;

    /**
     * Submissions allowed per IP address in one hour.
     */
    public const int MaxAttemptsPerHour = 3;

    public ?string $selectedDate = null;

    public ?string $selectedTime = null;

    public string $name = '';

    public string $phone = '';

    /**
     * @var array{date: string, time: string}|null
     */
    public ?array $confirmation = null;

    public function mount(): void
    {
        $this->selectedDate = ($this->days->first()['date'] ?? null)?->toDateString();
    }

    /**
     * @return Collection<int, array{date: CarbonImmutable, slots: Collection<int, CarbonImmutable>}>
     */
    #[Computed]
    public function days(): Collection
    {
        return app(AvailabilityService::class)->daysWithSlots();
    }

    /**
     * @return array{date: CarbonImmutable, slots: Collection<int, CarbonImmutable>}|null
     */
    #[Computed]
    public function selectedDay(): ?array
    {
        return $this->days->first(fn (array $day): bool => $day['date']->toDateString() === $this->selectedDate)
            ?? $this->days->first();
    }

    #[Computed]
    public function lessonMinutes(): int
    {
        return SiteSetting::current()->slot_interval_minutes;
    }

    #[Computed]
    public function whatsAppUrl(): ?string
    {
        return app(WhatsAppLink::class)->toTeacher(SiteSetting::current());
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->selectedTime = null;
    }

    public function selectTime(string $time): void
    {
        $this->selectedTime = $time;
    }

    public function book(AvailabilityService $availability, WhatsAppLink $whatsAppLink): void
    {
        if ($this->isLikelySpam() || $this->exceedsHourlyLimit('booking-request', self::MaxAttemptsPerHour)) {
            return;
        }

        $this->validate(
            [
                'selectedDate' => ['required', 'date_format:Y-m-d'],
                'selectedTime' => ['required', 'date_format:H:i'],
                'name' => ['required', 'string', 'min:2', 'max:120'],
                'phone' => ['required', 'string', 'max:30', $this->phoneRule()],
            ],
            [
                'selectedDate.required' => 'Escolha um dia.',
                'selectedTime.required' => 'Escolha um horário.',
                'name.required' => 'Informe seu nome.',
                'name.min' => 'Informe seu nome.',
                'phone.required' => 'Informe seu WhatsApp.',
            ],
        );

        $phone = BookingRequest::normalizePhone($this->phone);

        if (BookingRequest::query()->pending()->where('phone', $phone)->where('starts_at', '>=', now())->exists()) {
            $this->addError('phone', 'Já existe um pedido pendente para este WhatsApp. Em breve o professor entra em contato.');

            return;
        }

        $this->countSubmission('booking-request');

        $start = CarbonImmutable::createFromFormat('!Y-m-d H:i', "{$this->selectedDate} {$this->selectedTime}");

        try {
            $booking = Cache::lock('booking-requests:create', 10)->block(5, function () use ($availability, $start, $phone): ?BookingRequest {
                if (! $availability->isOffered($start)) {
                    return null;
                }

                return BookingRequest::query()->create([
                    'name' => trim($this->name),
                    'phone' => $phone,
                    'starts_at' => $start,
                    'ends_at' => $start->addMinutes($this->lessonMinutes),
                    'ip_address' => request()->ip(),
                ]);
            });
        } catch (LockTimeoutException) {
            $this->addError('form', 'Muitas pessoas agendando agora. Tente novamente em alguns segundos.');

            return;
        }

        if ($booking === null) {
            $this->selectedTime = null;
            unset($this->days, $this->selectedDay);
            $this->addError('selectedTime', 'Este horário acabou de ser reservado. Escolha outro, por favor.');

            return;
        }

        $this->confirmation = [
            'date' => $booking->starts_at->translatedFormat('l, d \d\e F'),
            'time' => $booking->starts_at->format('H:i'),
        ];

        $url = $whatsAppLink->forBooking(SiteSetting::current(), $booking);

        if ($url !== null) {
            session()->flash('booking_confirmation', [...$this->confirmation, 'url' => $url]);

            $this->redirect($url);
        }
    }

    public function render(): View
    {
        return view('livewire.booking-scheduler', [
            'flashedConfirmation' => session('booking_confirmation'),
        ]);
    }

    /**
     * Brazilian mobile and landline numbers, with or without the 55 prefix.
     */
    private function phoneRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $digitCount = strlen(BookingRequest::normalizePhone((string) $value));

            if ($digitCount < 12 || $digitCount > 13) {
                $fail('Informe seu WhatsApp com DDD, por exemplo (11) 98765-4321.');
            }
        };
    }
}
