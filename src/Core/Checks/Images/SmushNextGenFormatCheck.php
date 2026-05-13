<?php

namespace AkyosUpdates\Core\Checks\Images;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use Smush\Core\Settings;

final class SmushNextGenFormatCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'images.smush_nextgen_config';
    }

    public function getCategory(): string
    {
        return 'Images';
    }

    public function getTitle(): string
    {
        return 'Configuration Next-Gen Formats (WebP / AVIF)';
    }
    public function getSuccessMessage(): string
    {
        return 'Configuration next-gen mise à jour.';
    }


    public function run(SiteContext $context): CheckResult
    {
        if (!class_exists('\Smush\Core\Settings')) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Smush détecté mais API next-gen indisponible.',
                false,
                null,
                []
            );
        }

        $settings = Settings::get_instance();
        $webp = (bool) $settings->is_webp_module_active();
        $avif = (bool) $settings->is_avif_module_active();
        $nextGenEnabled = $webp || $avif || (bool) $settings->is_cdn_next_gen_conversion_active();
        $activeFormat = $avif ? 'avif' : 'webp';

        if (!$nextGenEnabled) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Format next-gen désactivé.',
                true,
                'images.smush_apply_nextgen_config',
                [
                    'nextGenEnabled' => false,
                    'activeFormat' => 'webp',
                    'availableFormats' => ['webp', 'avif'],
                ]
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'ok',
            'success',
            sprintf('Next-gen format actif: %s.', strtoupper($activeFormat)),
            true,
            'images.smush_apply_nextgen_config',
            [
                'nextGenEnabled' => true,
                'activeFormat' => $activeFormat,
                'availableFormats' => ['webp', 'avif'],
            ]
        );
    }
}
