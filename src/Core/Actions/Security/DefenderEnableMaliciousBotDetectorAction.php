<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\DefenderService;

final class DefenderEnableMaliciousBotDetectorAction implements ActionInterface
{
    public function getId(): string
    {
        return 'security.defender_enable_malicious_bot_detector';
    }

    public function run(array $payload = []): ActionResult
    {
        $out = DefenderService::enableMaliciousBotDetector();

        return ($out['success'] ?? false)
            ? ActionResult::success((string) $out['message'], $out)
            : ActionResult::failure((string) ($out['message'] ?? 'Échec activation détection bots.'), $out);
    }
}
