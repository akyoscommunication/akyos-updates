<?php

namespace AkyosUpdates\Core\Checks\Images;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use Smush\Core\Settings;

final class SmushConfigurationCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'images.smush_config';
    }

    public function getCategory(): string
    {
        return 'Images';
    }

    public function getTitle(): string
    {
        return 'Configuration Smush';
    }
    public function getSuccessMessage(): string
    {
        return 'Configuration Smush mise à jour.';
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
                'Smush détecté mais API configuration indisponible.',
                false,
                null,
                []
            );
        }

        $settings = Settings::get_instance();

        $lazyLoad = (bool) $settings->is_lazyload_active();
        $autoCompression = (bool) $settings->is_automatic_compression_active();
        $stripExif = (bool) $settings->is_module_active('strip_exif');
        $addDimensions = (bool) $settings->should_add_missing_dimensions();

        $missing = [];
        if (! $lazyLoad) {
            $missing[] = 'Lazy Load désactivé';
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

        if ($missing !== []) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                sprintf('%d réglage(s) Smush à activer.', count($missing)),
                true,
                'images.smush_apply_recommended_config',
                [
                    'lazyLoad' => $lazyLoad,
                    'autoCompression' => $autoCompression,
                    'stripExif' => $stripExif,
                    'addDimensions' => $addDimensions,
                    'missing' => $missing,
                ]
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'ok',
            'success',
            'Configuration Smush optimisée.',
            false,
            null,
            [
                'lazyLoad' => $lazyLoad,
                'autoCompression' => $autoCompression,
                'stripExif' => $stripExif,
                'addDimensions' => $addDimensions,
                'missing' => [],
            ]
        );
    }
}
