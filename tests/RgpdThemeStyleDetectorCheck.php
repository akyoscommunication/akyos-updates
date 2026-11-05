<?php

/**
 * php tests/RgpdThemeStyleDetectorCheck.php
 */
declare(strict_types=1);

require dirname(__DIR__) . '/src/Service/RgpdThemeStyleDetector.php';

use AkyosUpdates\Service\RgpdThemeStyleDetector;

function assert_same(mixed $expected, mixed $actual, string $msg): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, "FAIL: {$msg}\n" . var_export($actual, true) . "\n");
        exit(1);
    }
}

// Palette ACF des thèmes Akyos d'abord, puis le reste ne remplace rien.
assert_same(
    ['theme_primary' => '#8B5A2B', 'theme_primary_light' => '#D9C4A8', 'theme_text' => '#222222', 'theme_font' => 'Lato, sans-serif'],
    RgpdThemeStyleDetector::fromSources(
        [['primary' => '8b5a2b', 'secondary' => ['hex' => '#d9c4a8'], 'text' => '#222222']],
        ['#FF0000'],
        ['settings' => ['color' => ['palette' => [['slug' => 'primary', 'color' => '#00FF00']]]]],
        [':root{--primary:#0000FF} body{font-family: "Lato", sans-serif;}']
    ),
    'ACF prioritaire'
);
// Sans ACF : theme mod, puis theme.json pour le texte et le fond.
assert_same(
    ['theme_primary' => '#123456', 'theme_text' => '#111111', 'theme_surface' => '#FAFAFA'],
    RgpdThemeStyleDetector::fromSources([], ['', '#123456'], ['settings' => ['color' => ['palette' => [['slug' => 'foreground', 'color' => '#111111'], ['slug' => 'background', 'color' => '#fafafa']]]]], []),
    'theme mods et theme.json'
);
// Variables CSS en dernier recours ; valeurs invalides ignorées.
assert_same(['theme_primary' => '#ABCDEF'], RgpdThemeStyleDetector::fromSources([['primary' => 'rouge']], ['nope'], null, ['--color-primary: #abcdef;']), 'CSS');
assert_same([], RgpdThemeStyleDetector::fromSources([], [], null, []), 'rien trouvé');

echo "OK\n";
