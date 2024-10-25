<?php

namespace AkyosUpdates\Service\Plugins;

use AkyosUpdates\Attribute\Hook;
use AkyosUpdates\Trait\ServiceTrait;

class PluginComposerService
{
	use ServiceTrait;


	private array $wpmuPluginsComposer;

	/**
	 * @throws \JsonException
	 */
	public function __construct()
	{
		$this->wpmuPluginsComposer = json_decode(file_get_contents('https://raw.githubusercontent.com/josephfusco/wpmudev-plugins/refs/heads/master/composer.json'), true, 512, JSON_THROW_ON_ERROR);

		$this->redirectRoute = 'akyos_updates_plugins';
	}

	public function getComposerConfiguration(): array
	{
		$composerJson = file_exists(ABSPATH.'composer.json');
		$authJson = file_exists(ABSPATH.'auth.json');

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
		$composerJson = file_exists(ABSPATH.'composer.json');
		$authJson = file_exists(ABSPATH.'auth.json');

		if (!$composerJson) {
			copy(plugin_dir_path(__DIR__).'../defaultFiles/default_composer.txt', ABSPATH.'composer.json');
		}

		if (!$authJson) {
			copy(plugin_dir_path(__DIR__).'../defaultFiles/default_auth.txt', ABSPATH.'auth.json');
		}
	}

	public function getComposerPlugins(): array
	{
		$composerJson = file_exists(ABSPATH.'composer.json');

		$composerPlugins = [];
		$composerPluginMessage = '';

		if ($composerJson) {
			[$plugins, $pluginsInComposer, $pluginsNotInComposer] = $this->getPlugins();

			$composerPluginMessage = 'Plugins dans le fichier composer.json : <br>';
			$composerPluginMessage .= '<ul>';
			foreach ($pluginsInComposer as $plugin) {
				$composerPluginMessage .= '<li><p>✅ '.$plugin.' </p></li>';
			}
			$composerPluginMessage .= '</ul>';
			$composerPluginMessage .= '<br>';

			if (!empty($pluginsNotInComposer)) {
				$composerPluginMessage .= '<p>Plugins installés sur le site qui ne sont pas dans le fichier composer.json : </p><br>';
			}

			foreach ($pluginsNotInComposer as $plugin) {
				$pluginPackage = $this->getPackage($plugin);

				if (!$pluginPackage) {
					$composerPlugins[] = '<p>🔴 '.$plugin.' - Impossible de trouver un repository public pour le plugin. </p><br>';
				} else {
					$composerPlugins[] = '<p>🟠 '.$pluginPackage.' </p><br>';
				}
			}
		}

		return [
			'message' => $composerPluginMessage.'<br>'.implode('<br>', $composerPlugins),
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
		$composerJson = file_exists(ABSPATH.'composer.json');
		$composerData = json_decode(file_get_contents(ABSPATH.'composer.json'), true, 512, JSON_THROW_ON_ERROR);

		$composerPlugins = [];

		if ($composerJson) {
			[$plugins, $pluginsInComposer, $pluginsNotInComposer] = $this->getPlugins();

			foreach ($pluginsNotInComposer as $plugin) {
				$pluginPackage = $this->getPackage($plugin);

				if ($pluginPackage) {
					$composerPlugins[] = $pluginPackage;
				}
			}

			foreach ($composerPlugins as $plugin) {
				if ($plugin === 'akyoscommunication/aky-gdpr') {
					$composerData['require'][$plugin] = 'dev-master';
				} else {
					$composerData['require'][$plugin] = '*';
				}
			}

			file_put_contents(ABSPATH.'composer.json', json_encode($composerData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
		}
	}

	private function packageExistsOnWPPackagist($pluginSlug): bool
	{
		$url = 'https://api.wordpress.org/plugins/info/1.0/'.$pluginSlug;
		$headers = @get_headers($url);
		return $headers && str_contains($headers[0], '200');
	}

	private function packageExistsOnWPMU($package): bool
	{
		$pluginsInWPMUComposer = array_keys($this->wpmuPluginsComposer['require']);
		$pluginsInWPMUComposer = array_map(function ($plugin) {
			$exploded = explode('/', $plugin);
			return end($exploded);
		}, $pluginsInWPMUComposer);
		return in_array($package, $pluginsInWPMUComposer);
	}

	public function shouldRunComposerUpdate(): array
	{
		$composerMessage = '<p>Mettre les plugins à jour</p>';

		return [
			'message' => $composerMessage,
			'action_required' => true
		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_run_composer_update')]
	public function runComposerUpdate(): bool
	{
		shell_exec('cd .. && composer update');

		return wp_redirect(admin_url('admin.php?page='.$this->redirectRoute));
	}

	/**
	 * @throws \JsonException
	 */
	public function getPlugins(): array
	{
		$plugins = array_filter(glob(ABSPATH.'wp-content/plugins/*'), 'is_dir');
		$plugins = array_map('basename', $plugins);
		$plugins = array_map(static function ($plugin) {
			$plugin = $plugin === 'aky-gdpr-master' ? 'aky-gdpr' : $plugin;
			$plugin = $plugin === 'advanced-custom-fields' ? 'advanced-custom-fields-pro' : $plugin;
			return $plugin === 'wpmudev-updates' ? 'wpmu-dev-dashboard' : $plugin;
		}, $plugins);

		// Find packages in composer.json require section
		$pluginsInComposer = [];
		$composerJson = file_get_contents(ABSPATH.'composer.json');
		$composerData = json_decode($composerJson, true, 512, JSON_THROW_ON_ERROR);

		if (isset($composerData['require'])) {
			$pluginsInComposer = array_keys($composerData['require']);
		}

		$pluginsInComposer = array_filter($pluginsInComposer, static function ($plugin) {
			return !in_array($plugin, ['php', 'composer/installers']);
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
			$pluginPackage = $this->packageExistsOnWPPackagist($plugin) ? 'wpackagist-plugin/'.$plugin : null;
		}

		if (!$pluginPackage) {
			$pluginPackage = $this->packageExistsOnWPMU($plugin) ? 'wpmudev/'.$plugin : null;
		}

		return $pluginPackage;
	}
}
