<?php

namespace Tests\Feature\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_a_registered_user_can_open_the_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('filament.admin.pages.dashboard'));

        $response->assertOk();
    }

    public function test_the_admin_panel_offers_no_self_registration(): void
    {
        $response = $this->get('/admin/register');

        $response->assertNotFound();
    }
}
