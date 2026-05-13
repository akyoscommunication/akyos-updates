<?php

namespace AkyosUpdates\Core\Actions;

use Smush\Core\Settings;

final class SmushApplyResizeLargeAction implements ActionInterface
{
    private const DEFAULT_MAX = 1920;

    private const MIN_DIMENSION = 1;

    private const MAX_DIMENSION = 9999;

    public function getId(): string
    {
        return 'images.smush_apply_resize_large';
    }

    public function run(array $payload = []): ActionResult
    {
        if (! class_exists('\Smush\Core\Settings')) {
            return ActionResult::failure('API Smush indisponible.');
        }

        $width = isset($payload['width']) ? (int) $payload['width'] : self::DEFAULT_MAX;
        $height = isset($payload['height']) ? (int) $payload['height'] : self::DEFAULT_MAX;

        if ($width < self::MIN_DIMENSION || $width > self::MAX_DIMENSION) {
            return ActionResult::failure(sprintf('Largeur invalide (%d–%d px).', self::MIN_DIMENSION, self::MAX_DIMENSION));
        }

        if ($height < self::MIN_DIMENSION || $height > self::MAX_DIMENSION) {
            return ActionResult::failure(sprintf('Hauteur invalide (%d–%d px).', self::MIN_DIMENSION, self::MAX_DIMENSION));
        }

        $settings = Settings::get_instance();
        $settings->set('resize', true);
        $settings->set_setting('wp-smush-resize_sizes', [
            'width' => $width,
            'height' => $height,
        ]);

        $resizeActive = $settings->is_resize_module_active();

        return ActionResult::success(sprintf('Resize large images appliqué (%d × %d px).', $width, $height), [
            'resizeActive' => $resizeActive,
            'resizeWidth' => $width,
            'resizeHeight' => $height,
            'storedWidth' => $width,
            'storedHeight' => $height,
        ]);
    }
}
