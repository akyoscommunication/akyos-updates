<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\DefenderService;

final class DefenderEnablePreventInfoDisclosureAction implements ActionInterface
{
    public function getId(): string
    {
        return 'security.defender_enable_prevent_info_disclosure';
    }

    public function run(array $payload = []): ActionResult
    {
        $out = DefenderService::enablePreventInfoDisclosure();

        return ($out['success'] ?? false)
            ? ActionResult::success((string) $out['message'], $out)
            : ActionResult::failure(
                (string) ($out['message'] ?? 'Échec protection info disclosure.'),
                $out,
                isset($out['code']) ? (string) $out['code'] : null
            );
    }
}
