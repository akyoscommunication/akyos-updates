<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\DefenderService;

final class DefenderSaveMaskLoginAction implements ActionInterface
{
    public function getId(): string
    {
        return 'security.defender_save_mask_login';
    }

    public function run(array $payload = []): ActionResult
    {
        if (! DefenderService::isPluginActive()) {
            return ActionResult::failure('Defender doit être actif pour enregistrer Mask Login.');
        }

        $slug = (string) ($payload['maskUrl'] ?? '');
        $out = DefenderService::saveMaskLoginSlug($slug);
        $success = (bool) ($out['success'] ?? false);
        $message = (string) ($out['message'] ?? '');
        unset($out['success'], $out['message']);
        if ($success) {
            return ActionResult::success($message, $out);
        }

        return ActionResult::failure($message, $out);
    }
}
