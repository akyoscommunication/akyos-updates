<?php

namespace AkyosUpdates\Service;

final class SmushService
{
    public static function isPluginActive(): bool
    {
        return PluginService::isAnyPluginActive([
            'wp-smush-pro/wp-smush.php',
            'wp-smushit/wp-smush.php',
        ]);
    }

    public static function getInstalledSmushVersion(): string
    {
        if (defined('WP_SMUSH_VERSION') && is_string(WP_SMUSH_VERSION) && WP_SMUSH_VERSION !== '') {
            return WP_SMUSH_VERSION;
        }

        foreach (['wp-smushit/wp-smush.php', 'wp-smush-pro/wp-smush.php'] as $relative) {
            $path = WP_PLUGIN_DIR . '/' . $relative;
            if (!is_readable($path)) {
                continue;
            }

            $data = get_file_data($path, ['Version' => 'Version'], 'plugin');
            $v = isset($data['Version']) ? trim((string) $data['Version']) : '';
            if ($v !== '') {
                return $v;
            }
        }

        return '';
    }

    public static function isSmushV4AnalysisSupported(): bool
    {
        if (!self::isPluginActive()) {
            return false;
        }

        $v = self::getInstalledSmushVersion();
        if ($v !== '') {
            return version_compare($v, '4.0.0', '>=');
        }

        return class_exists('\Smush\Core\Settings')
            && class_exists('\Smush\Core\Stats\Global_Stats');
    }

    public static function dashboardAdminUrl(): string
    {
        if (class_exists('\Smush\Core\Helper')) {
            return \Smush\Core\Helper::get_page_url('smush');
        }

        if (is_multisite() && is_network_admin()) {
            return network_admin_url('admin.php?page=smush');
        }

        return admin_url('admin.php?page=smush');
    }
}
