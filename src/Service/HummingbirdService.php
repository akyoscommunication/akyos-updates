<?php

namespace AkyosUpdates\Service;

final class HummingbirdService
{
    private const MAIN_FILE = 'wp-hummingbird/wp-hummingbird.php';

    public static function isPluginActive(): bool
    {
        return PluginService::isPluginActiveFile(self::MAIN_FILE);
    }

    public static function getPageCacheState(): array
    {
        $state = [
            'available' => false,
            'moduleAvailable' => false,
            'pageCachingEnabled' => false,
            'preloadEnabled' => false,
            'varnishEnabled' => false,
            'clearIntervalEnabled' => false,
            'clearOnUpdateEnabled' => false,
            'missing' => [
                'Page Caching',
                'Préchargement',
                'Varnish',
                'Clear Interval',
                'Clear full cache on post/page update',
            ],
        ];

        if (!class_exists('\Hummingbird\Core\Utils')) {
            return $state;
        }
        $state['available'] = true;

        $pageModule = \Hummingbird\Core\Utils::get_module('page_cache');
        if (!is_object($pageModule) || !method_exists($pageModule, 'get_options') || !method_exists($pageModule, 'get_settings')) {
            return $state;
        }
        $state['moduleAvailable'] = true;

        $options = (array) $pageModule->get_options();
        $settings = (array) $pageModule->get_settings();
        $state['pageCachingEnabled'] = !empty($options['enabled']);
        $state['preloadEnabled'] = !empty($options['preload']);
        $state['varnishEnabled'] = !empty($options['integrations']['varnish']);
        $state['clearIntervalEnabled'] = !empty($settings['clear_interval']['enabled']);
        $state['clearOnUpdateEnabled'] = !empty($settings['settings']['clear_update']);

        $missing = [];
        if (!$state['pageCachingEnabled']) {
            $missing[] = 'Page Caching';
        }
        if (!$state['preloadEnabled']) {
            $missing[] = 'Préchargement';
        }
        if (!$state['varnishEnabled']) {
            $missing[] = 'Varnish';
        }
        if (!$state['clearIntervalEnabled']) {
            $missing[] = 'Clear Interval';
        }
        if (!$state['clearOnUpdateEnabled']) {
            $missing[] = 'Clear full cache on post/page update';
        }
        $state['missing'] = $missing;

        return $state;
    }

    public static function getGzipState(): array
    {
        $state = [
            'available' => false,
            'moduleAvailable' => false,
            'gzipActive' => false,
            'compressionType' => '',
        ];

        if (!class_exists('\Hummingbird\Core\Utils')) {
            return $state;
        }
        $state['available'] = true;

        $gzipModule = \Hummingbird\Core\Utils::get_module('gzip');
        if (!is_object($gzipModule) || !method_exists($gzipModule, 'is_active')) {
            return $state;
        }
        $state['moduleAvailable'] = true;

        $state['gzipActive'] = (bool) $gzipModule->is_active();
        $state['compressionType'] = (string) get_option('wphb_compression_type', '');

        return $state;
    }

    public static function getAdvancedState(): array
    {
        $state = [
            'available' => false,
            'moduleAvailable' => false,
            'queryStringEnabled' => false,
            'emojiEnabled' => false,
            'prefetchMissing' => [],
            'prefetchUrls' => [],
            'preconnectMissing' => [],
            'preconnectUrls' => [],
        ];

        if (!class_exists('\Hummingbird\Core\Utils')) {
            return $state;
        }
        $state['available'] = true;

        $advancedModule = \Hummingbird\Core\Utils::get_module('advanced');
        if (!is_object($advancedModule) || !method_exists($advancedModule, 'get_options')) {
            return $state;
        }
        $state['moduleAvailable'] = true;

        $opts = (array) $advancedModule->get_options();
        $state['queryStringEnabled'] = !empty($opts['query_string']);
        $state['emojiEnabled'] = !empty($opts['emoji']);
        $prefetchRaw = $opts['prefetch'] ?? [];
        $prefetchList = is_array($prefetchRaw) ? $prefetchRaw : [];
        $state['prefetchUrls'] = array_values(array_map('strval', $prefetchList));
        $state['prefetchMissing'] = self::missingPrefetchUrls($prefetchList);
        $preconnectRaw = $opts['preconnect'] ?? [];
        $preconnectList = is_array($preconnectRaw) ? $preconnectRaw : [];
        $state['preconnectUrls'] = array_values(array_map('strval', $preconnectList));
        $state['preconnectMissing'] = self::missingPreconnectUrls($preconnectList);

        return $state;
    }

    public static function prefetchUrls(): array
    {
        return [
            '//fonts.googleapis.com',
            '//fonts.gstatic.com',
            '//ajax.googleapis.com',
            '//apis.google.com',
            '//google-analytics.com',
            '//www.google-analytics.com',
            '//ssl.google-analytics.com',
            '//youtube.com',
            '//s.gravatar.com',
        ];
    }

    public static function preconnectUrls(): array
    {
        return [
            '//fonts.google.com',
            '//cdn.google.com',
        ];
    }

    public static function prefetchComparable(string $url): string
    {
        $u = strtolower(trim($url));
        if ($u === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $u)) {
            return preg_replace('#^https?:#', '', $u);
        }

        return $u;
    }

    public static function missingPrefetchUrls(array $configured): array
    {
        $configuredNorm = [];
        foreach ($configured as $u) {
            $key = self::prefetchComparable((string) $u);
            if ($key !== '') {
                $configuredNorm[$key] = true;
            }
        }

        $missing = [];
        foreach (self::prefetchUrls() as $url) {
            $key = self::prefetchComparable($url);
            if ($key !== '' && empty($configuredNorm[$key])) {
                $missing[] = $url;
            }
        }

        return $missing;
    }

    public static function mergePrefetchUrls(array $configured): array
    {
        $out = [];
        $seen = [];
        foreach ($configured as $u) {
            $trimmed = trim((string) $u);
            $key = self::prefetchComparable($trimmed);
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $trimmed;
        }
        foreach (self::prefetchUrls() as $url) {
            $key = self::prefetchComparable($url);
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $url;
        }

        return $out;
    }

    public static function missingPreconnectUrls(array $configured): array
    {
        $configuredNorm = [];
        foreach ($configured as $u) {
            $key = self::prefetchComparable((string) $u);
            if ($key !== '') {
                $configuredNorm[$key] = true;
            }
        }

        $missing = [];
        foreach (self::preconnectUrls() as $url) {
            $key = self::prefetchComparable($url);
            if ($key !== '' && empty($configuredNorm[$key])) {
                $missing[] = $url;
            }
        }

        return $missing;
    }

    public static function mergePreconnectUrls(array $configured): array
    {
        $out = [];
        $seen = [];
        foreach ($configured as $u) {
            $trimmed = trim((string) $u);
            $key = self::prefetchComparable($trimmed);
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $trimmed;
        }
        foreach (self::preconnectUrls() as $url) {
            $key = self::prefetchComparable($url);
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $url;
        }

        return $out;
    }
}
