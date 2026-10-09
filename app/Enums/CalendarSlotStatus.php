<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * How a slot appears in the teacher's schedule block calendar.
 */
enum CalendarSlotStatus: string implements HasLabel
{
    case Free = 'free';
    case Blocked = 'blocked';
    case Booked = 'booked';
    case Past = 'past';

    public function getLabel(): string
    {
        return match ($this) {
            self::Free => 'Livre',
            self::Blocked => 'Bloqueado',
            self::Booked => 'Aula experimental',
            self::Past => 'Já passou',
        };
    }

    /**
     * Whether a click in the calendar can switch the slot between free and blocked.
     */
    public function isToggleable(): bool
    {
        return in_array($this, [self::Free, self::Blocked], true);
    }
}
