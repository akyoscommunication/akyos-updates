<?php

namespace AkyosUpdates\Core\Checks\Images;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use Smush\Core\Settings;

final class SmushImageSizingCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'images.smush_image_sizing';
    }

    public function getCategory(): string
    {
        return 'Images';
    }

    public function getTitle(): string
    {
        return 'Image sizing (Smush)';
    }
    public function getSuccessMessage(): string
    {
        return 'Réglages Image sizing Smush mis à jour.';
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

        $payload = [
            'autoResizing' => $autoResizing,
            'imageDimensions' => $imageDimensions,
            'lazyLoad' => $lazyLoad,
            'missing' => $missing,
        ];

        if ($missing !== []) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                sprintf('%d option(s) Image sizing à activer dans Smush.', count($missing)),
                true,
                'images.smush_apply_image_sizing',
                $payload
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'ok',
            'success',
            'Automatic Resizing et Set image dimensions sont actifs.',
            false,
            null,
            $payload
        );
    }
}
