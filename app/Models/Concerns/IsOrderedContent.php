<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shared behaviour for the sortable content blocks shown on the home page
 * (methodology steps, lesson examples and testimonials).
 */
trait IsOrderedContent
{
    /**
     * Append new records to the end of the list, since Filament's reorderable
     * tables never assign a position on create.
     */
    public static function bootIsOrderedContent(): void
    {
        static::creating(function (self $model): void {
            if (blank($model->sort_order)) {
                $model->sort_order = (int) static::query()->max('sort_order') + 1;
            }
        });
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    #[Scope]
    protected function ordered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
