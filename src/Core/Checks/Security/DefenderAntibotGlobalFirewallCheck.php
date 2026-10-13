<?php

namespace AkyosUpdates\Core\Checks\Security;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\DefenderService;

final class DefenderAntibotGlobalFirewallCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'security.defender_antibot_global_firewall';
    }

    public function getCategory(): string
    {
        return 'Sécurité';
    }

    public function getTitle(): string
    {
        return 'Defender — Pare-feu AntiBot global';
    }

    public function getSuccessMessage(): string
    {
        return 'Pare-feu AntiBot global actif.';
    }

    public function run(SiteContext $context): CheckResult
    {
        $state = DefenderService::getAntibotGlobalFirewallState();
        $canUse = DefenderService::isPluginActive() && function_exists('wd_di')
            && class_exists('\WP_Defender\Model\Setting\Antibot_Global_Firewall_Setting');

        if (! $canUse) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Pare-feu AntiBot non vérifiable tant que Defender n\'est pas actif.',
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
                ? 'Pare-feu AntiBot global activé.'
                : 'Pare-feu AntiBot global désactivé.',
            ! $enabled,
            $enabled ? null : 'security.defender_enable_antibot_global_firewall',
            $state
        );
    }
}
