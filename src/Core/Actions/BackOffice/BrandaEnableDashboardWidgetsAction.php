<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\BrandaService;

final class BrandaEnableDashboardWidgetsAction implements ActionInterface
{
    public function getId(): string
    {
        return 'backoffice.branda_enable_dashboard_widgets';
    }

    public function run(array $payload = []): ActionResult
    {
        if (! BrandaService::isPluginActive() || ! BrandaService::isBrandaLoaded()) {
            return ActionResult::failure('Branda indisponible.');
        }

        $ok = BrandaService::activateModule(BrandaService::MODULE_DASHBOARD_WIDGETS);
        if (! $ok) {
            return ActionResult::failure('Activation du module Dashboard widgets impossible.');
        }

        $state = BrandaService::getDashboardWidgetsState();
        $count = (int) ($state['availableCount'] ?? 0);
        $message = $count > 0
            ? sprintf('Module Dashboard widgets activé ; %d widget(s) détecté(s) automatiquement.', $count)
            : 'Module Dashboard widgets activé dans Branda.';

        return ActionResult::success($message, $state);
    }
}
