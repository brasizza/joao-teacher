<?php

namespace Tests\Feature\Models\Concerns;

use App\Models\MethodologyStep;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class IsOrderedContentTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_new_items_are_placed_at_the_end_of_the_list(): void
    {
        MethodologyStep::factory()->create(['sort_order' => 7]);

        $newStep = MethodologyStep::factory()->create();

        $this->assertSame(8, $newStep->sort_order);
    }

    public function test_active_ordered_query_hides_inactive_items_and_follows_the_sort_order(): void
    {
        $second = MethodologyStep::factory()->create(['sort_order' => 2]);
        MethodologyStep::factory()->inactive()->create(['sort_order' => 1]);
        $first = MethodologyStep::factory()->create(['sort_order' => 0]);

        $steps = MethodologyStep::query()->active()->ordered()->get();

        $this->assertSame([$first->id, $second->id], $steps->modelKeys());
    }
}
