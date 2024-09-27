<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Attribute\AdminRoute;
use AkyosUpdates\Class\AbstractController;
use AkyosUpdates\Service\GenericWPOptionsService;

class GenericWPOptionsController extends AbstractController
{
    public function __construct(
        private readonly GenericWPOptionsService $generalConfigsService,
    ) {
        parent::__construct();
    }

    #[AdminRoute(type: AdminRoute::TYPE_SUBMENU_PAGE, pageTitle: 'Options générales Wordpress', menuTitle: 'Options WordPress', capability: 'manage_options', slug: 'akyos_updates_generic_wp_options', parentSlug: 'akyos_updates', position: 3)]
    public function genericWPOptions(): void
    {
        $this->render('generic_wp_options.html.twig', [
            'comments_disabled' => $this->generalConfigsService->getCommentsStatus(),
            'mail_publisher' => $this->generalConfigsService->getMailPublisherStatus(),
        ]);
    }
}