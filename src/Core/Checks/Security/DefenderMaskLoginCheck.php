<?php

namespace AkyosUpdates\Core\Checks\Security;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\DefenderService;

final class DefenderMaskLoginCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'security.defender_mask_login';
    }

    public function getCategory(): string
    {
        return 'Sécurité';
    }

    public function getTitle(): string
    {
        return 'Defender Mask Login';
    }
    public function getSuccessMessage(): string
    {
        return 'Configuration Defender Mask Login mise à jour.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $state = DefenderService::getMaskLoginState();
        $canUse = DefenderService::isPluginActive() && DefenderService::canUseApi();

        if (!$canUse) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Mask Login non vérifiable tant que Defender n\'est pas actif.',
                false,
                null,
                $state
            );
        }

        $isConfigured = (bool) $state['active'] && trim((string) $state['maskUrl']) !== '';

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            $isConfigured ? 'ok' : 'warn',
            $isConfigured ? 'success' : 'warning',
            $isConfigured
            ? sprintf('Mask Login actif sur "%s".', (string) $state['maskUrl'])
            : 'Mask Login non configuré ou inactif.',
            true,
            'security.defender_save_mask_login',
            $state
        );
    }
}
