<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Service\PluginService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class PluginController
{
    public function register(): void
    {
        register_rest_route('akyos-updates/v1', '/plugins/activate', [
            'methods' => 'POST',
            'callback' => [$this, 'activate'],
            'permission_callback' => [$this, 'canActivatePlugins'],
            'args' => [
                'plugin' => [
                    'required' => true,
                    'type' => 'string',
                ],
            ],
        ]);
    }

    public function canActivatePlugins(): bool
    {
        return current_user_can('activate_plugins');
    }

    public function activate(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $plugin = (string) $request->get_param('plugin');
        $plugin = str_replace(["\0"], '', $plugin);
        if (str_contains($plugin, '..')) {
            return new WP_Error('akyos_updates_invalid_plugin', 'Identifiant de plugin invalide.', ['status' => 400]);
        }

        if ($plugin === '' || ! preg_match('#^[a-zA-Z0-9_-]+/[a-zA-Z0-9_.-]+\.php$#', $plugin)) {
            return new WP_Error('akyos_updates_invalid_plugin', 'Identifiant de plugin invalide.', ['status' => 400]);
        }

        if (! PluginService::isPluginInstalled($plugin)) {
            return new WP_Error('akyos_updates_plugin_missing', 'Plugin introuvable sur le disque.', ['status' => 404]);
        }

        if (PluginService::isPluginActiveFile($plugin)) {
            return new WP_REST_Response([
                'success' => true,
                'alreadyActive' => true,
                'pluginPresence' => PluginService::capture(),
            ]);
        }

        $activated = activate_plugin($plugin, '', false, true);

        if (is_wp_error($activated)) {
            return new WP_Error(
                'akyos_updates_activate_failed',
                $activated->get_error_message(),
                ['status' => 500]
            );
        }

        return new WP_REST_Response([
            'success' => true,
            'pluginPresence' => PluginService::capture(),
        ]);
    }
}
