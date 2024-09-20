<?php

namespace AkyosUpdates\Controller;


use AkyosUpdates\Attribute\Route;
use AkyosUpdates\Class\AbstractController;
use AkyosUpdates\Service\GenericWPOptionsService;

class AkyosUpdatesController extends AbstractController
{
    #[Route(type: Route::TYPE_MENU_PAGE, pageTitle: 'Akyos Updates', menuTitle: 'Akyos Updates', capability: 'manage_options', slug: 'akyos_updates', iconUrl: 'dashicons-superhero', position: 99)]
    public function index(): void
    {
        $this->render('akyos_updates.html.twig');
    }
}