<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Carbon\CarbonInterface;
use Database\Factories\BookingRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A trial-lesson request made from the public schedule. While pending or
 * confirmed it keeps its slot out of the public agenda.
 */
#[Fillable(['name', 'phone', 'starts_at', 'ends_at', 'status', 'notes', 'ip_address'])]
class BookingRequest extends Model
{
    /** @use HasFactory<BookingRequestFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * Keep only digits and assume a Brazilian number (country code 55) when
     * the visitor typed just the area code and the local number.
     */
    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        return in_array(strlen($digits), [10, 11], true) ? '55'.$digits : $digits;
    }

    /**
     * Whether a pending request has outlived the hold period configured by
     * the teacher (a null hold period means it never expires).
     */
    public function isExpired(?int $pendingHoldHours): bool
    {
        return $this->status === BookingStatus::Pending
            && filled($pendingHoldHours)
            && $this->created_at->lt(now()->subHours($pendingHoldHours));
    }

    public function confirm(): void
    {
        $this->forceFill([
            'status' => BookingStatus::Confirmed,
            'confirmed_at' => now(),
            'cancelled_at' => null,
        ])->save();
    }

    public function release(): void
    {
        $this->forceFill([
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
        ])->save();
    }

    /**
     * Requests that currently hold their slot: confirmed ones, and pending
     * ones still inside the hold period.
     */
    #[Scope]
    protected function blocking(Builder $query, ?int $pendingHoldHours): Builder
    {
        return $query->where(function (Builder $query) use ($pendingHoldHours): void {
            $query->where('status', BookingStatus::Confirmed)
                ->orWhere(function (Builder $query) use ($pendingHoldHours): void {
                    $query->where('status', BookingStatus::Pending)
                        ->when($pendingHoldHours, fn (Builder $query, int $hours): Builder => $query->where('created_at', '>=', now()->subHours($hours)));
                });
        });
    }

    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query->where('status', BookingStatus::Pending);
    }

    #[Scope]
    protected function overlapping(Builder $query, CarbonInterface $start, CarbonInterface $end): Builder
    {
        return $query->where('starts_at', '<', $end)->where('ends_at', '>', $start);
    }

    /**
     * @return Attribute<string, string>
     */
    protected function phone(): Attribute
    {
        return Attribute::set(fn (string $value): string => static::normalizePhone($value));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'confirmed_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
            'status' => BookingStatus::class,
        ];
    }
}
