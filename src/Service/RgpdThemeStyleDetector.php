<?php

namespace AkyosUpdates\Service;

/**
 * Couleurs et police du thème pour le bandeau tarteaucitron (« Personnaliser le bandeau ») : palette ACF des thèmes
 * Akyos (color_palettes / color_palette), options du thème Salient (salient_redux), theme mods, theme.json, puis
 * variables CSS du thème. Renvoie seulement les
 * champs trouvés, prêts pour RgpdSettingsService::save().
 */
final class RgpdThemeStyleDetector
{
    /**
     * @return array<string, string>
     */
    public function detect(): array
    {
        $palettes = [];
        if (function_exists('get_field')) {
            foreach (['color_palettes', 'color_palette'] as $group) {
                $value = get_field($group, 'option');
                if (is_array($value)) {
                    $palettes[] = $value;
                }
            }
        }
        $css = [];
        // Salient (ThemeForest) : couleur d'accent et couleur n° 1 des options du thème, police du texte.
        $salient = get_option('salient_redux');
        if (is_array($salient)) {
            $palettes[] = ['primary' => (string) ($salient['accent-color'] ?? ''), 'secondary' => (string) ($salient['extra-color-1'] ?? '')];
            $font = is_array($salient['body_font_family'] ?? null) ? (string) ($salient['body_font_family']['font-family'] ?? '') : '';
            if ('' !== trim($font) && '-' !== trim($font)) {
                $css[] = 'font-family: ' . $font . ';';
            }
        }
        $mods = [];
        foreach (['primary_color', 'accent_color', 'theme_color'] as $mod) {
            $mods[] = (string) get_theme_mod($mod, '');
        }
        $themeJson = null;
        $jsonFile = get_theme_file_path('theme.json');
        if (is_readable($jsonFile)) {
            $decoded = json_decode((string) file_get_contents($jsonFile), true);
            $themeJson = is_array($decoded) ? $decoded : null;
        }
        $files = glob(get_stylesheet_directory() . '/{style.css,public/css/*.css,dist/*.css,assets/css/*.css}', GLOB_BRACE) ?: [];
        foreach (array_slice($files, 0, 8) as $file) {
            $css[] = (string) file_get_contents($file);
        }

        return self::fromSources($palettes, $mods, $themeJson, $css);
    }

    /**
     * @param list<array<string, mixed>> $palettes groupes ACF de couleurs
     * @param list<string>               $mods     theme mods de couleur, dans l'ordre de priorité
     * @param array<string, mixed>|null  $themeJson
     * @param list<string>               $css      contenus des feuilles du thème
     *
     * @return array<string, string>
     */
    public static function fromSources(array $palettes, array $mods, ?array $themeJson, array $css): array
    {
        $primary = $light = $text = $muted = $surface = null;
        $font = '';
        foreach ($palettes as $palette) {
            $primary ??= self::pick($palette, ['primary', 'couleur_principale']);
            $light ??= self::pick($palette, ['secondary', 'secondaire', 'primary_light']);
            $text ??= self::pick($palette, ['text', 'texte', 'text-primary']);
            $muted ??= self::pick($palette, ['text-secondary', 'text_muted']);
            $surface ??= self::pick($palette, ['background_body', 'background', 'surface']);
        }
        foreach ($mods as $mod) {
            $primary ??= self::hex($mod);
        }
        foreach ((array) ($themeJson['settings']['color']['palette'] ?? []) as $swatch) {
            $slug = strtolower((string) ($swatch['slug'] ?? ''));
            $color = self::hex((string) ($swatch['color'] ?? ''));
            if (null === $color) {
                continue;
            }
            if (null === $primary && str_contains($slug, 'primary')) {
                $primary = $color;
            }
            if (null === $text && in_array($slug, ['text', 'foreground', 'contrast'], true)) {
                $text = $color;
            }
            if (null === $surface && in_array($slug, ['background', 'base', 'surface'], true)) {
                $surface = $color;
            }
        }
        foreach ($css as $sheet) {
            if (null === $primary && preg_match('/--(?:color-)?primary(?:-color)?:\s*(#[0-9A-Fa-f]{6})/', $sheet, $match)) {
                $primary = self::hex($match[1]);
            }
            if (null === $text && preg_match('/--(?:color-)?(?:text|foreground):\s*(#[0-9A-Fa-f]{6})/', $sheet, $match)) {
                $text = self::hex($match[1]);
            }
            if ('' === $font && preg_match('/font-family:\s*([^;}{]+)/', $sheet, $match)) {
                $font = trim(str_replace(['"', "'"], '', $match[1]));
            }
        }

        return array_filter([
            'theme_primary' => $primary,
            'theme_primary_light' => $light,
            'theme_text' => $text,
            'theme_text_muted' => $muted,
            'theme_surface' => $surface,
            'theme_font' => $font,
        ], static fn (?string $value): bool => null !== $value && '' !== $value);
    }

    /**
     * @param array<string, mixed> $palette
     * @param list<string>         $keys
     */
    private static function pick(array $palette, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $palette[$key] ?? null;
            $hex = self::hex(is_array($value) ? (string) ($value['hex'] ?? '') : (string) $value);
            if (null !== $hex) {
                return $hex;
            }
        }

        return null;
    }

    public static function hex(string $value): ?string
    {
        $value = ltrim(trim($value), '#');

        return 1 === preg_match('/^[0-9A-Fa-f]{6}$/', $value) ? '#' . strtoupper($value) : null;
    }
}
