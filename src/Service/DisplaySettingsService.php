<?php

namespace AkyosUpdates\Service;

final class DisplaySettingsService
{
    public const OPTION_KEY = 'akyos_updates_show_maintenance_admin_bar';

    public function showMaintenanceAdminBar(): bool
    {
        return (bool) get_option(self::OPTION_KEY, false);
    }

    public function save(bool $show): bool
    {
        update_option(self::OPTION_KEY, $show, false);

        return $show;
    }

    /** @return array{show_maintenance_admin_bar: bool} */
    public function publicView(): array
    {
        return [
            'show_maintenance_admin_bar' => $this->showMaintenanceAdminBar(),
        ];
    }
}
