<?php

namespace Tests\Feature\Models\Concerns;

use App\Models\Testimonial;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CleansUpStoredFilesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_replacing_an_image_deletes_the_previous_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('testimonials/old.jpg', 'old');
        Storage::disk('public')->put('testimonials/new.jpg', 'new');
        $testimonial = Testimonial::factory()->create(['photo_path' => 'testimonials/old.jpg']);

        $testimonial->update(['photo_path' => 'testimonials/new.jpg']);

        Storage::disk('public')->assertMissing('testimonials/old.jpg');
        Storage::disk('public')->assertExists('testimonials/new.jpg');
    }

    public function test_deleting_a_record_deletes_its_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('testimonials/photo.jpg', 'photo');
        $testimonial = Testimonial::factory()->create(['photo_path' => 'testimonials/photo.jpg']);

        $testimonial->delete();

        Storage::disk('public')->assertMissing('testimonials/photo.jpg');
    }
}
