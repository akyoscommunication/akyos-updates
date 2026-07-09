<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\DefenderService;

final class DefenderEnableDisableTrackbacksAction implements ActionInterface
{
    public function getId(): string
    {
        return 'security.defender_enable_disable_trackbacks';
    }

    public function run(array $payload = []): ActionResult
    {
        $out = DefenderService::enableDisableTrackbacks();

        return ($out['success'] ?? false)
            ? ActionResult::success((string) $out['message'], $out)
            : ActionResult::failure((string) ($out['message'] ?? 'Échec désactivation trackbacks.'), $out);
    }
}
