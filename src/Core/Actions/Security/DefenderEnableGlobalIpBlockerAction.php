<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\DefenderService;

final class DefenderEnableGlobalIpBlockerAction implements ActionInterface
{
    public function getId(): string
    {
        return 'security.defender_enable_global_ip_blocker';
    }

    public function run(array $payload = []): ActionResult
    {
        $out = DefenderService::enableGlobalIpBlocker();
        $success = (bool) ($out['success'] ?? false);
        $message = (string) ($out['message'] ?? '');
        unset($out['success'], $out['message']);

        if ($success) {
            return ActionResult::success($message, $out);
        }

        return ActionResult::failure($message, $out);
    }
}
