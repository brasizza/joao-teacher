<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TestimonialControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_renders_the_page_in_the_primary_color_chosen_by_the_teacher(): void
    {
        SiteSetting::factory()->create(['primary_color' => '#7f1d1d']);

        $response = $this->get(route('testimonials.create'));

        $response->assertSee('--color-ink: #7f1d1d;', escape: false);
    }
}
