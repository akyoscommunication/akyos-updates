<?php

namespace AkyosUpdates\Core\Checks\Seo;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\SeoService;

final class SeoSitemapCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'seo.sitemap';
    }

    public function getCategory(): string
    {
        return 'SEO';
    }

    public function getTitle(): string
    {
        return 'Sitemap XML';
    }
    public function getSuccessMessage(): string
    {
        return 'Point validé après action corrective.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $sitemapUrl = SeoService::findSitemapUrl();
        $hasSitemap = is_string($sitemapUrl) && $sitemapUrl !== '';

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            $hasSitemap ? 'ok' : 'fail',
            $hasSitemap ? 'success' : 'high',
            $hasSitemap ? 'Sitemap trouvé.' : 'Aucun sitemap accessible détecté.',
            false,
            null,
            ['sitemapUrl' => $sitemapUrl]
        );
    }
}
