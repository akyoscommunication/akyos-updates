<?php

namespace AkyosUpdates\Core\Checks\Security;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\DefenderService;

final class DefenderPluginCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'security.defender_plugin';
    }

    public function getCategory(): string
    {
        return 'Sécurité';
    }

    public function getTitle(): string
    {
        return 'Plugin Defender';
    }
    public function getSuccessMessage(): string
    {
        return 'Point validé après action corrective.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $installed = DefenderService::isInstalled();
        $active = $installed && DefenderService::isPluginActive();
        $apiReady = $active && DefenderService::canUseApi();

        if (!$installed) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'fail',
                'high',
                'Defender (WPMU) non installé.',
                false,
                null,
                ['installed' => false, 'active' => false, 'apiReady' => false]
            );
        }

        if (!$active) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'fail',
                'high',
                'Defender installé mais inactif.',
                false,
                null,
                ['installed' => true, 'active' => false, 'apiReady' => false]
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            $apiReady ? 'ok' : 'warn',
            $apiReady ? 'success' : 'warning',
            $apiReady ? 'Defender actif et API disponible.' : 'Defender actif, API interne indisponible.',
            false,
            null,
            ['installed' => true, 'active' => true, 'apiReady' => $apiReady]
        );
    }
}
