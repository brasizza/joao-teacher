<?php

namespace App\Models;

use App\Models\Concerns\CleansUpStoredFiles;
use App\Services\SitePalette;
use Database\Factories\SiteSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Single-row table holding every editable text, contact detail, color and
 * scheduling rule of the public site.
 *
 * @property array<int, array{value: string, label: string}>|null $highlights
 */
#[Fillable([
    'teacher_name',
    'tagline',
    'headline',
    'subheadline',
    'photo_path',
    'primary_color',
    'highlights',
    'about_title',
    'about_text',
    'methodology_intro',
    'lessons_intro',
    'testimonials_intro',
    'schedule_intro',
    'whatsapp_number',
    'whatsapp_general_message',
    'whatsapp_booking_message',
    'instagram_url',
    'email',
    'seo_title',
    'seo_description',
    'slot_interval_minutes',
    'booking_window_days',
    'minimum_notice_hours',
    'pending_hold_hours',
])]
class SiteSetting extends Model
{
    /** @use HasFactory<SiteSettingFactory> */
    use CleansUpStoredFiles, HasFactory;

    /**
     * Get the site's settings, creating the row with sensible defaults when
     * the database has not been seeded yet.
     */
    public static function current(): self
    {
        return once(fn (): self => static::query()->oldest('id')->firstOrCreate([], static::defaults()));
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'teacher_name' => 'Teacher João',
            'headline' => 'Inglês com método, clareza e confiança.',
            'whatsapp_general_message' => 'Olá! Gostaria de saber mais sobre as aulas de inglês.',
            'whatsapp_booking_message' => 'Olá! Meu nome é {nome} e acabei de solicitar uma aula experimental para {data} às {hora}.',
            'slot_interval_minutes' => 30,
            'booking_window_days' => 14,
            'minimum_notice_hours' => 12,
        ];
    }

    public function photoUrl(): ?string
    {
        return filled($this->photo_path) ? Storage::disk('public')->url($this->photo_path) : null;
    }

    /**
     * First letter of the teacher's name, ignoring titles such as "Teacher".
     */
    public function monogram(): string
    {
        $name = preg_replace('/^(teacher|prof(essor|essora)?\.?)\s+/iu', '', trim($this->teacher_name)) ?: $this->teacher_name;

        return Str::upper(Str::substr($name, 0, 1));
    }

    /**
     * Colors of the public site, based on the main color the teacher chose.
     */
    public function palette(): SitePalette
    {
        return new SitePalette($this->primary_color);
    }

    public function hasWhatsApp(): bool
    {
        return filled($this->whatsapp_number);
    }

    /**
     * @return list<string>
     */
    protected function storedFileAttributes(): array
    {
        return ['photo_path'];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'highlights' => 'array',
            'slot_interval_minutes' => 'integer',
            'booking_window_days' => 'integer',
            'minimum_notice_hours' => 'integer',
            'pending_hold_hours' => 'integer',
        ];
    }
}
