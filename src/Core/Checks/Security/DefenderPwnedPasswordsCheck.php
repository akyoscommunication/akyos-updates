<?php

namespace AkyosUpdates\Core\Checks\Security;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\DefenderService;

final class DefenderPwnedPasswordsCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'security.defender_pwned_passwords';
    }

    public function getCategory(): string
    {
        return 'Sécurité';
    }

    public function getTitle(): string
    {
        return 'Defender Pwned Passwords';
    }
    public function getSuccessMessage(): string
    {
        return 'Configuration Defender Pwned Passwords mise à jour.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $state = DefenderService::getPwnedPasswordsState();
        $canUse = DefenderService::isPluginActive() && DefenderService::canUseApi();

        if (!$canUse) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Pwned Passwords non vérifiable tant que Defender n\'est pas actif.',
                false,
                null,
                $state
            );
        }

        $active = (bool) $state['active'];

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            $active ? 'ok' : 'warn',
            $active ? 'success' : 'warning',
            $active ? 'Pwned Passwords est actif.' : 'Pwned Passwords n\'est pas actif.',
            !$active,
            $active ? null : 'security.defender_enable_pwned_passwords',
            $state
        );
    }
}
