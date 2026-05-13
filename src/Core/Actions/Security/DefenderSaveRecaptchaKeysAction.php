<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\DefenderService;

final class DefenderSaveRecaptchaKeysAction implements ActionInterface
{
    public function getId(): string
    {
        return 'security.defender_save_recaptcha_keys';
    }

    public function run(array $payload = []): ActionResult
    {
        if (! DefenderService::isPluginActive()) {
            return ActionResult::failure('Defender doit être actif pour enregistrer reCAPTCHA.');
        }

        $siteKey = (string) ($payload['siteKey'] ?? '');
        $secretKey = (string) ($payload['secretKey'] ?? '');
        $out = DefenderService::saveRecaptchaKeys($siteKey, $secretKey);
        $success = (bool) ($out['success'] ?? false);
        $message = (string) ($out['message'] ?? '');
        unset($out['success'], $out['message']);
        if ($success) {
            return ActionResult::success($message, $out);
        }

        return ActionResult::failure($message, $out);
    }
}
