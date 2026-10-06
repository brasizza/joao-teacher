<?php

namespace App\Models;

use App\Models\Concerns\CleansUpStoredFiles;
use App\Models\Concerns\IsOrderedContent;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['student_name', 'student_context', 'content', 'photo_path', 'submitted_at', 'sort_order', 'is_active'])]
class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use CleansUpStoredFiles, HasFactory, IsOrderedContent;

    public function photoUrl(): ?string
    {
        return filled($this->photo_path) ? Storage::disk('public')->url($this->photo_path) : null;
    }

    public function initials(): string
    {
        return Str::of($this->student_name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $word): string => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');
    }

    /**
     * Testimonials sent by students through the public form that the
     * teacher has not published yet.
     */
    #[Scope]
    protected function awaitingReview(Builder $query): Builder
    {
        return $query->where('is_active', false)->whereNotNull('submitted_at');
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
            'submitted_at' => 'datetime',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
