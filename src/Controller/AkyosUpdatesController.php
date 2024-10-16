<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Attribute\AdminRoute;
use AkyosUpdates\Class\AbstractController;

class AkyosUpdatesController extends AbstractController
{
    #[AdminRoute(type: AdminRoute::TYPE_MENU_PAGE, pageTitle: 'Akyos Updates', menuTitle: 'Akyos Updates', capability: 'manage_options', slug: 'akyos_updates', iconUrl: 'dashicons-superhero', position: 99)]
    public function index(): void
    {
        $this->render('akyos_updates.html.twig');
    }
}