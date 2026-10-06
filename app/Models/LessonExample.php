<?php

namespace App\Models;

use App\Models\Concerns\CleansUpStoredFiles;
use App\Models\Concerns\IsOrderedContent;
use Database\Factories\LessonExampleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['title', 'description', 'image_path', 'youtube_url', 'sort_order', 'is_active'])]
class LessonExample extends Model
{
    /** @use HasFactory<LessonExampleFactory> */
    use CleansUpStoredFiles, HasFactory, IsOrderedContent;

    /**
     * Extract the 11-character video ID from the common YouTube URL formats
     * (watch, youtu.be, shorts, embed and live links).
     */
    public static function parseYoutubeVideoId(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $pattern = '~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/)|youtu\.be/)([A-Za-z0-9_-]{11})~';

        return preg_match($pattern, $url, $matches) === 1 ? $matches[1] : null;
    }

    public function imageUrl(): ?string
    {
        return filled($this->image_path) ? Storage::disk('public')->url($this->image_path) : null;
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function youtubeVideoId(): Attribute
    {
        return Attribute::get(fn (): ?string => static::parseYoutubeVideoId($this->youtube_url));
    }

    /**
     * @return list<string>
     */
    protected function storedFileAttributes(): array
    {
        return ['image_path'];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
