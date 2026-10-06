<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Removes uploaded images from the public disk when they are replaced or
 * when the record is deleted, because Filament never deletes old uploads.
 */
trait CleansUpStoredFiles
{
    /**
     * @return list<string>
     */
    abstract protected function storedFileAttributes(): array;

    public static function bootCleansUpStoredFiles(): void
    {
        static::updated(function (self $model): void {
            foreach ($model->storedFileAttributes() as $attribute) {
                $previousPath = $model->getOriginal($attribute);

                if ($model->wasChanged($attribute) && filled($previousPath)) {
                    Storage::disk('public')->delete($previousPath);
                }
            }
        });

        static::deleted(function (self $model): void {
            $paths = collect($model->storedFileAttributes())
                ->map(fn (string $attribute): ?string => $model->getAttribute($attribute))
                ->filter()
                ->all();

            Storage::disk('public')->delete($paths);
        });
    }
}
