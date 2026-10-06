@php
    $completedBooking = $confirmation ?? $flashedConfirmation;
@endphp

<div class="rounded-2xl bg-paper p-6 text-ink shadow-[0_40px_80px_-40px_rgb(0_0_0/0.6)] sm:p-10">
    @if ($completedBooking)
        <div class="py-6 text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-full bg-whatsapp/10 text-whatsapp">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
            </span>

            <h3 class="mt-6 font-serif text-3xl font-medium">Pedido registrado!</h3>

            <p class="mx-auto mt-3 max-w-md leading-relaxed text-ink-soft">
                Sua aula experimental foi solicitada para
                <strong class="font-semibold text-ink">{{ $completedBooking['date'] }} às {{ $completedBooking['time'] }}</strong>.
                O horário fica reservado até a confirmação pelo WhatsApp.
            </p>

            @if (filled($completedBooking['url'] ?? null))
                <a href="{{ $completedBooking['url'] }}" target="_blank" rel="noopener" class="mt-8 inline-flex items-center gap-2 rounded-full bg-whatsapp px-6 py-3 font-medium text-white transition hover:brightness-95">
                    <x-site.whatsapp-icon /> Abrir o WhatsApp novamente
                </a>
            @endif
        </div>
    @elseif ($this->days->isEmpty())
        <div class="py-6 text-center">
            <h3 class="font-serif text-2xl font-medium">Agenda online sem horários no momento</h3>
            <p class="mx-auto mt-3 max-w-md leading-relaxed text-ink-soft">Novos horários são abertos com frequência. Enquanto isso, fale diretamente comigo pelo WhatsApp.</p>

            @if ($this->whatsAppUrl)
                <a href="{{ $this->whatsAppUrl }}" target="_blank" rel="noopener" class="mt-8 inline-flex items-center gap-2 rounded-full bg-whatsapp px-6 py-3 font-medium text-white transition hover:brightness-95">
                    <x-site.whatsapp-icon /> Falar no WhatsApp
                </a>
            @endif
        </div>
    @else
        <form wire:submit="book" class="space-y-10">
            <fieldset class="min-w-0">
                <legend class="text-xs font-semibold tracking-[0.18em] text-ink-soft uppercase">1 · Escolha o dia</legend>

                <div class="mt-4 grid grid-cols-[repeat(auto-fill,minmax(4rem,1fr))] gap-2">
                    @foreach ($this->days as $day)
                        @php
                            $dateValue = $day['date']->toDateString();
                            $isSelectedDay = $dateValue === $this->selectedDay['date']->toDateString();
                        @endphp

                        <button
                            type="button"
                            wire:key="day-{{ $dateValue }}"
                            wire:click="selectDate('{{ $dateValue }}')"
                            aria-pressed="{{ $isSelectedDay ? 'true' : 'false' }}"
                            aria-label="{{ $day['date']->translatedFormat('l, d \d\e F') }}"
                            @class([
                                'flex flex-col items-center rounded-xl border py-3 transition',
                                'border-ink bg-ink text-paper' => $isSelectedDay,
                                'border-line bg-white/60 text-ink hover:border-ink/40' => ! $isSelectedDay,
                            ])
                        >
                            <span class="text-[11px] font-medium tracking-wider uppercase opacity-70">{{ $day['date']->translatedFormat('D') }}</span>
                            <span class="font-serif text-2xl leading-tight">{{ $day['date']->format('d') }}</span>
                            <span class="text-[11px] tracking-wider uppercase opacity-70">{{ $day['date']->translatedFormat('M') }}</span>
                        </button>
                    @endforeach
                </div>
            </fieldset>

            <fieldset class="min-w-0">
                <legend class="text-xs font-semibold tracking-[0.18em] text-ink-soft uppercase">2 · Escolha o horário</legend>
                <p class="mt-2 text-sm text-ink-soft first-letter:uppercase">{{ $this->selectedDay['date']->translatedFormat('l, d \d\e F') }}</p>

                <div class="mt-4 grid grid-cols-3 gap-2 sm:grid-cols-4">
                    @foreach ($this->selectedDay['slots'] as $slot)
                        @php
                            $timeValue = $slot->format('H:i');
                            $isSelectedTime = $timeValue === $selectedTime;
                        @endphp

                        <button
                            type="button"
                            wire:key="slot-{{ $slot->getTimestamp() }}"
                            wire:click="selectTime('{{ $timeValue }}')"
                            aria-pressed="{{ $isSelectedTime ? 'true' : 'false' }}"
                            @class([
                                'rounded-lg border py-2.5 text-sm font-medium tabular-nums transition',
                                'border-ink bg-ink text-paper' => $isSelectedTime,
                                'border-line bg-white/60 text-ink hover:border-ink/40' => ! $isSelectedTime,
                            ])
                        >
                            {{ $timeValue }}
                        </button>
                    @endforeach
                </div>

                @error('selectedTime')
                    <p class="mt-3 text-sm font-medium text-red-700">{{ $message }}</p>
                @enderror
            </fieldset>

            @if ($selectedTime)
                <fieldset class="relative min-w-0 space-y-5">
                    <legend class="text-xs font-semibold tracking-[0.18em] text-ink-soft uppercase">3 · Seus dados</legend>

                    <p class="rounded-lg bg-paper-deep px-4 py-3 text-sm text-ink">
                        Aula experimental em
                        <strong class="font-semibold">{{ $this->selectedDay['date']->translatedFormat('l, d \d\e F') }} às {{ $selectedTime }}</strong>
                        · {{ $this->lessonMinutes }} min
                    </p>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="booking-name" class="text-sm font-medium">Seu nome</label>
                            <input
                                id="booking-name"
                                type="text"
                                wire:model="name"
                                autocomplete="name"
                                required
                                class="mt-2 block w-full rounded-lg border border-line bg-white px-4 py-3 text-ink placeholder:text-ink-soft/60 focus:border-ink focus:ring-2 focus:ring-ink/15 focus:outline-none"
                            >
                            @error('name')
                                <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="booking-phone" class="text-sm font-medium">Seu WhatsApp</label>
                            <input
                                id="booking-phone"
                                type="tel"
                                inputmode="tel"
                                wire:model="phone"
                                autocomplete="tel"
                                placeholder="(11) 98765-4321"
                                required
                                class="mt-2 block w-full rounded-lg border border-line bg-white px-4 py-3 text-ink placeholder:text-ink-soft/60 focus:border-ink focus:ring-2 focus:ring-ink/15 focus:outline-none"
                            >
                            @error('phone')
                                <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div aria-hidden="true" class="absolute -left-[9999px] size-px overflow-hidden">
                        <label for="booking-website">Website</label>
                        <input id="booking-website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
                    </div>

                    @error('form')
                        <p class="text-sm font-medium text-red-700">{{ $message }}</p>
                    @enderror

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="book"
                        class="inline-flex w-full items-center justify-center gap-3 rounded-full bg-ink px-6 py-4 text-base font-medium text-paper transition hover:bg-ink-deep disabled:opacity-60"
                    >
                        <x-site.whatsapp-icon />
                        <span wire:loading.remove wire:target="book">Solicitar aula e abrir o WhatsApp</span>
                        <span wire:loading wire:target="book">Reservando seu horário…</span>
                    </button>

                    <p class="text-center text-xs leading-relaxed text-ink-soft">
                        Seu nome e WhatsApp são usados apenas para combinar a aula.
                    </p>
                </fieldset>
            @endif
        </form>
    @endif
</div>
