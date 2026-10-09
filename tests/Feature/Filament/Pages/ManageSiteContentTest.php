<?php

namespace Tests\Feature\Filament\Pages;

use App\Filament\Pages\ManageSiteContent;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_saves_the_primary_color_of_the_site(): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create(['primary_color' => null]);

        $component = Livewire::test(ManageSiteContent::class)
            ->fillForm(['primary_color' => '#14532D'], 'form')
            ->call('save');

        $component->assertHasNoFormErrors(form: 'form');
        $this->assertDatabaseHas('site_settings', ['primary_color' => '#14532d']);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function unusablePrimaryColors(): array
    {
        return [
            'too light for the light texts' => ['#22c55e', 'Essa cor é clara demais e deixaria os textos claros do site difíceis de ler. Escolha um tom mais escuro.'],
            'not hexadecimal' => ['verde', 'Informe a cor no formato #RRGGBB, por exemplo #1e2a44.'],
        ];
    }

    #[DataProvider('unusablePrimaryColors')]
    public function test_rejects_an_unusable_primary_color(string $color, string $expectedMessage): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create(['primary_color' => '#14532d']);

        $component = Livewire::test(ManageSiteContent::class)
            ->fillForm(['primary_color' => $color], 'form')
            ->call('save');

        $component->assertHasFormErrors(['primary_color' => $expectedMessage], 'form');
        $this->assertDatabaseHas('site_settings', ['primary_color' => '#14532d']);
    }

    public function test_restoring_the_original_color_clears_the_primary_color(): void
    {
        $this->actingAs(User::factory()->create());
        SiteSetting::factory()->create(['primary_color' => '#14532d']);

        $component = Livewire::test(ManageSiteContent::class)
            ->callAction(TestAction::make('restoreDefaultPrimaryColor')->schemaComponent('primary_color', schema: 'form'))
            ->call('save');

        $component->assertHasNoFormErrors(form: 'form');
        $this->assertDatabaseHas('site_settings', ['primary_color' => null]);
    }
}
