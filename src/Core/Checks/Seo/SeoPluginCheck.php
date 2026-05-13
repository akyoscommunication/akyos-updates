<?php

namespace AkyosUpdates\Core\Checks\Seo;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\SeoService;

final class SeoPluginCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'seo.plugin';
    }

    public function getCategory(): string
    {
        return 'SEO';
    }

    public function getTitle(): string
    {
        return 'Plugin SEO détecté';
    }
    public function getSuccessMessage(): string
    {
        return 'Point validé après action corrective.';
    }

    public function run(SiteContext $context): CheckResult
    {
        $provider = SeoService::detectProvider();
        $isDetected = $provider['key'] !== SeoService::PROVIDER_NONE;

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            $isDetected ? 'ok' : 'warn',
            $isDetected ? 'success' : 'warning',
            $isDetected ? sprintf('Plugin SEO actif : %s', $provider['label']) : 'Aucun plugin SEO actif trouvé.',
            false,
            null,
            ['provider' => $provider]
        );
    }
}
