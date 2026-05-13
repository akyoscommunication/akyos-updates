<?php

namespace AkyosUpdates\Core\Actions;

final class HummingbirdApplyPageCacheConfigAction implements ActionInterface
{
    public function getId(): string
    {
        return 'performance.hummingbird_apply_page_cache_config';
    }

    public function run(array $payload = []): ActionResult
    {
        if (! class_exists('\Hummingbird\Core\Utils')) {
            return ActionResult::failure('Hummingbird non disponible.');
        }

        $pageModule = \Hummingbird\Core\Utils::get_module('page_cache');
        if (! is_object($pageModule) || ! method_exists($pageModule, 'get_options') || ! method_exists($pageModule, 'update_options') || ! method_exists($pageModule, 'get_settings') || ! method_exists($pageModule, 'save_settings')) {
            return ActionResult::failure('Module Page Caching Hummingbird indisponible.');
        }

        $options = (array) $pageModule->get_options();
        $options['enabled'] = true;
        $options['preload'] = true;
        if (array_key_exists('cache_blog', $options)) {
            $options['cache_blog'] = true;
        }
        $integrations = is_array($options['integrations'] ?? null) ? $options['integrations'] : [];
        $integrations['varnish'] = true;
        $options['integrations'] = $integrations;
        $pageModule->update_options($options);
        if (method_exists($pageModule, 'enable')) {
            $pageModule->enable(true);
        }

        $settings = (array) $pageModule->get_settings();
        $settings['clear_interval'] = is_array($settings['clear_interval'] ?? null) ? $settings['clear_interval'] : [];
        $settings['clear_interval']['enabled'] = true;
        if (empty($settings['clear_interval']['interval'])) {
            $settings['clear_interval']['interval'] = 720;
        }
        $settings['settings'] = is_array($settings['settings'] ?? null) ? $settings['settings'] : [];
        $settings['settings']['clear_update'] = 1;
        $pageModule->save_settings($settings);

        $updatedOptions = (array) $pageModule->get_options();
        $updatedSettings = (array) $pageModule->get_settings();
        $pageCachingEnabled = ! empty($updatedOptions['enabled']);
        $preloadEnabled = ! empty($updatedOptions['preload']);
        $varnishEnabled = ! empty($updatedOptions['integrations']['varnish']);
        $clearIntervalEnabled = ! empty($updatedSettings['clear_interval']['enabled']);
        $clearOnUpdateEnabled = ! empty($updatedSettings['settings']['clear_update']);

        $missing = [];
        if (! $pageCachingEnabled) {
            $missing[] = 'Page Caching';
        }
        if (! $preloadEnabled) {
            $missing[] = 'Préchargement';
        }
        if (! $varnishEnabled) {
            $missing[] = 'Varnish';
        }
        if (! $clearIntervalEnabled) {
            $missing[] = 'Clear Interval';
        }
        if (! $clearOnUpdateEnabled) {
            $missing[] = 'Clear full cache on post/page update';
        }
        $fullyApplied = $missing === [];

        $message = $fullyApplied
            ? 'Configuration Hummingbird Page Caching appliquée.'
            : sprintf('Configuration partielle. Réglages encore inactifs: %s.', implode(', ', $missing));
        $data = [
            'pageCachingEnabled' => $pageCachingEnabled,
            'preloadEnabled' => $preloadEnabled,
            'varnishEnabled' => $varnishEnabled,
            'clearIntervalEnabled' => $clearIntervalEnabled,
            'clearOnUpdateEnabled' => $clearOnUpdateEnabled,
            'missing' => $missing,
        ];
        if ($fullyApplied) {
            return ActionResult::success($message, $data);
        }

        return ActionResult::failure($message, $data);
    }
}
