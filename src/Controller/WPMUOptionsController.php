<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Attribute\AdminRoute;
use AkyosUpdates\Class\AbstractController;
use AkyosUpdates\Service\GenericWPOptionsService;
use AkyosUpdates\Service\WPMUOptionsService;

class WPMUOptionsController extends AbstractController
{
    public function __construct(
        private readonly WPMUOptionsService $WPMUOptionsService,
    ) {
        parent::__construct();
    }

    #[AdminRoute(type: AdminRoute::TYPE_SUBMENU_PAGE, pageTitle: 'Options WPMU', menuTitle: 'Options WPMU', capability: 'manage_options', slug: 'akyos_updates_wpmu_options', parentSlug: 'akyos_updates', position: 4)]
    public function wpmuOptions(): void
    {
        $this->render('wpmu_options.html.twig', [
        ]);
    }
}