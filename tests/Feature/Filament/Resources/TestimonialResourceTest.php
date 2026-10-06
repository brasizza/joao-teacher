<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TestimonialResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_navigation_badge_counts_student_testimonials_awaiting_approval(): void
    {
        Testimonial::factory()->submittedByStudent()->count(2)->create();
        Testimonial::factory()->submittedByStudent()->create(['is_active' => true]);
        Testimonial::factory()->inactive()->create();

        $badge = TestimonialResource::getNavigationBadge();

        $this->assertSame('2', $badge);
    }

    public function test_awaiting_tab_lists_only_unpublished_student_testimonials(): void
    {
        $this->actingAs(User::factory()->create());
        $awaiting = Testimonial::factory()->submittedByStudent()->create();
        $published = Testimonial::factory()->submittedByStudent()->create(['is_active' => true]);
        $hiddenByTeacher = Testimonial::factory()->inactive()->create();

        $component = Livewire::test(ManageTestimonials::class, ['activeTab' => 'awaiting']);

        $component
            ->assertCanSeeTableRecords([$awaiting])
            ->assertCanNotSeeTableRecords([$published, $hiddenByTeacher]);
    }

    public function test_request_action_shows_the_public_testimonial_link(): void
    {
        $this->actingAs(User::factory()->create());

        $component = Livewire::test(ManageTestimonials::class)->mountAction('requestTestimonial');

        $component->assertSchemaStateSet(['link' => route('testimonials.create')]);
    }
}
