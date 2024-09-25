<?php

namespace AkyosUpdates\Controller;


use AkyosUpdates\Attribute\Route;
use AkyosUpdates\Class\AbstractController;
use AkyosUpdates\Service\HostingService;

class HostingController extends AbstractController
{
    public function __construct(
        private readonly HostingService $hostingService,
    ) {
        parent::__construct();
    }

    #[Route(type: Route::TYPE_SUBMENU_PAGE, pageTitle: 'Configuration de l\'hébergement', menuTitle: 'Hébergement', capability: 'manage_options', slug: 'akyos_updates_hosting', parentSlug: 'akyos_updates', position: 1)]
    public function genericWPOptions(): void
    {
        $this->render('hosting.html.twig', [
            'php_version' => $this->hostingService->getPhpVersion(),
            'composer_configuration' => $this->hostingService->getComposerConfiguration(),
            'composer_plugins' => $this->hostingService->getComposerPlugins(),
            'composer_udapte' => $this->hostingService->shouldRunComposerUpdate(),
        ]);
    }
}