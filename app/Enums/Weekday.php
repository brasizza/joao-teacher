<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Days of the week numbered like Carbon's `dayOfWeek` (Sunday = 0).
 */
enum Weekday: int implements HasLabel
{
    case Sunday = 0;
    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;

    public function getLabel(): string
    {
        return match ($this) {
            self::Sunday => 'Domingo',
            self::Monday => 'Segunda-feira',
            self::Tuesday => 'Terça-feira',
            self::Wednesday => 'Quarta-feira',
            self::Thursday => 'Quinta-feira',
            self::Friday => 'Sexta-feira',
            self::Saturday => 'Sábado',
        };
    }
}
