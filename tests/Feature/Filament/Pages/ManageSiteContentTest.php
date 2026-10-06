<?php

namespace Tests\Feature\Filament\Pages;

use App\Filament\Pages\ManageSiteContent;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageSiteContentTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(ManageSiteContent::getUrl());

        $response->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_loads_the_current_settings_into_the_form(): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create(['teacher_name' => 'Teacher Ana', 'slot_interval_minutes' => 50]);

        $component = Livewire::test(ManageSiteContent::class);

        $component->assertSchemaStateSet(['teacher_name' => 'Teacher Ana', 'slot_interval_minutes' => 50], 'form');
    }

    public function test_saving_updates_the_site_settings(): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create();

        $component = Livewire::test(ManageSiteContent::class)
            ->fillForm([
                'teacher_name' => 'Teacher Ana',
                'whatsapp_number' => '+55 (21) 99876-5432',
                'slot_interval_minutes' => 45,
                'pending_hold_hours' => 48,
            ], 'form')
            ->call('save');

        $component
            ->assertHasNoFormErrors(form: 'form')
            ->assertNotified('Conteúdo salvo');
        $this->assertDatabaseHas('site_settings', [
            'teacher_name' => 'Teacher Ana',
            'whatsapp_number' => '5521998765432',
            'slot_interval_minutes' => 45,
            'pending_hold_hours' => 48,
        ]);
    }

    public function test_rejects_an_incomplete_whatsapp_number_and_a_too_short_interval(): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create(['whatsapp_number' => '5511987654321', 'slot_interval_minutes' => 30]);

        $component = Livewire::test(ManageSiteContent::class)
            ->fillForm(['whatsapp_number' => '98765-4321', 'slot_interval_minutes' => 5], 'form')
            ->call('save');

        $component->assertHasFormErrors(['whatsapp_number', 'slot_interval_minutes'], 'form');
        $this->assertDatabaseHas('site_settings', ['whatsapp_number' => '5511987654321', 'slot_interval_minutes' => 30]);
    }
}
