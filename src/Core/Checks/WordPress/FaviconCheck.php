<?php

namespace AkyosUpdates\Core\Checks\WordPress;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;

final class FaviconCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'wordpress.favicon';
    }

    public function getCategory(): string
    {
        return 'WordPress';
    }

    public function getTitle(): string
    {
        return 'Favicon';
    }
    public function getSuccessMessage(): string
    {
        return 'Favicon présent';
    }


    public function run(SiteContext $context): CheckResult
    {
        $siteIconId = (int) get_option('site_icon');
        $isValid = false;
        $faviconUrl = null;

        if ($siteIconId > 0) {
            $iconData = wp_get_attachment_image_src($siteIconId, 'full');
            $isValid = is_array($iconData) && ! empty($iconData[0]);
            if ($isValid) {
                $faviconUrl = (string) $iconData[0];
            }
        }

        if ($isValid) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'ok',
                'success',
                'Favicon présent',
                false,
                null,
                [
                    'siteIconId' => $siteIconId,
                    'faviconUrl' => $faviconUrl,
                ]
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'fail',
            'high',
            'Aucun favicon valide.',
            true,
            'wordpress.set_favicon',
            [
                'siteIconId' => $siteIconId,
                'faviconUrl' => null,
            ]
        );
    }
}
