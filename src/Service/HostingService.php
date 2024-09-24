<?php

namespace AkyosUpdates\Service;

use AkyosUpdates\Attribute\Hook;

class HostingService
{
    private array $wpmuPluginsComposer;
    public function __construct()
    {
        $this->wpmuPluginsComposer = json_decode(file_get_contents('https://raw.githubusercontent.com/josephfusco/wpmudev-plugins/refs/heads/master/composer.json'), true);
    }

    public function getPhpVersion(): string
    {
        $phpVersion = phpversion();

        if (version_compare($phpVersion, '8.3.0', '<=')) {
            $phpVersionMessage = "⭕ La version de PHP n'est pas à jour : $phpVersion";
        } else {
            $phpVersionMessage = "✅ La version de PHP est à jour : $phpVersion";
        }

        return $phpVersionMessage;
    }

    public function getComposerConfiguration(): array
    {
        $composerJson = file_exists(ABSPATH . 'composer.json');
        $authJson = file_exists(ABSPATH . 'auth.json');

        $composerMessage = '';

        if ($composerJson) {
            $composerMessage = "✅ Le fichier composer.json est présent à la racine du site <br>";
        } else {
            $composerMessage = "⭕ Le fichier composer.json n'est pas présent à la racine du site <br>";
        }

        if ($authJson) {
            $composerMessage .= "✅ Le fichier auth.json est présent à la racine du site";
        } else {
            $composerMessage .= "⭕ Le fichier auth.json n'est pas présent à la racine du site";
        }

        return [
            'message' => $composerMessage,
            'action_required' => !$composerJson || !$authJson
        ];
    }

    #[Hook(hook: 'admin_post_akyos_updates_add_composer_and_auth')]
    public function addComposerAndAuth(): bool
    {
        $composerJson = file_exists(ABSPATH . 'composer.json');
        $authJson = file_exists(ABSPATH . 'auth.json');

        if(!$composerJson) {
            $copyComposer = copy(plugin_dir_path(__DIR__) . '../defaultFiles/default_composer.txt', ABSPATH . 'composer.json');
        }

        if(!$authJson) {
            copy(plugin_dir_path(__DIR__) . '../defaultFiles/default_auth.txt', ABSPATH . 'auth.json');
        }

        return wp_redirect(admin_url('admin.php?page=akyos_updates_hosting'));
    }

    public function getComposerPlugins(): array
    {
        $composerJson = file_exists(ABSPATH . 'composer.json');

        $composerPlugins = [];
        $composerPluginMessage = '';

        if ($composerJson) {
            $plugins = array_filter(glob(ABSPATH . 'wp-content/plugins/*'), 'is_dir');
            $plugins = array_map('basename', $plugins);
            $plugins = array_map(function($plugin) {
                $plugin = $plugin === 'aky-gdpr-master' ? 'aky-gdpr' : $plugin;
                $plugin = $plugin === 'advanced-custom-fields' ? 'advanced-custom-fields-pro' : $plugin;
                $plugin = $plugin === 'wpmudev-updates' ? 'wpmu-dev-dashboard' : $plugin;
                return $plugin;
            }, $plugins);

            // Find packages in composer.json require section
            $pluginsInComposer = [];
            $composerJson = file_get_contents(ABSPATH . 'composer.json');
            $composerData = json_decode($composerJson, true);

            if (isset($composerData['require'])) {
                $pluginsInComposer = array_keys($composerData['require']);
            }

            $pluginsInComposer = array_filter($pluginsInComposer, function($plugin) {
                return !in_array($plugin, ['php', 'composer/installers']);
            });

            $pluginsInComposer = array_map(function($plugin) {
                $exploded = explode('/', $plugin);
                return end($exploded);
            }, $pluginsInComposer);

            $composerPluginMessage = 'Plugins dans le fichier composer.json : <br>';
            foreach ($pluginsInComposer as $plugin) {
                $composerPluginMessage .= '✅ '.$plugin.' <br>';
            }
            $composerPluginMessage .= '<br>';

            $pluginsNotInComposer = array_diff($plugins, $pluginsInComposer);

            $composerPluginMessage .= 'Plugins installés sur le site qui ne sont pas dans le fichier composer.json : <br>';
            foreach ($pluginsNotInComposer as $plugin) {

                if($plugin === 'aky-gdpr' || $plugin === 'aky-gdpr-master') {
                    $pluginPackage = 'akyoscommunication/aky-gdpr';
                } elseif($plugin === 'akyos-updates') {
                    $pluginPackage = 'akyos/akyos-updates';
                } elseif($plugin === 'advanced-custom-fields' || $plugin === 'advanced-custom-fields-pro') {
                    $pluginPackage = 'wpengine/advanced-custom-fields-pro';
                } elseif($plugin === 'wpmudev-updates') {
                    $pluginPackage = 'wpmudev/';
                } else {
                    $pluginPackage = null;
                }

                if(!$pluginPackage) {
                    $pluginPackage = $this->packageExistsOnWPPackagist($plugin) ? 'wp-packagist/' . $plugin : null;
                }

                if(!$pluginPackage) {
                    $pluginPackage = $this->packageExistsOnWPMU($plugin) ? 'wpmudev/' . $plugin : null;
                }

                if(!$pluginPackage) {
                    $composerPlugins[] = [
                        'message' => '🔴 '.$plugin.' - Impossible de trouver un repository public pour le plugin. <br>',
                    ];
                } else {
                    $composerPlugins[] = [
                        'message' => '🟠 '.$pluginPackage.' <br>',
                    ];
                }
            }
        }

        return [
            'message' => $composerPluginMessage,
            'plugins' => $composerPlugins,
        ];
    }

    #[Hook(hook: 'admin_post_akyos_updates_add_packages_in_composer')]
    public function addPackagesInComposer(): bool
    {
        $composerJson = file_exists(ABSPATH . 'composer.json');

        $composerPlugins = [];

        if ($composerJson) {
            $plugins = array_filter(glob(ABSPATH . 'wp-content/plugins/*'), 'is_dir');
            $plugins = array_map('basename', $plugins);
            $plugins = array_map(function($plugin) {
                $plugin = $plugin === 'aky-gdpr-master' ? 'aky-gdpr' : $plugin;
                $plugin = $plugin === 'advanced-custom-fields' ? 'advanced-custom-fields-pro' : $plugin;
                $plugin = $plugin === 'wpmudev-updates' ? 'wpmu-dev-dashboard' : $plugin;
                return $plugin;
            }, $plugins);

            // Find packages in composer.json require section
            $pluginsInComposer = [];
            $composerJson = file_get_contents(ABSPATH . 'composer.json');
            $composerData = json_decode($composerJson, true);

            if (isset($composerData['require'])) {
                $pluginsInComposer = array_keys($composerData['require']);
            }

            $pluginsInComposer = array_filter($pluginsInComposer, function($plugin) {
                return !in_array($plugin, ['php', 'composer/installers']);
            });

            $pluginsInComposer = array_map(function($plugin) {
                $exploded = explode('/', $plugin);
                return end($exploded);
            }, $pluginsInComposer);

            $pluginsNotInComposer = array_diff($plugins, $pluginsInComposer);

            foreach ($pluginsNotInComposer as $plugin) {

                if($plugin === 'aky-gdpr' || $plugin === 'aky-gdpr-master') {
                    $pluginPackage = 'akyoscommunication/aky-gdpr';
                } elseif($plugin === 'akyos-updates') {
                    $pluginPackage = 'akyos/akyos-updates';
                } elseif($plugin === 'advanced-custom-fields' || $plugin === 'advanced-custom-fields-pro') {
                    $pluginPackage = 'wpengine/advanced-custom-fields-pro';
                } elseif($plugin === 'wpmudev-updates') {
                    $pluginPackage = 'wpmudev/';
                } else {
                    $pluginPackage = null;
                }

                if(!$pluginPackage) {
                    $pluginPackage = $this->packageExistsOnWPPackagist($plugin) ? 'wp-packagist/' . $plugin : null;
                }

                if(!$pluginPackage) {
                    $pluginPackage = $this->packageExistsOnWPMU($plugin) ? 'wpmudev/' . $plugin : null;
                }

                if($pluginPackage) {
                    $composerPlugins[] = $pluginPackage;
                }
            }

            foreach ($composerPlugins as $plugin) {
                $composerData['require'][$plugin] = '*';
            }

            file_put_contents(ABSPATH . 'composer.json', json_encode($composerData, JSON_PRETTY_PRINT));
        }

        return wp_redirect(admin_url('admin.php?page=akyos_updates_hosting'));
    }

    private function packageExistsOnWPPackagist($pluginSlug): bool
    {
        $url = 'https://api.wordpress.org/plugins/info/1.0/' . $pluginSlug;
        $headers = @get_headers($url);
        return $headers && str_contains($headers[0], '200');
    }

    private function packageExistsOnWPMU($package): bool
    {
        $pluginsInWPMUComposer = array_keys($this->wpmuPluginsComposer['require']);
        $pluginsInWPMUComposer = array_map(function($plugin) {
            $exploded = explode('/', $plugin);
            return end($exploded);
        }, $pluginsInWPMUComposer);
        return in_array($package, $pluginsInWPMUComposer);
    }
}