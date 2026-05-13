<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Core\Checks\Security\DefenderFirewallTranslationsCheck;
use AkyosUpdates\Service\DefenderService;

final class DefenderApplyFirewallTranslationsAction implements ActionInterface
{
    public function getId(): string
    {
        return 'security.defender_apply_firewall_translations';
    }

    public function run(array $payload = []): ActionResult
    {
        $out = DefenderService::applyFirewallFrenchTranslations();
        $success = (bool) ($out['success'] ?? false);
        $message = (string) ($out['message'] ?? '');
        $data = DefenderFirewallTranslationsCheck::summarizeFirewallPayload($out);

        if ($success) {
            return ActionResult::success($message, $data);
        }

        return ActionResult::failure($message, $data);
    }
}
