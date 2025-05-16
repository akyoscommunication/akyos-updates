<?php

namespace AkyosUpdates\Service\Plugins;

use AkyosUpdates\Attribute\Hook;
use AkyosUpdates\Trait\ServiceTrait;

class PluginComposerService
{
    use ServiceTrait;

    public function __construct()
    {
        $this->redirectRoute = 'akyos_updates_plugins';
    }

    public function getComposerConfiguration(): array
    {
        $composerJson = file_exists(ABSPATH . 'composer.json');
        $authJson = file_exists(ABSPATH . 'auth.json');

        if ($this->isBedrock()) {
            $composerJson = file_exists($this->getBedrockRoot() . '/composer.json');
            $authJson = file_exists($this->getBedrockRoot() . '/auth.json');
        } else {
            $composerJson = file_exists(ABSPATH . 'composer.json');
            $authJson = file_exists(ABSPATH . 'auth.json');
        }

        $composerMessage = '';

        if ($composerJson) {
            $composerMessage = "<p>✅ Le fichier composer.json est présent à la racine du site <br></p>";
        } else {
            $composerMessage = "<p>⭕ Le fichier composer.json n'est pas présent à la racine du site <br></p>";
        }

        if ($authJson) {
            $composerMessage .= "<p>✅ Le fichier auth.json est présent à la racine du site</p>";
        } else {
            $composerMessage .= "<p>⭕ Le fichier auth.json n'est pas présent à la racine du site</p>";
        }

        return [
            'message' => $composerMessage,
            'action_required' => !$composerJson || !$authJson,
            'ajax' => [
                'hook' => 'admin_post_akyos_updates_add_composer_and_auth'
            ]
        ];
    }

    #[Hook(hook: 'admin_post_akyos_updates_add_composer_and_auth')]
    public function addComposerAndAuth(): void
    {
        $composerJson = file_exists(ABSPATH . 'composer.json');
        $authJson = file_exists(ABSPATH . 'auth.json');

        if (!$composerJson) {
            copy(plugin_dir_path(__DIR__) . '../defaultFiles/default_composer.txt', ABSPATH . 'composer.json');
        }

        if (!$authJson) {
            copy(plugin_dir_path(__DIR__) . '../defaultFiles/default_auth.txt', ABSPATH . 'auth.json');
        }
    }

    public function getComposerPlugins(): array
    {

        if ($this->isBedrock()) {
            $composerJson = file_exists($this->getBedrockRoot() . '/composer.json');
        } else {
            $composerJson = file_exists(ABSPATH . 'composer.json');
        }

        $composerPlugins = [];
        $composerPluginMessage = '';

        if ($composerJson) {
            [$plugins, $pluginsInComposer, $pluginsNotInComposer] = $this->getPlugins();

            $composerPluginMessage = 'Plugins dans le fichier composer.json : <br>';
            $composerPluginMessage .= '<ul>';
            foreach ($pluginsInComposer as $plugin) {
                $composerPluginMessage .= '<li><p>✅ ' . $plugin . ' </p></li>';
            }
            $composerPluginMessage .= '</ul>';
            $composerPluginMessage .= '<br>';

            if (!empty($pluginsNotInComposer)) {
                $composerPluginsFound = [];
                $composerPluginsNotFound = [];

                foreach ($pluginsNotInComposer as $plugin) {
                    $pluginPackage = $this->getPackage($plugin);

                    if (!$pluginPackage) {
                        $composerPluginsNotFound[] = '<p>🔴 ' . $plugin . '</p>';
                    } else {
                        $composerPluginsFound[] = '<p>🟠 ' . $pluginPackage . ' </p>';
                    }
                }

                if (!empty($composerPluginsFound)) {
                    $composerPluginMessage .= '<p>Plugins installés sur le site qui ne sont pas dans le fichier composer.json : </p>';
                    $composerPluginMessage .= implode('', $composerPluginsFound) . '<br/>';
                }

                if (!empty($composerPluginsNotFound)) {
                    $composerPluginMessage .= '<p>Impossible de trouver un repository public (à ajouter dans le .gitignore) : </p>';
                    $composerPluginMessage .= implode('', $composerPluginsNotFound);
                }
            }
        }

        return [
            'message' => $composerPluginMessage,
            'action_required' => !empty($pluginsNotInComposer),
            'ajax' => [
                'hook' => 'admin_post_akyos_updates_add_packages_in_composer'
            ]
        ];
    }

    /**
     * @throws \JsonException
     */
    #[Hook(hook: 'admin_post_akyos_updates_add_packages_in_composer')]
    public function addPackagesInComposer(): void
    {
        if ($this->isBedrock()) {
            $composerPath = $this->getBedrockRoot() . '/composer.json';
        } else {
            $composerPath = ABSPATH . 'composer.json';
        }

        $composerJson = file_exists($composerPath);
        $composerData = json_decode(file_get_contents($composerPath), true, 512, JSON_THROW_ON_ERROR);

        $composerPlugins = [];

        if ($composerJson) {
            [$plugins, $pluginsInComposer, $pluginsNotInComposer] = $this->getPlugins();

            foreach ($pluginsNotInComposer as $plugin) {
                $pluginPackage = $this->getPackage($plugin);

                if ($pluginPackage) {
                    $composerPlugins[] = $pluginPackage;
                } else {
                    if ($this->isBedrock()) {
                        $gitIgnorePath = $this->getBedrockRoot() . '/.gitignore';
                        $gitIgnoreContent = file_get_contents($gitIgnorePath);
                        $gitIgnoreContent .= "\n" . '!web/app/plugins/' . $plugin . '/';
                    } else {
                        $gitIgnorePath = ABSPATH . '.gitignore';
                        $gitIgnoreContent = file_get_contents($gitIgnorePath);
                        $gitIgnoreContent .= "\n" . '!wp-content/plugins/' . $plugin . '/';
                    }

                    file_put_contents($gitIgnorePath, $gitIgnoreContent);
                }
            }

            foreach ($composerPlugins as $plugin) {
                if ($plugin === 'akyoscommunication/aky-gdpr') {
                    $composerData['require'][$plugin] = 'dev-master';
                } else {
                    $composerData['require'][$plugin] = '*';
                }
            }

            file_put_contents($composerPath, json_encode($composerData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    }

    private function packageExistsOnWPPackagist($pluginSlug): bool
    {
        $url = 'https://api.wordpress.org/plugins/info/1.0/' . $pluginSlug;
        $headers = @get_headers($url);
        return $headers && str_contains($headers[0], '200');
    }

    /**
     * @throws \JsonException
     */
    private function packageExistsOnWPMU($package): bool
    {
        $wpmuPluginsComposer = json_decode(file_get_contents('https://raw.githubusercontent.com/josephfusco/wpmudev-plugins/refs/heads/master/composer.json'), true, 512, JSON_THROW_ON_ERROR);

        $pluginsInWPMUComposer = array_keys($wpmuPluginsComposer['require']);

        $pluginsInWPMUComposer = array_map(function ($plugin) {
            $exploded = explode('/', $plugin);
            return end($exploded);
        }, $pluginsInWPMUComposer);

        return in_array($package, $pluginsInWPMUComposer);
    }

    public function shouldRunComposerUpdate(): array
    {
        $composerMessage = '<p>Mets à jour tous les plugins présents dans le fichier composer.json</p>';

        return [
            'message' => $composerMessage,
            'action_required' => true
        ];
    }

    #[Hook(hook: 'admin_post_akyos_updates_run_composer_update')]
    public function runComposerUpdate(): bool
    {
        shell_exec('cd .. && composer update');

        return wp_redirect(admin_url('admin.php?page=' . $this->redirectRoute));
    }

    /**
     * @throws \JsonException
     */
    public function getPlugins(): array
    {
        if ($this->isBedrock()) {
            $plugins = array_filter(glob(dirname(ABSPATH) . '/app/plugins/*'), 'is_dir');
        } else {
            $plugins = array_filter(glob(ABSPATH . 'wp-content/plugins/*'), 'is_dir');
        }

        $plugins = array_map('basename', $plugins);
        $plugins = array_map(static function ($plugin) {
            $plugin = $plugin === 'aky-gdpr-master' ? 'aky-gdpr' : $plugin;
            $plugin = $plugin === 'advanced-custom-fields' ? 'advanced-custom-fields-pro' : $plugin;
            return $plugin === 'wpmudev-updates' ? 'wpmu-dev-dashboard' : $plugin;
        }, $plugins);

        // Find packages in composer.json require section
        $pluginsInComposer = [];
        if ($this->isBedrock()) {
            $composerJson = file_get_contents($this->getBedrockRoot() . '/composer.json');
        } else {
            $composerJson = file_get_contents(ABSPATH . 'composer.json');
        }
        $composerData = json_decode($composerJson, true, 512, JSON_THROW_ON_ERROR);


        if (isset($composerData['require'])) {
            $pluginsInComposer = array_keys($composerData['require']);
        }

        $pluginsInComposer = array_filter($pluginsInComposer, static function ($plugin) {

            return !in_array($plugin, ['php', 'composer/installers', 'vlucas/phpdotenv', 'oscarotero/env', 'roots/bedrock-autoloader', 'roots/wordpress', 'roots/wp-config', 'roots/wp-password-bcrypt', 'roots/bedrock-disallow-indexing']);
        });


        $pluginsInComposer = array_map(static function ($plugin) {
            $exploded = explode('/', $plugin);
            return end($exploded);
        }, $pluginsInComposer);

        $pluginsNotInComposer = array_diff($plugins, $pluginsInComposer);

        return [$plugins, $pluginsInComposer, $pluginsNotInComposer];
    }

    public function getPackage(mixed $plugin): ?string
    {
        if ($plugin === 'aky-gdpr' || $plugin === 'aky-gdpr-master') {
            $pluginPackage = 'akyoscommunication/aky-gdpr';
        } elseif ($plugin === 'akyos-updates') {
            $pluginPackage = 'akyos/akyos-updates';
        } elseif ($plugin === 'advanced-custom-fields' || $plugin === 'advanced-custom-fields-pro') {
            $pluginPackage = 'wpengine/advanced-custom-fields-pro';
        } elseif ($plugin === 'wpmudev-updates') {
            $pluginPackage = 'wpmudev/';
        } else {
            $pluginPackage = null;
        }

        if (!$pluginPackage) {
            $pluginPackage = $this->packageExistsOnWPPackagist($plugin) ? 'wpackagist-plugin/' . $plugin : null;
        }

        if (!$pluginPackage) {
            $pluginPackage = $this->packageExistsOnWPMU($plugin) ? 'wpmudev/' . $plugin : null;
        }

        return $pluginPackage;
    }

    public function isBedrock(): bool
    {
        return file_exists(ABSPATH . 'wp-load.php');
    }

    public function getBedrockRoot(): string
    {
        return dirname(ABSPATH, 2);
    }
}
