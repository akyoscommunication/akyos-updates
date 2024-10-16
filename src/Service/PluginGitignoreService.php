<?php

namespace AkyosUpdates\Service;

use AkyosUpdates\Attribute\Hook;

readonly class PluginGitignoreService
{
	public function __construct(
		private PluginComposerService $hostingComposerService,
	)
	{
	}

	public function getGitignoreFile(): array
	{
		$gitignore = file_exists(ABSPATH.'.gitignore');

		$gitIgnoreMessage = '';

		if ($gitignore) {
			$gitIgnoreMessage = "<p>✅ Le fichier .gitignore est présent à la racine du site </p><br>";
		} else {
			$gitIgnoreMessage = "<p>⭕ Le fichier .gitignore n'est pas présent à la racine du site </p><br>";
		}

		return [
			'message' => $gitIgnoreMessage,
			'action_required' => !$gitignore
		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_add_gitignore')]
	public function addGitignore(): bool
	{
		$gitignore = file_exists(ABSPATH.'.gitignore');

		if (!$gitignore) {
			copy(plugin_dir_path(__DIR__).'../defaultFiles/default_gitignore.txt', ABSPATH.'.gitignore');
		}

		return wp_redirect(admin_url('admin.php?page=akyos_updates_hosting'));
	}

	public function getGitIgnoreConfigurations(): array
	{
		$gitignore = file_exists(ABSPATH.'.gitignore');

		$gitIgnoreMessage = '';
		[$notInGitignore, $gitignoreContent] = $this->getNotInGitignore($gitignore);

		if (count($notInGitignore) === 0) {
			$gitIgnoreMessage = "<p>✅ Tous les éléments sont présents dans le fichier .gitignore</p>";
		} else {
			$gitIgnoreMessage = "<p>⭕ Ces éléments ne sont pas présents dans le fichier .gitignore : </p><br> <ul>";
			foreach ($notInGitignore as $element) {
				$gitIgnoreMessage .= "<li>$element</li>";
			}
			$gitIgnoreMessage .= "</ul>";
		}

		return [
			'message' => $gitIgnoreMessage,
			'action_required' => count($notInGitignore)
		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_add_gitignore_configurations')]
	public function addGitignoreConfigurations(): bool
	{
		$gitignore = file_exists(ABSPATH.'.gitignore');

		[$notInGitignore, $gitignoreContent] = $this->getNotInGitignore($gitignore);

		if (count($notInGitignore) > 0) {
			$gitignoreContent .= "\n".implode("\n", $notInGitignore);
			file_put_contents(ABSPATH.'.gitignore', $gitignoreContent);
		}

		return wp_redirect(admin_url('admin.php?page=akyos_updates_hosting'));
	}

	private function getNotInGitignore(bool $gitignore): array
	{
		$gitignoreContent = '';
		$notInGitignore = [];

		if ($gitignore) {
			$gitignoreContent = file_get_contents(ABSPATH.'.gitignore');
			$lines = explode("\n", $gitignoreContent);

			if (!in_array('.idea', $lines)) {
				$notInGitignore[] = '.idea';
			}
			if (!in_array('.DS_Store', $lines)) {
				$notInGitignore[] = '.DS_Store';
			}
			if (!in_array('wp-content/plugins/', $lines)) {
				$notInGitignore[] = 'wp-content/plugins/';
			}

			[$plugins, $pluginsInComposer, $pluginsNotInComposer] = $this->hostingComposerService->getPlugins();

			foreach ($pluginsNotInComposer as $plugin) {
				$pluginPackage = $this->hostingComposerService->getPackage($plugin);

				if (!$pluginPackage) {
					if (!in_array('!wp-content/plugins/'.$plugin.'/', $lines)) {
						$notInGitignore[] = '!wp-content/plugins/'.$plugin.'/';
					}
				}
			}
		}

		return [$notInGitignore, $gitignoreContent];
	}
}
