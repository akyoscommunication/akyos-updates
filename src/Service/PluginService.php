<?php

namespace AkyosUpdates\Service;

use AkyosUpdates\Core\Checks\Rgpd\RgpdPluginCheck;
use AkyosUpdates\Service\BrandaService;
use AkyosUpdates\Service\DefenderService;
use AkyosUpdates\Service\SeoService;

final class PluginService
{
    private const HUMMINGBIRD_MAIN_FILE = 'wp-hummingbird/wp-hummingbird.php';

    private const YOAST_MAIN_FILE = 'wordpress-seo/wp-seo.php';

    private const SMARTCRAWL_MAIN_FILE_CANDIDATES = [
        'smartcrawl-wordpress-seo/wpmu-dev-seo.php',
        'smartcrawl-seo/wpmu-dev-seo.php',
    ];

    private const SMUSH_MAIN_FILE_CANDIDATES = [
        'wp-smushit/wp-smush.php',
        'wp-smush-pro/wp-smush.php',
    ];

    public static function capture(): array
    {
        $seoPluginActive = SeoService::detectProvider()['key'] !== SeoService::PROVIDER_NONE;

        $defenderFile = DefenderService::PLUGIN_FILE;
        $defenderInstalled = self::isPluginInstalled($defenderFile);
        $defenderActive = self::isPluginActiveFile($defenderFile);

        $brandaInstalled = self::isPluginInstalled(BrandaService::PLUGIN_FILE);
        $brandaActive = self::isPluginActiveFile(BrandaService::PLUGIN_FILE);

        $hbInstalled = self::isPluginInstalled(self::HUMMINGBIRD_MAIN_FILE);
        $hbActive = self::isHummingbirdActive();

        $yoastInstalled = self::isPluginInstalled(self::YOAST_MAIN_FILE);
        $yoastActive = self::isPluginActiveFile(self::YOAST_MAIN_FILE);

        $smartcrawlFile = self::resolveExistingSmartcrawlMainFile();
        $smartcrawlInstalled = $smartcrawlFile !== null;
        $smartcrawlActive = $smartcrawlFile !== null && self::isPluginActiveFile($smartcrawlFile);

        $smushFile = self::resolveExistingSmushMainFile();
        $smushInstalled = $smushFile !== null;
        $smushActive = self::isSmushActive();

        [$seoActivateFile, $seoActivateLabel] = self::resolveSeoActivateTarget(
            $seoPluginActive,
            $yoastInstalled,
            $yoastActive,
            $smartcrawlInstalled,
            $smartcrawlActive,
            $smartcrawlFile
        );

        return [
            'defenderActive' => $defenderActive,
            'defenderInstalled' => $defenderInstalled,
            'defenderActivateFile' => ($defenderInstalled && ! $defenderActive) ? $defenderFile : null,

            'brandaActive' => $brandaActive,
            'brandaInstalled' => $brandaInstalled,
            'brandaActivateFile' => ($brandaInstalled && ! $brandaActive) ? BrandaService::PLUGIN_FILE : null,

            'hummingbirdActive' => $hbActive,
            'hummingbirdInstalled' => $hbInstalled,
            'hummingbirdActivateFile' => ($hbInstalled && ! $hbActive) ? self::HUMMINGBIRD_MAIN_FILE : null,

            'seoPluginActive' => $seoPluginActive,
            'seoYoastInstalled' => $yoastInstalled,
            'seoYoastActive' => $yoastActive,
            'seoSmartcrawlInstalled' => $smartcrawlInstalled,
            'seoSmartcrawlActive' => $smartcrawlActive,
            'seoActivateFile' => $seoActivateFile,
            'seoActivateLabel' => $seoActivateLabel,

            'smushActive' => $smushActive,
            'smushInstalled' => $smushInstalled,
            'smushActivateFile' => ($smushInstalled && ! $smushActive && $smushFile !== null) ? $smushFile : null,

            'rgpdPluginActive' => RgpdPluginCheck::hasActiveRgpdRelatedPlugin(),
        ];
    }

    private static function ensurePluginApiLoaded(): void
    {
        if (! function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
    }

    public static function isPluginInstalled(string $relative): bool
    {
        return is_readable(WP_PLUGIN_DIR . '/' . $relative);
    }

    public static function isPluginActiveFile(string $relative): bool
    {
        self::ensurePluginApiLoaded();
        return is_plugin_active($relative) || is_plugin_active_for_network($relative);
    }

    /** @param string[] $pluginFiles */
    public static function isAnyPluginActive(array $pluginFiles): bool
    {
        foreach ($pluginFiles as $pluginFile) {
            if (self::isPluginActiveFile((string) $pluginFile)) {
                return true;
            }
        }

        return false;
    }

    /** @param string[] $pluginFiles */
    public static function resolveInstalledPluginFile(array $pluginFiles): ?string
    {
        foreach ($pluginFiles as $pluginFile) {
            $file = (string) $pluginFile;
            if (self::isPluginInstalled($file)) {
                return $file;
            }
        }

        return null;
    }

    /** @return string[] */
    public static function listActivePluginFiles(): array
    {
        self::ensurePluginApiLoaded();
        if (! is_multisite()) {
            $active = (array) get_option('active_plugins', []);
            return array_values(array_filter(array_map('strval', $active)));
        }

        $active = (array) get_option('active_plugins', []);
        $network = (array) get_site_option('active_sitewide_plugins', []);
        $networkFiles = array_keys($network);

        return array_values(array_unique(array_merge(
            array_filter(array_map('strval', $active)),
            array_map('strval', $networkFiles)
        )));
    }

    private static function isSmushActive(): bool
    {
        return self::isAnyPluginActive(self::SMUSH_MAIN_FILE_CANDIDATES);
    }

    private static function isHummingbirdActive(): bool
    {
        return self::isPluginActiveFile(self::HUMMINGBIRD_MAIN_FILE);
    }

    private static function resolveExistingSmushMainFile(): ?string
    {
        return self::resolveInstalledPluginFile(self::SMUSH_MAIN_FILE_CANDIDATES);
    }

    private static function resolveExistingSmartcrawlMainFile(): ?string
    {
        return self::resolveInstalledPluginFile(self::SMARTCRAWL_MAIN_FILE_CANDIDATES);
    }

    private static function resolveSeoActivateTarget(
        bool $seoPluginActive,
        bool $yoastInstalled,
        bool $yoastActive,
        bool $smartcrawlInstalled,
        bool $smartcrawlActive,
        ?string $smartcrawlFile
    ): array {
        if ($seoPluginActive) {
            return [null, null];
        }

        if ($yoastInstalled && ! $yoastActive) {
            return [self::YOAST_MAIN_FILE, 'Yoast SEO'];
        }

        if ($smartcrawlInstalled && ! $smartcrawlActive && $smartcrawlFile !== null) {
            return [$smartcrawlFile, 'SmartCrawl'];
        }

        return [null, null];
    }
}
