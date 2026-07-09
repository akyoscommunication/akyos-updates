<?php

namespace AkyosUpdates\Plugin;

use AkyosUpdates\Service\GithubReleaseService;

final class GithubUpdater
{
    private string $basename;

    public function __construct()
    {
        $this->basename = plugin_basename(AKYOS_UPDATES_PLUGIN_FILE);
    }

    public function register(): void
    {
        add_filter('pre_set_site_transient_update_plugins', [$this, 'modifyTransient']);
        add_filter('plugins_api', [$this, 'pluginInfo'], 10, 3);
        add_filter('upgrader_post_install', [$this, 'afterInstall'], 10, 3);
        add_action('in_plugin_update_message-' . $this->basename, [$this, 'renderBedrockUpdateMessage']);
    }

    /** @param object $transient */
    public function modifyTransient($transient)
    {
        if (!is_object($transient) || !property_exists($transient, 'checked') || !is_array($transient->checked)) {
            return $transient;
        }

        if (!isset($transient->checked[$this->basename])) {
            return $transient;
        }

        $status = GithubReleaseService::getUpdateStatus();
        if (!$status['updateAvailable'] || !is_string($status['latest']) || $status['latest'] === '') {
            return $transient;
        }

        $pluginData = get_plugin_data(AKYOS_UPDATES_PLUGIN_FILE, false, false);
        $update = [
            'slug' => dirname($this->basename),
            'plugin' => $this->basename,
            'new_version' => $status['latest'],
            'url' => $status['detailsUrl'] ?? ($pluginData['PluginURI'] ?? 'https://akyos.com'),
        ];

        if (!$status['bedrock']) {
            $package = GithubReleaseService::getZipballUrl();
            if ($package !== '') {
                $update['package'] = $package;
            }
        }

        $transient->response[$this->basename] = (object) $update;

        return $transient;
    }

    /** @param false|object|array<string, mixed> $result */
    public function pluginInfo($result, string $action, object $args)
    {
        if ($action !== 'plugin_information' || ($args->slug ?? '') !== dirname($this->basename)) {
            return $result;
        }

        $status = GithubReleaseService::getUpdateStatus();
        if (!is_string($status['latest']) || $status['latest'] === '') {
            return $result;
        }

        $pluginData = get_plugin_data(AKYOS_UPDATES_PLUGIN_FILE, false, false);

        return (object) [
            'name' => $pluginData['Name'] ?? 'Akyos Updates',
            'slug' => dirname($this->basename),
            'version' => $status['latest'],
            'author' => $pluginData['Author'] ?? '',
            'homepage' => $status['detailsUrl'] ?? ($pluginData['PluginURI'] ?? 'https://akyos.com'),
            'download_link' => GithubReleaseService::getZipballUrl(),
            'sections' => [
                'description' => $pluginData['Description'] ?? '',
            ],
        ];
    }

    /** @param array<string, mixed> $response */
    public function afterInstall($response, array $hook_extra, array $result)
    {
        if (($hook_extra['plugin'] ?? '') !== $this->basename || GithubReleaseService::isBedrockInstall()) {
            return $response;
        }

        global $wp_filesystem;
        if (!$wp_filesystem instanceof \WP_Filesystem_Base) {
            return $response;
        }

        $installDirectory = plugin_dir_path(AKYOS_UPDATES_PLUGIN_FILE);
        $wp_filesystem->move($result['destination'], $installDirectory);
        $result['destination'] = $installDirectory;

        if (is_plugin_active($this->basename)) {
            activate_plugin($this->basename);
        }

        return $result;
    }

    public function renderBedrockUpdateMessage(): void
    {
        if (!GithubReleaseService::isBedrockInstall()) {
            return;
        }

        echo ' Mettre à jour via <code>composer update akyos/akyos-updates</code>.';
    }
}
