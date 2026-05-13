<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\BrandaService;

final class BrandaSaveSmtpAction implements ActionInterface
{
    public function getId(): string
    {
        return 'backoffice.branda_save_smtp';
    }

    public function run(array $payload = []): ActionResult
    {
        $result = BrandaService::saveSmtp($payload);
        $success = (bool) ($result['success'] ?? false);
        $message = (string) ($result['message'] ?? '');
        unset($result['success'], $result['message']);

        if (! $success) {
            return ActionResult::failure($message, $result);
        }

        $state = BrandaService::getSmtpState();
        return ActionResult::success($message, array_merge($result, $state));
    }
}
