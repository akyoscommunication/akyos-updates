<?php

namespace AkyosUpdates\Core\Actions;

use Smush\Core\Settings;

final class SmushApplyImageSizingAction implements ActionInterface
{
    public function getId(): string
    {
        return 'images.smush_apply_image_sizing';
    }

    public function run(array $payload = []): ActionResult
    {
        if (! class_exists('\Smush\Core\Settings')) {
            return ActionResult::failure('API Smush indisponible.');
        }

        $settings = Settings::get_instance();

        $settings->set('lazy_load', true);
        $settings->set('auto_resizing', true);
        $settings->set('image_dimensions', true);

        $autoResizing = (bool) $settings->get('auto_resizing');
        $imageDimensions = (bool) $settings->get('image_dimensions');
        $lazyLoad = (bool) $settings->get('lazy_load');

        $missing = [];
        if (! $autoResizing) {
            $missing[] = 'Automatic Resizing désactivé';
        }
        if (! $imageDimensions) {
            $missing[] = 'Set image dimensions désactivé';
        }

        return ActionResult::success(
            $missing === []
                ? 'Image sizing Smush activé (Lazy Load, Automatic Resizing, dimensions).'
                : 'Réglages partiellement appliqués (vérifie l’abonnement Smush / modules disponibles).',
            [
            'autoResizing' => $autoResizing,
            'imageDimensions' => $imageDimensions,
            'lazyLoad' => $lazyLoad,
            'missing' => $missing,
            ]
        );
    }
}
