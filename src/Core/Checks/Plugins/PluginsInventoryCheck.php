<?php

namespace AkyosUpdates\Core\Checks\Plugins;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\ComposerService;
use AkyosUpdates\Service\PluginService;

final class PluginsInventoryCheck implements CheckInterface
{
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
}
