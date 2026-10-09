<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Turns the main color chosen by the teacher into the shades the public
 * site uses in place of the navy blue of resources/css/app.css: the color
 * itself for buttons, headings and dark sections, a darker shade for hovers
 * and the footer, and a muted tone of the same hue for body text.
 *
 * Shades are derived in the OKLab color space, which keeps the hue of the
 * chosen color and, applied to the original navy, reproduces the shades the
 * site was designed with.
 */
class SitePalette
{
    /**
     * Navy blue the site was designed with (--color-ink in app.css).
     */
    public const string DefaultPrimaryColor = '#1e2a44';

    /**
     * WCAG contrast ratio the main color must reach against the paper
     * background. Light text sits on top of it in the dark sections, so this
     * keeps even their semi-transparent texts above the 4.5:1 minimum.
     */
    public const float MinimumContrast = 7.0;

    /**
     * Background of the site (--color-paper in app.css).
     */
    private const string PaperColor = '#f7f4ec';

    /**
     * How much of the main color is kept when mixing it with black for the
     * darker shade.
     */
    private const float DeepShadeRatio = 0.81;

    private const float SoftToneLightness = 0.445;

    private const float SoftToneChromaRatio = 0.7;

    private const float SoftToneMaximumChroma = 0.04;

    /**
     * The chosen color as lowercase #rrggbb, or the navy blue when none
     * (or an invalid one) was chosen.
     */
    public readonly string $primaryColor;

    public function __construct(?string $primaryColor = null)
    {
        $this->primaryColor = self::normalize($primaryColor) ?? self::DefaultPrimaryColor;
    }

    public function isCustomized(): bool
    {
        return $this->primaryColor !== self::DefaultPrimaryColor;
    }

    /**
     * Whether text in the main color stays legible on the paper background,
     * and light text stays legible on the main color.
     */
    public function hasLegibleContrast(): bool
    {
        return $this->contrastWithPaper() >= self::MinimumContrast;
    }

    /**
     * Custom properties that override the theme colors of app.css.
     *
     * @return array{'--color-ink': string, '--color-ink-deep': string, '--color-ink-soft': string}
     */
    public function cssVariables(): array
    {
        [$lightness, $greenRed, $blueYellow] = self::toOklab($this->primaryColor);

        $chroma = hypot($greenRed, $blueYellow);
        $softChroma = min($chroma * self::SoftToneChromaRatio, self::SoftToneMaximumChroma);
        $softChromaScale = $chroma > 0 ? $softChroma / $chroma : 0;

        return [
            '--color-ink' => $this->primaryColor,
            '--color-ink-deep' => self::fromOklab(
                $lightness * self::DeepShadeRatio,
                $greenRed * self::DeepShadeRatio,
                $blueYellow * self::DeepShadeRatio,
            ),
            '--color-ink-soft' => self::fromOklab(
                self::SoftToneLightness,
                $greenRed * $softChromaScale,
                $blueYellow * $softChromaScale,
            ),
        ];
    }

    private function contrastWithPaper(): float
    {
        $lighter = self::relativeLuminance(self::PaperColor);
        $darker = self::relativeLuminance($this->primaryColor);

        if ($darker > $lighter) {
            [$lighter, $darker] = [$darker, $lighter];
        }

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    private static function normalize(?string $color): ?string
    {
        $color = Str::lower(trim((string) $color));

        return preg_match('/^#[0-9a-f]{6}$/', $color) ? $color : null;
    }

    /**
     * @return array{float, float, float}
     */
    private static function toLinearRgb(string $color): array
    {
        return array_map(
            fn (string $channel): float => self::toLinearChannel(hexdec($channel) / 255),
            str_split(ltrim($color, '#'), 2),
        );
    }

    private static function toLinearChannel(float $channel): float
    {
        return $channel <= 0.04045 ? $channel / 12.92 : (($channel + 0.055) / 1.055) ** 2.4;
    }

    private static function toGammaChannel(float $channel): float
    {
        $channel = max(0.0, min(1.0, $channel));

        return $channel <= 0.0031308 ? $channel * 12.92 : 1.055 * $channel ** (1 / 2.4) - 0.055;
    }

    private static function relativeLuminance(string $color): float
    {
        [$red, $green, $blue] = self::toLinearRgb($color);

        return 0.2126 * $red + 0.7152 * $green + 0.0722 * $blue;
    }

    /**
     * Conversion published by Björn Ottosson, the author of OKLab.
     *
     * @return array{float, float, float}
     */
    private static function toOklab(string $color): array
    {
        [$red, $green, $blue] = self::toLinearRgb($color);

        $long = (0.4122214708 * $red + 0.5363325363 * $green + 0.0514459929 * $blue) ** (1 / 3);
        $medium = (0.2119034982 * $red + 0.6806995451 * $green + 0.1073969566 * $blue) ** (1 / 3);
        $short = (0.0883024619 * $red + 0.2817188376 * $green + 0.6299787005 * $blue) ** (1 / 3);

        return [
            0.2104542553 * $long + 0.7936177850 * $medium - 0.0040720468 * $short,
            1.9779984951 * $long - 2.4285922050 * $medium + 0.4505937099 * $short,
            0.0259040371 * $long + 0.7827717662 * $medium - 0.8086757660 * $short,
        ];
    }

    private static function fromOklab(float $lightness, float $greenRed, float $blueYellow): string
    {
        $long = ($lightness + 0.3963377774 * $greenRed + 0.2158037573 * $blueYellow) ** 3;
        $medium = ($lightness - 0.1055613458 * $greenRed - 0.0638541728 * $blueYellow) ** 3;
        $short = ($lightness - 0.0894841775 * $greenRed - 1.2914855480 * $blueYellow) ** 3;

        $channels = [
            4.0767416621 * $long - 3.3077115913 * $medium + 0.2309699292 * $short,
            -1.2684380046 * $long + 2.6097574011 * $medium - 0.3413193965 * $short,
            -0.0041960863 * $long - 0.7034186147 * $medium + 1.7076147010 * $short,
        ];

        return '#'.implode('', array_map(
            fn (float $channel): string => sprintf('%02x', (int) round(self::toGammaChannel($channel) * 255)),
            $channels,
        ));
    }
}
