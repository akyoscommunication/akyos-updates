<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\DefenderService;

final class DefenderEnableAntibotGlobalFirewallAction implements ActionInterface
{
    public function getId(): string
    {
        return 'security.defender_enable_antibot_global_firewall';
    }

    public function run(array $payload = []): ActionResult
    {
        $out = DefenderService::enableAntibotGlobalFirewall();

        return ($out['success'] ?? false)
            ? ActionResult::success((string) $out['message'], $out)
            : ActionResult::failure((string) ($out['message'] ?? 'Échec activation AntiBot.'), $out);
    }
}
