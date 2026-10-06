<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;

/**
 * Shared protection for the public forms: a honeypot field, a minimum time
 * to fill the form and an hourly limit of submissions per IP address.
 */
trait ProtectsAgainstSpam
{
    /**
     * Humans need at least this long to fill a form; faster posts are bots.
     */
    public const int MinimumSecondsToFill = 3;

    /**
     * Honeypot field: hidden from people, filled in by spam bots.
     */
    public string $website = '';

    #[Locked]
    public int $renderedAt = 0;

    public function mountProtectsAgainstSpam(): void
    {
        $this->renderedAt = now()->getTimestamp();
    }

    protected function isLikelySpam(): bool
    {
        return filled($this->website) || now()->getTimestamp() - $this->renderedAt < self::MinimumSecondsToFill;
    }

    /**
     * Add a form error and return true when this visitor already used up
     * the submissions allowed for the hour.
     */
    protected function exceedsHourlyLimit(string $action, int $maxAttempts): bool
    {
        $key = $this->rateLimitKey($action);

        if (! RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return false;
        }

        $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
        $this->addError('form', "Muitas tentativas seguidas. Tente novamente em {$minutes} minutos.");

        return true;
    }

    protected function countSubmission(string $action): void
    {
        RateLimiter::hit($this->rateLimitKey($action), 3600);
    }

    private function rateLimitKey(string $action): string
    {
        return $action.':'.request()->ip();
    }
}
