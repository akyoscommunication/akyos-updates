<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\DefenderService;

final class DefenderEnableLoginDurationAction implements ActionInterface
{
    public function getId(): string
    {
        return 'security.defender_enable_login_duration';
    }

    public function run(array $payload = []): ActionResult
    {
        // payload.duration = durée actuelle du check, pas la cible — toujours fixer à la valeur Akyos.
        $days = DefenderService::TARGET_LOGIN_DURATION_DAYS;

        $out = DefenderService::enableLoginDuration($days);

        return ($out['success'] ?? false)
            ? ActionResult::success((string) $out['message'], $out)
            : ActionResult::failure((string) ($out['message'] ?? 'Échec configuration durée de login.'), $out);
    }
}
