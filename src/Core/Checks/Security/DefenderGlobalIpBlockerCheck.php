<?php

namespace AkyosUpdates\Core\Checks\Security;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\DefenderService;

final class DefenderGlobalIpBlockerCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'security.defender_global_ip_blocker';
    }

    public function getCategory(): string
    {
        return 'Sécurité';
    }

    public function getTitle(): string
    {
        return 'Defender Global IP Blocker';
    }
    public function getSuccessMessage(): string
    {
        return 'Configuration Defender Global IP Blocker mise à jour.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $canRead = DefenderService::isPluginActive() && function_exists('wd_di')
            && class_exists('\WP_Defender\Model\Setting\Global_Ip_Lockout');
        $state = DefenderService::getGlobalIpLockoutState();

        if (!$canRead) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Global IP Blocker non vérifiable tant que Defender n\'est pas actif.',
                false,
                null,
                $state
            );
        }

        $enabled = (bool) ($state['enabled'] ?? false);

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            $enabled ? 'ok' : 'warn',
            $enabled ? 'success' : 'warning',
            $enabled
            ? 'Global IP Blocker (liste centralisée WPMU DEV) est activé.'
            : 'Global IP Blocker est désactivé.',
            !$enabled,
            $enabled ? null : 'security.defender_enable_global_ip_blocker',
            $state
        );
    }
}
