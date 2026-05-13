<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\BrandaService;

final class BrandaHideDashboardWidgetsAction implements ActionInterface
{
    public function getId(): string
    {
        return 'backoffice.branda_hide_dashboard_widgets';
    }

    public function run(array $payload = []): ActionResult
    {
        $result = BrandaService::hideAllDashboardWidgets();
        $success = (bool) ($result['success'] ?? false);
        $message = (string) ($result['message'] ?? '');
        unset($result['success'], $result['message']);

        if (! $success) {
            return ActionResult::failure($message, $result);
        }

        $state = BrandaService::getDashboardWidgetsState();
        return ActionResult::success($message, array_merge($result, $state));
    }
}
