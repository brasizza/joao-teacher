<?php

namespace Tests\Unit\Services;

use App\Services\SitePalette;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SitePaletteTest extends TestCase
{
    /**
     * @return array<string, array{0: string|null}>
     */
    public static function invalidColors(): array
    {
        return [
            'nothing chosen' => [null],
            'empty' => [''],
            'color name' => ['navy'],
            'missing a digit' => ['#1e2a4'],
            'with transparency' => ['#1e2a44cc'],
            'css injection' => ['#1e2a44; } body { display: none'],
        ];
    }

    #[DataProvider('invalidColors')]
    public function test_falls_back_to_the_original_navy_without_a_valid_color(?string $color): void
    {
        $palette = new SitePalette($color);

        $this->assertSame('#1e2a44', $palette->primaryColor);
        $this->assertFalse($palette->isCustomized());
    }

    public function test_normalizes_the_chosen_color_to_lowercase(): void
    {
        $palette = new SitePalette(' #14532D ');

        $this->assertSame('#14532d', $palette->primaryColor);
        $this->assertTrue($palette->isCustomized());
    }

    public function test_reproduces_the_shades_the_site_was_designed_with_from_the_original_navy(): void
    {
        $palette = new SitePalette('#1e2a44');

        $this->assertSame([
            '--color-ink' => '#1e2a44',
            '--color-ink-deep' => '#141d31',
            '--color-ink-soft' => '#4a5468',
        ], $palette->cssVariables());
    }

    public function test_derives_a_darker_shade_and_a_muted_tone_of_the_chosen_hue(): void
    {
        $palette = new SitePalette('#14532d');

        $this->assertSame([
            '--color-ink' => '#14532d',
            '--color-ink-deep' => '#0c3d1f',
            '--color-ink-soft' => '#435b49',
        ], $palette->cssVariables());
    }

    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function colorsAndLegibility(): array
    {
        return [
            'original navy' => ['#1e2a44', true],
            'black' => ['#000000', true],
            'dark green' => ['#14532d', true],
            'brown, just above the minimum' => ['#713f12', true],
            'burnt orange, just below the minimum' => ['#9a3412', false],
            'royal blue' => ['#1d4ed8', false],
            'bright green' => ['#22c55e', false],
            'white' => ['#ffffff', false],
        ];
    }

    #[DataProvider('colorsAndLegibility')]
    public function test_accepts_only_colors_dark_enough_to_keep_the_texts_legible(string $color, bool $isLegible): void
    {
        $palette = new SitePalette($color);

        $this->assertSame($isLegible, $palette->hasLegibleContrast());
    }
}
