<?php

namespace AkyosUpdates\Core\Actions;

use Smush\Core\Settings;

final class SmushApplyNextGenFormatAction implements ActionInterface
{
    public function getId(): string
    {
        return 'images.smush_apply_nextgen_config';
    }

    public function run(array $payload = []): ActionResult
    {
        if (! class_exists('\Smush\Core\Settings')) {
            return ActionResult::failure('API Smush next-gen indisponible.');
        }

        $enabled = (bool) ($payload['nextGenEnabled'] ?? false);
        $format = (string) ($payload['activeFormat'] ?? 'webp');
        if (! in_array($format, ['webp', 'avif'], true)) {
            $format = 'webp';
        }

        $settings = Settings::get_instance();
        $settings->set('webp_mod', $enabled && $format === 'webp');
        $settings->set('avif_mod', $enabled && $format === 'avif');

        $webp = (bool) $settings->is_webp_module_active();
        $avif = (bool) $settings->is_avif_module_active();
        $nextGenEnabled = $webp || $avif || (bool) $settings->is_cdn_next_gen_conversion_active();
        $activeFormat = $avif ? 'avif' : 'webp';

        return ActionResult::success(
            $nextGenEnabled
                ? sprintf('Next-gen configuré en %s.', strtoupper($activeFormat))
                : 'Next-gen désactivé.',
            [
            'nextGenEnabled' => $nextGenEnabled,
            'activeFormat' => $activeFormat,
            'availableFormats' => ['webp', 'avif'],
            ]
        );
    }
}
