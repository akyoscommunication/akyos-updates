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
     * Polices du site (body d'abord) proposées dans le sélecteur « Police » du bandeau, en plus des préréglages :
     * Salient, Astra, kit Elementor, theme.json / styles globaux, puis règles html/body et variables --font-* du CSS.
     *
     * @return list<array{value: string, label: string}>
     */
    public function fontFamilies(): array
    {
        $stacks = [];
        $salient = get_option('salient_redux');
        if (is_array($salient) && is_array($salient['body_font_family'] ?? null)) {
            $stacks[] = (string) ($salient['body_font_family']['font-family'] ?? '');
        }
        $astra = get_option('astra-settings');
        if (is_array($astra)) {
            $stacks[] = (string) ($astra['body-font-family'] ?? '');
        }
        $kit = (int) get_option('elementor_active_kit');
        $kitSettings = $kit > 0 ? get_post_meta($kit, '_elementor_page_settings', true) : null;
        if (is_array($kitSettings)) {
            $typos = (array) ($kitSettings['system_typography'] ?? []);
            // « text » = police du corps dans Elementor : en premier.
            usort($typos, static fn ($a, $b): int => (int) (($b['_id'] ?? '') === 'text') <=> (int) (($a['_id'] ?? '') === 'text'));
            foreach ($typos as $typo) {
                if (is_array($typo)) {
                    $stacks[] = (string) ($typo['typography_font_family'] ?? '');
                }
            }
        }

        $presets = [];
        if (function_exists('wp_get_global_settings')) {
            foreach ((array) wp_get_global_settings(['typography', 'fontFamilies']) as $list) {
                foreach ((array) $list as $preset) {
                    if (is_array($preset) && ! empty($preset['slug']) && ! empty($preset['fontFamily'])) {
                        $presets[(string) $preset['slug']] = (string) $preset['fontFamily'];
                    }
                }
            }
            $body = function_exists('wp_get_global_styles') ? wp_get_global_styles(['typography', 'fontFamily']) : '';
            array_unshift($presets, is_string($body) ? $body : '');
        }

        $css = [];
        $files = glob(get_stylesheet_directory() . '/{style.css,public/css/*.css,dist/*.css,dist/css/*.css,assets/css/*.css,build/*.css}', GLOB_BRACE) ?: [];
        foreach (array_slice($files, 0, 12) as $file) {
            $css[] = (string) file_get_contents($file);
        }

        return self::fontsFromSources($stacks, $presets, $css);
    }

    /**
     * @param list<string>              $stacks  piles venant des options de thème / builder, par priorité
     * @param array<int|string, string> $presets presets theme.json (slug => pile) ; clés numériques = piles directes
     * @param list<string>              $css     contenus des feuilles du thème
     *
     * @return list<array{value: string, label: string}>
     */
    public static function fontsFromSources(array $stacks, array $presets, array $css): array
    {
        $vars = [];
        $bodyStacks = [];
        $fontVars = [];
        foreach ($css as $sheet) {
            $sheet = (string) preg_replace('#/\*.*?\*/#s', '', $sheet);
            if (preg_match_all('/(--[\w-]+)\s*:\s*([^;{}]+)/', $sheet, $m, PREG_SET_ORDER)) {
                foreach ($m as [, $name, $value]) {
                    $vars[$name] ??= trim($value);
                    if (preg_match('/^--(?:[\w-]*-)?font(?:-family)?(?:-[\w-]+)?$/', $name) && ! preg_match('/weight|size|style|feature|variation|settings|mono|icon|line|letter|smoothing/', $name)) {
                        $fontVars[] = trim($value);
                    }
                }
            }
            if (preg_match_all('/(?:^|[{};])\s*([^{};@]*\b(?:html|body)\b[^{};]*)\{([^{}]*)\}/i', $sheet, $m, PREG_SET_ORDER)) {
                foreach ($m as [, , $declarations]) {
                    if (preg_match('/(?:^|;|\s)font-family\s*:\s*([^;}]+)/i', $declarations, $font)) {
                        $bodyStacks[] = trim($font[1]);
                    }
                }
            }
        }

        $resolve = static function (string $stack) use (&$resolve, $vars, $presets): string {
            $stack = trim((string) preg_replace('/\s*!important\s*$/i', '', $stack));
            if (preg_match('/^var:preset\|font-family\|([\w-]+)$/', $stack, $m)) {
                return (string) ($presets[$m[1]] ?? '');
            }
            if (preg_match('/^var\(\s*(--[\w-]+)\s*(?:,\s*(.+))?\)$/', $stack, $m)) {
                if (preg_match('/^--wp--preset--font-family--([\w-]+)$/', $m[1], $preset) && isset($presets[$preset[1]])) {
                    return $resolve($presets[$preset[1]]);
                }
                if (isset($vars[$m[1]]) && $vars[$m[1]] !== $stack) {
                    return $resolve($vars[$m[1]]);
                }

                return isset($m[2]) ? $resolve($m[2]) : '';
            }

            return $stack;
        };

        $out = [];
        foreach ([...$stacks, ...array_values($presets), ...$bodyStacks, ...$fontVars] as $raw) {
            $stack = $resolve((string) $raw);
            $first = trim(explode(',', $stack)[0] ?? '', " \t\"'");
            if ($first === '' || $first === '-' || str_contains($stack, 'var(') || preg_match('/[;{}<>]/', $stack)
                || preg_match('/^(inherit|initial|unset|revert|serif|sans-serif|monospace|cursive|fantasy|system-ui|ui-[\w-]+|-apple-system|BlinkMacSystemFont)$/i', $first)
                || preg_match('/icon|awesome|dashicons|eicons|^fa[-\s]|^fa$/i', $first)) {
                continue;
            }
            // Nom seul (options Salient / Astra / Elementor) : on complète la pile.
            if (! str_contains($stack, ',')) {
                $stack = (str_contains($first, ' ') ? '"' . $first . '"' : $first) . ', sans-serif';
            }
            $key = strtolower($first);
            if (! isset($out[$key]) && strlen($stack) <= 200) {
                $out[$key] = ['value' => $stack, 'label' => $first . ' (thème)'];
            }
        }

        return array_values($out);
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
