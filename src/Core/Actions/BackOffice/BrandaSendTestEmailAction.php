<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\BrandaService;

final class BrandaSendTestEmailAction implements ActionInterface
{
    public function getId(): string
    {
        return 'backoffice.branda_send_test_email';
    }

    public function run(array $payload = []): ActionResult
    {
        $out = BrandaService::sendTestEmail((string) ($payload['to'] ?? ''));
        $success = (bool) ($out['success'] ?? false);
        $message = (string) ($out['message'] ?? '');
        unset($out['success'], $out['message']);
        if ($success) {
            return ActionResult::success($message, $out);
        }

        return ActionResult::failure($message, $out);
    }
}
