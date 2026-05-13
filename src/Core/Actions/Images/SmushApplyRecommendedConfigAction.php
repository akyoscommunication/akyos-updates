<?php

namespace AkyosUpdates\Core\Actions;

use Smush\Core\Settings;

final class SmushApplyRecommendedConfigAction implements ActionInterface
{
    public function getId(): string
    {
        return 'images.smush_apply_recommended_config';
    }

    public function run(array $payload = []): ActionResult
    {
        if (! class_exists('\Smush\Core\Settings')) {
            return ActionResult::failure('API Smush indisponible.');
        }

        $settings = Settings::get_instance();

        $settings->set('lazy_load', true);
        $settings->set('webp_mod', true);
        $settings->set('avif_mod', true);
        $settings->set('auto', true);
        $settings->set('strip_exif', true);
        $settings->set('image_dimensions', true);

        $lazyLoad = (bool) $settings->is_lazyload_active();
        $webp = (bool) $settings->is_webp_module_active();
        $avif = (bool) $settings->is_avif_module_active();
        $autoCompression = (bool) $settings->is_automatic_compression_active();
        $stripExif = (bool) $settings->is_module_active('strip_exif');
        $addDimensions = (bool) $settings->should_add_missing_dimensions();
        $nextGenEnabled = $webp || $avif || (bool) $settings->is_cdn_next_gen_conversion_active();

        $missing = [];
        if (! $lazyLoad) {
            $missing[] = 'Lazy Load désactivé';
        }
        if (! $nextGenEnabled) {
            $missing[] = 'Formats next-gen désactivés (WebP/AVIF)';
        }
        if (! $autoCompression) {
            $missing[] = 'Compression automatique désactivée';
        }
        if (! $stripExif) {
            $missing[] = 'Suppression des métadonnées EXIF désactivée';
        }
        if (! $addDimensions) {
            $missing[] = 'Ajout automatique des dimensions d’image désactivé';
        }

        return ActionResult::success(
            $missing === []
                ? 'Réglages Smush recommandés activés.'
                : 'Réglages Smush appliqués partiellement selon les modules disponibles.',
            [
            'lazyLoad' => $lazyLoad,
            'nextGenEnabled' => $nextGenEnabled,
            'webp' => $webp,
            'avif' => $avif,
            'autoCompression' => $autoCompression,
            'stripExif' => $stripExif,
            'addDimensions' => $addDimensions,
            'missing' => $missing,
            ]
        );
    }
}
