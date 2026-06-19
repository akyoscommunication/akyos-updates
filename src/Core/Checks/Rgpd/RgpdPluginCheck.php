<?php

namespace AkyosUpdates\Core\Checks\Rgpd;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\PluginService;
use AkyosUpdates\Service\RgpdSettingsService;

final class RgpdPluginCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'rgpd.rgpd_plugin';
    }

    public function getCategory(): string
    {
        return 'RGPD';
    }

    public function getTitle(): string
    {
        return 'Plugin RGPD / cookies actif';
    }
    public function getSuccessMessage(): string
    {
        return 'Point validé après action corrective.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $internalActive = (new RgpdSettingsService())->isActiveOnFrontend();
        $active = self::collectMatchingPlugins();
        $ok = $internalActive || $active !== [];

        $labels = array_map(
            static fn(array $row): string => $row['label'],
            $active
        );
        if ($internalActive) {
            array_unshift($labels, 'Module RGPD Akyos Updates (intégré)');
        }

        $message = $ok
            ? 'Au moins une solution RGPD / cookies / consentement est active : ' . implode(', ', $labels) . '.'
            : 'Aucune extension détectée. Active le module RGPD intégré (onglet RGPD) ou une extension dédiée.';

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            $ok ? 'ok' : 'warn',
            $ok ? 'success' : 'warning',
            $message,
            false,
            null,
            [
                'matchedPlugins' => $active,
                'internalModuleActive' => $internalActive,
            ]
        );
    }

    public static function hasActiveRgpdRelatedPlugin(): bool
    {
        return self::collectMatchingPlugins() !== [];
    }

    /** @return array<int, array{slug: string, file: string, label: string}> */
    public static function collectMatchingPlugins(): array
    {
        if (! function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all = get_plugins();
        $activeFiles = PluginService::listActivePluginFiles();
        $out = [];

        foreach ($activeFiles as $file) {
            $file = (string) $file;
            if ($file === '' || ! isset($all[$file])) {
                continue;
            }

            $slug = self::slugFromPluginFile($file);
            $slugLower = mb_strtolower($slug);
            if (! self::slugLooksRgpdRelated($slugLower)) {
                continue;
            }

            $data = $all[$file];
            $name = is_array($data) && isset($data['Name']) ? (string) $data['Name'] : $slug;

            $out[] = [
                'slug' => $slug,
                'file' => $file,
                'label' => $name,
            ];
        }

        return $out;
    }

    private static function slugFromPluginFile(string $pluginFile): string
    {
        $dir = dirname($pluginFile);

        return $dir === '.' ? basename($pluginFile, '.php') : $dir;
    }

    private static function slugLooksRgpdRelated(string $slugLower): bool
    {
        foreach (['rgpd', 'gdpr', 'cookie', 'consent', 'cmp', 'privacy', 'cookielaw', 'cookieyes', 'complianz', 'legal'] as $needle) {
            if (str_contains($slugLower, $needle)) {
                return true;
            }
        }

        return (bool) preg_match('/\b(iubenda|onetrust|termsfeed)\b/i', $slugLower);
    }
}
