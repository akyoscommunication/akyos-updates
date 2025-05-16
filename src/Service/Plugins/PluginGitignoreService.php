<?php

namespace AkyosUpdates\Service\Plugins;

use AkyosUpdates\Attribute\Hook;
use AkyosUpdates\Trait\ServiceTrait;

class PluginGitignoreService
{
	use ServiceTrait;

	public function __construct(
		private PluginComposerService $hostingComposerService,
	)
	{
		$this->redirectRoute = 'akyos_updates_plugins';
	}

	public function getGitignoreFile(): array
	{

		if ($this->hostingComposerService->isBedrock()) {
			$gitignorePath = $this->hostingComposerService->getBedrockRoot().'/.gitignore';
		} else {
			$gitignorePath = ABSPATH.'.gitignore';
		}

		$gitIgnoreMessage = '';

		if (file_exists($gitignorePath)) {
			$gitIgnoreMessage = "<p>✅ Le fichier .gitignore est présent à la racine du site </p><br>";
            $gitignoreData = file_get_contents($gitignorePath);
            $gitignore = explode("\n", $gitignoreData);

            $gitIgnoreMessage .= "<pre><code>" . $gitignoreData . "</code></pre><br/>";
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
        if ($this->hostingComposerService->isBedrock()) {
            $gitignorePath = $this->hostingComposerService->getBedrockRoot().'/.gitignore';
        } else {
            $gitignorePath = ABSPATH.'.gitignore';
        }

		if (!file_exists($gitignorePath)) {
			copy(plugin_dir_path(__DIR__).'../defaultFiles/default_gitignore.txt', ABSPATH.'.gitignore');
		}

		return wp_redirect(admin_url('admin.php?page='.$this->redirectRoute));
	}

	public function getGitIgnoreConfigurations(): array
	{
        if ($this->hostingComposerService->isBedrock()) {
            $gitignorePath = $this->hostingComposerService->getBedrockRoot().'/.gitignore';
        } else {
            $gitignorePath = ABSPATH.'.gitignore';
        }

		$gitIgnoreMessage = '';
		[$notInGitignore, $gitignoreContent] = $this->getNotInGitignore(file_exists($gitignorePath));


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
        if ($this->hostingComposerService->isBedrock()) {
            $gitignorePath = $this->hostingComposerService->getBedrockRoot().'/.gitignore';
        } else {
            $gitignorePath = ABSPATH.'.gitignore';
        }

		[$notInGitignore, $gitignoreContent] = $this->getNotInGitignore(file_exists($gitignorePath));

		if (count($notInGitignore) > 0) {
			$gitignoreContent .= "\n".implode("\n", $notInGitignore);
			file_put_contents(ABSPATH.'.gitignore', $gitignoreContent);
		}

		return wp_redirect(admin_url('admin.php?page='.$this->redirectRoute));
	}

	private function getNotInGitignore(bool $gitignore): array
	{
		$gitignoreContent = '';
		$notInGitignore = [];

		if ($gitignore) {

            if ($this->hostingComposerService->isBedrock()) {
                $gitignorePath = $this->hostingComposerService->getBedrockRoot().'/.gitignore';
            } else {
                $gitignorePath = ABSPATH.'.gitignore';
            }

			$gitignoreContent = file_get_contents($gitignorePath);
			$lines = explode("\n", $gitignoreContent);

			if (!in_array('.idea', $lines)) {
				$notInGitignore[] = '.idea';
			}
			if (!in_array('.DS_Store', $lines)) {
				$notInGitignore[] = '.DS_Store';
			}
		}

		return [$notInGitignore, $gitignoreContent];
	}
}
