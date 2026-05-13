<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Core\Context\InstallationContextDetector;
use AkyosUpdates\Service\PluginService;

final class GenerateComposerJsonAction implements ActionInterface
{
    private const DEFAULT_VERSION = '*';

    public function getId(): string
    {
        return 'plugins.generate_composer_json';
    }

    public function run(array $payload = []): ActionResult
    {
        $detector = new InstallationContextDetector();
        $context = $detector->detect();
        $isBedrock = $context->getInstallationType() === 'bedrock';

        if (! function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_plugins();
        $activePluginFiles = PluginService::listActivePluginFiles();
        $installedSlugs = [];
        $activeSlugs = [];

        foreach ($plugins as $pluginFile => $meta) {
            $slug = strtok($pluginFile, '/');
            if (! is_string($slug) || $slug === '') {
                continue;
            }
            $installedSlugs[$slug] = true;
            if (in_array($pluginFile, $activePluginFiles, true)) {
                $activeSlugs[$slug] = true;
            }
        }

        $require = $this->buildBaseRequire($isBedrock);
        $defaultPackages = [
            'akyoscommunication/aky-gdpr' => 'dev-master',
            'akyos/akyos-updates' => 'dev-master',
        ];
        foreach ($defaultPackages as $package => $version) {
            $require[$package] = $version;
        }

        $wpmudevSlugMap = [
            'wp-defender' => 'wpmudev/wp-defender',
            'wp-hummingbird' => 'wpmudev/wp-hummingbird',
            'wp-smush-pro' => 'wpmudev/wp-smush-pro',
            'ultimate-branding' => 'wpmudev/ultimate-branding',
            'wpmu-dev-dashboard' => 'wpmudev/wpmu-dev-dashboard',
            'forminator' => 'wpmudev/forminator-pro',
            'smartcrawl-seo' => 'wpmudev/smartcrawl-wordpress-seo',
        ];
        $formPluginSlugs = [
            'contact-form-7',
            'gravityforms',
            'ninja-forms',
            'formidable',
            'formidablepro',
            'fluentform',
            'wpforms-lite',
            'wpforms',
            'caldera-forms',
            'weforms',
        ];

        $yoastActive = isset($activeSlugs['wordpress-seo']);
        $otherFormPluginActive = false;
        foreach ($formPluginSlugs as $slug) {
            if ($slug === 'forminator') {
                continue;
            }
            if (isset($activeSlugs[$slug])) {
                $otherFormPluginActive = true;
                break;
            }
        }

        foreach (array_keys($installedSlugs) as $slug) {
            if ($slug === 'advanced-custom-fields-pro') {
                $require['wpengine/advanced-custom-fields-pro'] = self::DEFAULT_VERSION;
                continue;
            }
            if ($slug === 'advanced-custom-fields') {
                $require['wpackagist-plugin/advanced-custom-fields'] = self::DEFAULT_VERSION;
                continue;
            }
            if ($slug === 'smartcrawl-seo' && $yoastActive) {
                continue;
            }
            if ($slug === 'forminator' && $otherFormPluginActive) {
                continue;
            }

            if (isset($wpmudevSlugMap[$slug])) {
                $require[$wpmudevSlugMap[$slug]] = self::DEFAULT_VERSION;
                continue;
            }

            if ($this->isWpackagistPublic($slug)) {
                $require['wpackagist-plugin/' . $slug] = self::DEFAULT_VERSION;
            }
        }

        ksort($require);

        $repositories = [
            [
                'type' => 'composer',
                'url' => 'https://wpackagist.org',
                'only' => ['wpackagist-plugin/*', 'wpackagist-theme/*'],
            ],
        ];

        if ($this->hasPackagePrefix($require, 'wpmudev/')) {
            $repositories[] = [
                'type' => 'composer',
                'url' => 'https://premium.wpmudev.org/',
            ];
        }

        if (isset($require['wpengine/advanced-custom-fields-pro'])) {
            $repositories[] = [
                'type' => 'composer',
                'url' => 'https://connect.advancedcustomfields.com',
            ];
        }

        if (isset($require['akyoscommunication/aky-gdpr'])) {
            $repositories[] = [
                'type' => 'vcs',
                'url' => 'https://github.com/akyoscommunication/aky-gdpr.git',
            ];
        }

        $composerData = [
            'name' => $isBedrock ? 'roots/bedrock' : 'akyos/generated-wordpress-project',
            'type' => 'project',
            'license' => 'MIT',
            'require' => $require,
            'config' => [
                'optimize-autoloader' => true,
                'preferred-install' => 'dist',
                'allow-plugins' => [
                    'composer/installers' => true,
                ],
            ],
            'minimum-stability' => 'dev',
            'prefer-stable' => true,
            'repositories' => $repositories,
            'extra' => [
                'installer-paths' => $isBedrock
                    ? [
                        'web/app/mu-plugins/{$name}/' => ['type:wordpress-muplugin'],
                        'web/app/plugins/{$name}/' => ['type:wordpress-plugin'],
                        'web/app/themes/{$name}/' => ['type:wordpress-theme'],
                    ]
                    : [
                        'wp-content/mu-plugins/{$name}/' => ['type:wordpress-muplugin'],
                        'wp-content/plugins/{$name}/' => ['type:wordpress-plugin'],
                        'wp-content/themes/{$name}/' => ['type:wordpress-theme'],
                    ],
            ],
        ];

        if ($isBedrock) {
            $composerData['description'] = 'WordPress boilerplate with Composer and Bedrock structure';
            $composerData['require'] = array_merge([
                'composer/installers' => '^2.2',
                'oscarotero/env' => '^2.1',
                'roots/bedrock-autoloader' => '^1.0',
                'roots/bedrock-disallow-indexing' => '^2.0',
                'roots/wordpress' => '*',
                'roots/wp-config' => '1.0.0',
                'roots/wp-password-bcrypt' => '1.1.0',
                'vlucas/phpdotenv' => '^5.5',
                'php' => '>=8.0',
            ], $require);
            ksort($composerData['require']);
            $composerData['config']['allow-plugins']['roots/wordpress-core-installer'] = true;
            if (isset($composerData['require']['wpengine/advanced-custom-fields-pro'])) {
                $composerData['config']['allow-plugins']['pivvenit/acf-pro-installer'] = true;
            }
            $composerData['extra']['wordpress-install-dir'] = 'web/wp';
        } else {
            $composerData['require'] = array_merge([
                'composer/installers' => '^1.11',
                'php' => '>=8.0',
            ], $require);
            ksort($composerData['require']);
        }

        $composerJson = json_encode($composerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (! is_string($composerJson)) {
            return ActionResult::failure('Impossible de générer le composer.json.');
        }

        return ActionResult::success('Template composer.json généré.', [
            'composerJson' => $composerJson . PHP_EOL,
            'targetPath' => $context->getProjectRootPath() . '/composer.json',
            'installationType' => $context->getInstallationType(),
            'detectedPluginsCount' => count($installedSlugs),
        ]);
    }

    private function hasPackagePrefix(array $require, string $prefix): bool
    {
        foreach (array_keys($require) as $packageName) {
            if (str_starts_with((string) $packageName, $prefix)) {
                return true;
            }
        }
        return false;
    }

    private function buildBaseRequire(bool $isBedrock): array
    {
        if ($isBedrock) {
            return [];
        }
        return [];
    }

    private function isWpackagistPublic(string $slug): bool
    {
        if ($slug === '' || str_starts_with($slug, 'akyos-')) {
            return false;
        }

        $cacheKey = 'akyos_updates_wpackagist_' . md5($slug);
        $cached = get_transient($cacheKey);
        if (is_bool($cached)) {
            return $cached;
        }

        $url = sprintf('https://wpackagist.org/packages/wpackagist-plugin/%s.json', rawurlencode($slug));
        $response = wp_remote_get($url, ['timeout' => 4]);
        $isAvailable = ! is_wp_error($response) && (int) wp_remote_retrieve_response_code($response) === 200;
        set_transient($cacheKey, $isAvailable, DAY_IN_SECONDS);

        return $isAvailable;
    }
}
