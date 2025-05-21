<?php

namespace AkyosUpdates\Controller;


use AkyosUpdates\Attribute\AdminRoute;
use AkyosUpdates\Class\AbstractController;
use AkyosUpdates\Service\Plugins\PluginComposerService;
use AkyosUpdates\Service\Plugins\PluginGitignoreService;

class PluginsController extends AbstractController
{
	public function __construct(
		private readonly PluginComposerService  $pluginsComposerService,
		private readonly PluginGitignoreService $pluginsGitignoreService,
	)
	{
		parent::__construct();
	}

	#[AdminRoute(type: AdminRoute::TYPE_SUBMENU_PAGE, pageTitle: 'Plugins', menuTitle: 'Plugins', capability: 'manage_options', slug: 'akyos_updates_plugins', parentSlug: 'akyos_updates', position: 3)]
	public function plugins(): void
	{
		$this->render('plugins.html.twig', [
			'composer_configuration' => $this->pluginsComposerService->getComposerConfiguration(),
			'composer_plugins' => $this->pluginsComposerService->getComposerPlugins(),
			'composer_update' => $this->pluginsComposerService->shouldRunComposerUpdate(),
			'gitignore_file' => $this->pluginsGitignoreService->getGitignoreFile(),
			'gitignore_configurations' => $this->pluginsGitignoreService->getGitIgnoreConfigurations(),
		]);
	}
}
