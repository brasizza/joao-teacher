@php
    use App\Enums\CalendarSlotStatus;
    use App\Filament\Resources\AvailabilityWindows\AvailabilityWindowResource;

    $days = $this->calendarDays;
    $selectedDay = $this->selectedCalendarDay;
@endphp

<x-filament::section>
    <x-slot name="heading">Calendário de bloqueios</x-slot>

    <x-slot name="description">
        Escolha o dia e toque nos horários em que você não vai atender, como as aulas fixas. Um toque bloqueia (vermelho), outro libera.
    </x-slot>

    <x-slot name="afterHeader">
        <div class="flex items-center gap-1">
            <x-filament::icon-button
                icon="heroicon-m-chevron-left"
                color="gray"
                label="Semana anterior"
                wire:click="previousWeek"
                :disabled="$this->isShowingCurrentWeek"
            />

            <span class="min-w-28 text-center text-sm font-medium text-gray-950 tabular-nums dark:text-white">
                {{ $days->first()['date']->translatedFormat('d M') }} – {{ $days->last()['date']->translatedFormat('d M') }}
            </span>

            <x-filament::icon-button
                icon="heroicon-m-chevron-right"
                color="gray"
                label="Próxima semana"
                wire:click="nextWeek"
            />
        </div>
    </x-slot>

    <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
        @foreach ($days as $day)
            @php
                $dateValue = $day['date']->toDateString();
                $isSelectedDay = $dateValue === $selectedDay['date']->toDateString();
                $isPastDay = $day['date']->isPast() && ! $day['date']->isToday();
                $hasSlots = $day['slots']->isNotEmpty();
            @endphp

            <button
                type="button"
                wire:key="calendar-day-{{ $dateValue }}"
                wire:click="selectDate('{{ $dateValue }}')"
                @disabled($isPastDay)
                aria-pressed="{{ $isSelectedDay ? 'true' : 'false' }}"
                aria-label="{{ $day['date']->translatedFormat('l, d \d\e F') }}{{ $day['blockedCount'] ? " · {$day['blockedCount']} bloqueado(s)" : '' }}"
                @class([
                    'relative flex flex-col items-center rounded-xl border py-2.5 transition sm:py-3',
                    'border-primary-600 bg-primary-600 text-white dark:border-primary-500 dark:bg-primary-500' => $isSelectedDay,
                    'border-gray-200 bg-white text-gray-950 hover:border-gray-400 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/30' => ! $isSelectedDay && $hasSlots,
                    'border-dashed border-gray-200 bg-transparent text-gray-400 hover:border-gray-400 dark:border-white/10 dark:text-gray-500' => ! $isSelectedDay && ! $hasSlots,
                    'cursor-not-allowed opacity-40 hover:border-gray-200 dark:hover:border-white/10' => $isPastDay,
                ])
            >
                <span class="text-[11px] font-medium tracking-wider uppercase opacity-70">{{ $day['date']->translatedFormat('D') }}</span>
                <span class="text-xl leading-tight font-semibold tabular-nums sm:text-2xl">{{ $day['date']->format('d') }}</span>
                <span class="text-[11px] tracking-wider uppercase opacity-70">{{ $day['date']->translatedFormat('M') }}</span>

                @if ($day['blockedCount'] > 0)
                    <span class="absolute -top-1.5 -right-1.5 grid min-w-5 place-items-center rounded-full bg-danger-600 px-1 text-[11px] leading-5 font-semibold text-white ring-2 ring-white tabular-nums dark:ring-gray-900">
                        {{ $day['blockedCount'] }}
                    </span>
                @endif
            </button>
        @endforeach
    </div>

    <div class="mt-6">
        <p class="text-sm font-semibold text-gray-950 first-letter:uppercase dark:text-white">
            {{ $selectedDay['date']->translatedFormat('l, d \d\e F') }}
        </p>

        @if ($selectedDay['slots']->isEmpty())
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Você não atende neste dia da semana.
                <a href="{{ AvailabilityWindowResource::getUrl() }}" class="font-medium text-primary-600 underline hover:text-primary-500 dark:text-primary-400">Ajustar a disponibilidade semanal</a>
            </p>
        @else
            <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-5 lg:grid-cols-8">
                @foreach ($selectedDay['slots'] as $slot)
                    @php
                        $time = $slot['start']->format('H:i');
                        $status = $slot['status'];
                    @endphp

                    <button
                        type="button"
                        wire:key="calendar-slot-{{ $slot['start']->getTimestamp() }}"
                        @if ($status->isToggleable())
                            wire:click="toggleSlot('{{ $slot['start']->format('Y-m-d H:i') }}')"
                            aria-pressed="{{ $status === CalendarSlotStatus::Blocked ? 'true' : 'false' }}"
                        @else
                            disabled
                        @endif
                        aria-label="{{ $time }} · {{ $status->getLabel() }}{{ filled($slot['note']) ? " · {$slot['note']}" : '' }}"
                        @if (filled($slot['note'])) title="{{ $slot['note'] }}" @endif
                        @class([
                            'flex min-h-11 flex-col items-center justify-center rounded-lg border px-1 py-2 text-sm font-medium tabular-nums transition data-loading:opacity-50',
                            'border-gray-200 bg-white text-gray-950 hover:border-danger-400 hover:bg-danger-50 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-danger-400 dark:hover:bg-danger-500/10' => $status === CalendarSlotStatus::Free,
                            'border-danger-600 bg-danger-600 text-white hover:bg-danger-500 dark:border-danger-500 dark:bg-danger-500' => $status === CalendarSlotStatus::Blocked,
                            'cursor-not-allowed border-primary-200 bg-primary-50 text-primary-700 dark:border-primary-400/30 dark:bg-primary-400/10 dark:text-primary-300' => $status === CalendarSlotStatus::Booked,
                            'cursor-not-allowed border-dashed border-gray-200 text-gray-400 dark:border-white/10 dark:text-gray-500' => $status === CalendarSlotStatus::Past,
                        ])
                    >
                        <span>{{ $time }}</span>

                        @if (filled($slot['note']) && $status !== CalendarSlotStatus::Past)
                            <span class="mt-0.5 block w-full truncate text-[11px] font-normal opacity-80">{{ $slot['note'] }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <ul class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-xs text-gray-500 dark:text-gray-400">
        <li class="flex items-center gap-1.5">
            <span class="size-3 rounded border border-gray-300 bg-white dark:border-white/20 dark:bg-white/5"></span> Livre
        </li>
        <li class="flex items-center gap-1.5">
            <span class="size-3 rounded bg-danger-600 dark:bg-danger-500"></span> Bloqueado
        </li>
        <li class="flex items-center gap-1.5">
            <span class="size-3 rounded border border-primary-200 bg-primary-50 dark:border-primary-400/30 dark:bg-primary-400/10"></span> Aula experimental agendada
        </li>
        <li class="flex items-center gap-1.5">
            <span class="size-3 rounded border border-dashed border-gray-300 dark:border-white/20"></span> Já passou
        </li>
    </ul>
</x-filament::section>
