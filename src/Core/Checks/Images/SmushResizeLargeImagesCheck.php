<?php

namespace AkyosUpdates\Core\Checks\Images;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use Smush\Core\Settings;

final class SmushResizeLargeImagesCheck implements CheckInterface
{
    private const DEFAULT_MAX = 1920;

    public function getId(): string
    {
        return 'images.smush_resize_large';
    }

    public function getCategory(): string
    {
        return 'Images';
    }

    public function getTitle(): string
    {
        return 'Resize large images (Smush)';
    }
    public function getSuccessMessage(): string
    {
        return 'Réglages Resize large images mis à jour.';
    }


    public function run(SiteContext $context): CheckResult
    {
        if (! class_exists('\Smush\Core\Settings')) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Smush détecté mais API réglages indisponible.',
                false,
                null,
                []
            );
        }

        $settings = Settings::get_instance();
        $resizeActive = $settings->is_resize_module_active();
        $sizes = $settings->get_setting('wp-smush-resize_sizes', []);
        $width = isset($sizes['width']) ? (int) $sizes['width'] : 0;
        $height = isset($sizes['height']) ? (int) $sizes['height'] : 0;
        $displayWidth = $width > 0 ? $width : self::DEFAULT_MAX;
        $displayHeight = $height > 0 ? $height : self::DEFAULT_MAX;

        $payload = [
            'resizeActive' => $resizeActive,
            'resizeWidth' => $displayWidth,
            'resizeHeight' => $displayHeight,
            'storedWidth' => $width,
            'storedHeight' => $height,
        ];

        if (! $resizeActive) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Le redimensionnement des grandes images (upload) est désactivé dans Smush.',
                false,
                null,
                $payload
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'ok',
            'success',
            sprintf(
                'Resize large images actif (max %d × %d px).',
                $displayWidth,
                $displayHeight
            ),
            false,
            null,
            $payload
        );
    }
}
