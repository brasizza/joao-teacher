<?php

namespace App\Models;

use App\Enums\Weekday;
use Database\Factories\AvailabilityWindowFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A recurring weekly period in which trial lessons can be booked.
 * `start_time` and `end_time` are plain "H:i[:s]" strings.
 */
#[Fillable(['weekday', 'start_time', 'end_time', 'is_active'])]
class AvailabilityWindow extends Model
{
    /** @use HasFactory<AvailabilityWindowFactory> */
    use HasFactory;

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weekday' => Weekday::class,
            'is_active' => 'boolean',
        ];
    }
}
