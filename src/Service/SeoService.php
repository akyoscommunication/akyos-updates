<?php

namespace AkyosUpdates\Service;

final class SeoService
{
    public const PROVIDER_SMARTCRAWL = 'smartcrawl';
    public const PROVIDER_YOAST = 'yoast';
    public const PROVIDER_NONE = 'none';
    private const SITEMAP_CACHE_PREFIX = 'akyos_updates_sitemap_url_v3_';
    private const SITEMAP_CACHE_TTL_FOUND = 30 * MINUTE_IN_SECONDS;

    public static function detectProvider(): array
    {
        if (self::isSmartcrawlActive()) {
            return [
                'key' => self::PROVIDER_SMARTCRAWL,
                'label' => 'SmartCrawl (WPMU DEV)',
            ];
        }

        if (self::isYoastActive()) {
            return [
                'key' => self::PROVIDER_YOAST,
                'label' => 'Yoast SEO',
            ];
        }

        return [
            'key' => self::PROVIDER_NONE,
            'label' => 'Aucun plugin SEO détecté',
        ];
    }

    public static function isNoindexEnabled(string $kind, string $name): bool
    {
        $provider = self::detectProvider()['key'];
        if ($provider === self::PROVIDER_SMARTCRAWL) {
            $options = get_option('wds_onpage_options', []);
            return ! empty($options[sprintf('meta_robots-noindex-%s', $name)]);
        }

        if ($provider === self::PROVIDER_YOAST) {
            $titles = get_option('wpseo_titles', []);
            $key = $kind === 'taxonomy' ? sprintf('noindex-tax-%s', $name) : sprintf('noindex-%s', $name);
            return ! empty($titles[$key]);
        }

        return false;
    }

    public static function setNoindex(string $kind, string $name, bool $noindex): bool
    {
        $provider = self::detectProvider()['key'];

        if ($provider === self::PROVIDER_SMARTCRAWL) {
            $options = get_option('wds_onpage_options', []);
            $key = sprintf('meta_robots-noindex-%s', $name);
            $current = ! empty($options[$key]);
            if ($current === $noindex) {
                return true;
            }
            if ($noindex) {
                $options[$key] = true;
            } else {
                unset($options[$key]);
            }
            return (bool) update_option('wds_onpage_options', $options, false);
        }

        if ($provider === self::PROVIDER_YOAST) {
            $titles = get_option('wpseo_titles', []);
            $key = $kind === 'taxonomy' ? sprintf('noindex-tax-%s', $name) : sprintf('noindex-%s', $name);
            $current = ! empty($titles[$key]);
            if ($current === $noindex) {
                return true;
            }
            $titles[$key] = $noindex;
            return (bool) update_option('wpseo_titles', $titles, false);
        }

        return false;
    }

    public static function setPostNoindex(int $postId, bool $noindex): bool
    {
        if ($postId <= 0) {
            return false;
        }

        $provider = self::detectProvider()['key'];
        if ($provider === self::PROVIDER_SMARTCRAWL) {
            if ($noindex) {
                update_post_meta($postId, '_wds_meta-robots-noindex', 1);
                delete_post_meta($postId, '_wds_meta-robots-index');
            } else {
                delete_post_meta($postId, '_wds_meta-robots-noindex');
            }
            return true;
        }

        if ($provider === self::PROVIDER_YOAST) {
            update_post_meta($postId, '_yoast_wpseo_meta-robots-noindex', $noindex ? '1' : '2');
            return true;
        }

        return false;
    }

    public static function isPostNoindexByMeta(int $postId): bool
    {
        $provider = self::detectProvider()['key'];

        if ($provider === self::PROVIDER_SMARTCRAWL) {
            $explicitIndex = (bool) get_post_meta($postId, '_wds_meta-robots-index', true);
            if ($explicitIndex) {
                return false;
            }
            $explicitNoindex = (bool) get_post_meta($postId, '_wds_meta-robots-noindex', true);
            if ($explicitNoindex) {
                return true;
            }
            return self::isNoindexEnabled('post_type', 'page');
        }

        if ($provider === self::PROVIDER_YOAST) {
            $metaNoindex = (string) get_post_meta($postId, '_yoast_wpseo_meta-robots-noindex', true);
            if ($metaNoindex === '1') {
                return true;
            }
            if ($metaNoindex === '2') {
                return false;
            }
            return self::isNoindexEnabled('post_type', 'page');
        }

        return false;
    }

    public static function findSitemapUrl(): ?string
    {
        $cacheKey = self::SITEMAP_CACHE_PREFIX . md5(home_url('/'));
        $cached = get_transient($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $inferred = self::inferSitemapUrlFromActiveProvider();
        $candidates = self::orderedSitemapCandidates($inferred);

        foreach ($candidates as $candidate) {
            if (self::isSitemapUrlReachable($candidate)) {
                set_transient($cacheKey, $candidate, self::SITEMAP_CACHE_TTL_FOUND);
                return $candidate;
            }
        }

        return null;
    }

    private static function isSitemapUrlReachable(string $url): bool
    {
        $args = [
            'timeout' => 5,
            'redirection' => 2,
            'sslverify' => apply_filters('https_local_ssl_verify', false),
        ];

        $head = wp_remote_head($url, $args);
        if (! is_wp_error($head)) {
            $status = (int) wp_remote_retrieve_response_code($head);
            if ($status >= 200 && $status < 400) {
                return true;
            }
        }

        $get = wp_remote_get($url, $args);
        if (is_wp_error($get)) {
            return false;
        }

        $status = (int) wp_remote_retrieve_response_code($get);
        return $status >= 200 && $status < 400;
    }

    private static function orderedSitemapCandidates(string $inferred): array
    {
        $candidates = [];
        if ($inferred !== '') {
            $candidates[] = $inferred;
        }

        $providerKey = self::detectProvider()['key'];

        if ($providerKey === self::PROVIDER_YOAST) {
            $tail = [
                home_url('/sitemap_index.xml'),
                home_url('/wp-sitemap.xml'),
                home_url('/sitemap.xml'),
            ];
        } elseif ($providerKey === self::PROVIDER_SMARTCRAWL) {
            $tail = [
                home_url('/sitemap.xml'),
                home_url('/sitemap_index.xml'),
                home_url('/wp-sitemap.xml'),
            ];
        } else {
            $tail = [
                home_url('/wp-sitemap.xml'),
                home_url('/sitemap_index.xml'),
                home_url('/sitemap.xml'),
            ];
        }

        foreach ($tail as $candidate) {
            if (! in_array($candidate, $candidates, true)) {
                $candidates[] = $candidate;
            }
        }

        return $candidates;
    }

    private static function inferSitemapUrlFromActiveProvider(): string
    {
        if (self::smartcrawlSitemapShouldRun()) {
            return home_url('/sitemap.xml');
        }

        if (self::yoastXmlSitemapsEnabled()) {
            return home_url('/sitemap_index.xml');
        }

        return '';
    }

    private static function smartcrawlSitemapShouldRun(): bool
    {
        if (! class_exists('\SmartCrawl\Settings')) {
            return false;
        }

        if (! \SmartCrawl\Settings::get_setting('sitemap')) {
            return false;
        }

        if (class_exists('\SmartCrawl\Admin\Settings\Admin_Settings')) {
            return \SmartCrawl\Admin\Settings\Admin_Settings::is_tab_allowed(\SmartCrawl\Settings::TAB_SITEMAP);
        }

        return true;
    }

    private static function yoastXmlSitemapsEnabled(): bool
    {
        if (! class_exists('\WPSEO_Options')) {
            return false;
        }

        return (bool) \WPSEO_Options::get('enable_xml_sitemap', false, ['wpseo']);
    }

    private static function isSmartcrawlActive(): bool
    {
        return PluginService::isAnyPluginActive([
            'smartcrawl-wordpress-seo/wpmu-dev-seo.php',
            'smartcrawl-seo/wpmu-dev-seo.php',
        ]);
    }

    private static function isYoastActive(): bool
    {
        return PluginService::isPluginActiveFile('wordpress-seo/wp-seo.php');
    }

    public static function getReportSitemapUrl(): ?string
    {
        $providerKey = self::detectProvider()['key'];

        if ($providerKey === self::PROVIDER_YOAST) {
            return home_url('/sitemap_index.xml');
        }

        if ($providerKey === self::PROVIDER_SMARTCRAWL) {
            return home_url('/sitemap.xml');
        }

        return null;
    }
}
