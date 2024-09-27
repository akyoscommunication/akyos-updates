<?php

namespace AkyosUpdates\Controller;


use AkyosUpdates\Attribute\AdminRoute;
use AkyosUpdates\Class\AbstractController;
use AkyosUpdates\Service\HostingComposerService;
use AkyosUpdates\Service\HostingGitignoreService;
use AkyosUpdates\Service\HostingService;

class HostingController extends AbstractController
{
    public function __construct(
        private readonly HostingService $hostingService,
        private readonly HostingComposerService $hostingComposerService,
        private readonly HostingGitignoreService $hostingGitignoreService,
    ) {
        parent::__construct();
    }

    #[AdminRoute(type: AdminRoute::TYPE_SUBMENU_PAGE, pageTitle: 'Configuration de l\'hébergement', menuTitle: 'Hébergement', capability: 'manage_options', slug: 'akyos_updates_hosting', parentSlug: 'akyos_updates', position: 1)]
    public function hosting(): void
    {
        $this->render('hosting.html.twig', [
            'php_version' => $this->hostingService->getPhpVersion(),
            'composer_configuration' => $this->hostingComposerService->getComposerConfiguration(),
            'composer_plugins' => $this->hostingComposerService->getComposerPlugins(),
            'composer_udapte' => $this->hostingComposerService->shouldRunComposerUpdate(),
            'gitignore_file' => $this->hostingGitignoreService->getGitignoreFile(),
            'gitignore_configurations' => $this->hostingGitignoreService->getGitIgnoreConfigurations(),
        ]);
    }
}