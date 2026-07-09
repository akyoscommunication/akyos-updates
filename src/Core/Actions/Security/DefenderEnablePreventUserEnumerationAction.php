<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\DefenderService;

final class DefenderEnablePreventUserEnumerationAction implements ActionInterface
{
    public function getId(): string
    {
        return 'security.defender_enable_prevent_user_enumeration';
    }

    public function run(array $payload = []): ActionResult
    {
        $out = DefenderService::enablePreventUserEnumeration();

        return ($out['success'] ?? false)
            ? ActionResult::success((string) $out['message'], $out)
            : ActionResult::failure((string) ($out['message'] ?? 'Échec protection anti-énumération.'), $out);
    }
}
