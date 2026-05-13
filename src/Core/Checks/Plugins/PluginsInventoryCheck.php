<?php

namespace AkyosUpdates\Core\Checks\Plugins;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\ComposerService;
use AkyosUpdates\Service\PluginService;

final class PluginsInventoryCheck implements CheckInterface
{
    private const UPDATES_REFRESH_LOCK_TRANSIENT = 'akyos_updates_plugins_inventory_refresh_lock';

    public function getId(): string
    {
        return 'plugins.inventory';
    }

    public function getCategory(): string
    {
        return 'Plugins';
    }

    public function getTitle(): string
    {
        return 'Inventaire plugins';
    }
    public function getSuccessMessage(): string
    {
        return 'Point validé après action corrective.';
    }


    public function run(SiteContext $context): CheckResult
    {
        if (! function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if (! function_exists('wp_update_plugins')) {
            require_once ABSPATH . 'wp-includes/update.php';
        }

        $pluginUpdates = get_site_transient('update_plugins');
        $lastChecked = is_object($pluginUpdates) ? (int) ($pluginUpdates->last_checked ?? 0) : 0;
        $updatesStale = $lastChecked === 0 || (time() - $lastChecked) > 12 * HOUR_IN_SECONDS;
        if ($updatesStale && ! get_transient(self::UPDATES_REFRESH_LOCK_TRANSIENT)) {
            set_transient(self::UPDATES_REFRESH_LOCK_TRANSIENT, '1', 5 * MINUTE_IN_SECONDS);
            wp_update_plugins(['akyos_updates_plugins_inventory' => '1']);
        }

        $pluginUpdates = get_site_transient('update_plugins');
        $updatesResponse = is_object($pluginUpdates) && isset($pluginUpdates->response) && is_array($pluginUpdates->response)
            ? $pluginUpdates->response
            : [];
        $updatesNoChange = is_object($pluginUpdates) && isset($pluginUpdates->no_update) && is_array($pluginUpdates->no_update)
            ? $pluginUpdates->no_update
            : [];

        $plugins = get_plugins();
        $activePlugins = PluginService::listActivePluginFiles();
        $rows = [];

        $composerPath = $context->getProjectRootPath() . '/composer.json';
        $composerJsonReadable = false;
        $managedSlugs = [];
        if (is_readable($composerPath)) {
            $rawComposer = (string) file_get_contents($composerPath);
            $decodedComposer = json_decode($rawComposer, true);
            if (is_array($decodedComposer)) {
                $require = is_array($decodedComposer['require'] ?? null) ? $decodedComposer['require'] : [];
                $managedSlugs = ComposerService::fromRequire($require);
                $composerJsonReadable = true;
            }
        }

        foreach ($plugins as $file => $meta) {
            $slug = strtok($file, '/');
            $rows[] = [
                'name' => $meta['Name'] ?? $slug,
                'slug' => $slug,
                'version' => $meta['Version'] ?? 'n/a',
                'active' => in_array($file, $activePlugins, true),
                'mustUse' => false,
                'inComposer' => $composerJsonReadable ? in_array($slug, $managedSlugs, true) : null,
                'upToDate' => $this->resolvePluginUpToDate($file, $updatesResponse, $updatesNoChange, false),
            ];
        }

        $muPlugins = get_mu_plugins();
        foreach ($muPlugins as $file => $meta) {
            $slug = basename($file, '.php');
            $rows[] = [
                'name' => $meta['Name'] ?? $file,
                'slug' => $slug,
                'version' => $meta['Version'] ?? 'n/a',
                'active' => true,
                'mustUse' => true,
                'inComposer' => $composerJsonReadable ? in_array($slug, $managedSlugs, true) : null,
                'upToDate' => $this->resolvePluginUpToDate($file, $updatesResponse, $updatesNoChange, true),
            ];
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'ok',
            'info',
            sprintf('%d plugins détectés.', count($rows)),
            false,
            null,
            [
                'plugins' => $rows,
                'composerJsonReadable' => $composerJsonReadable,
            ]
        );
    }

    private function resolvePluginUpToDate(
        string $pluginFile,
        array $updatesResponse,
        array $updatesNoChange,
        bool $isMu
    ): ?bool {
        if ($isMu) {
            return null;
        }
        if (array_key_exists($pluginFile, $updatesResponse)) {
            return false;
        }
        if (array_key_exists($pluginFile, $updatesNoChange)) {
            return true;
        }

        return null;
    }
}
