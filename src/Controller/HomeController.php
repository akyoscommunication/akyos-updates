<?php

namespace AkyosUpdates\Controller;


use AkyosUpdates\Attribute\Route;
use AkyosUpdates\Class\AbstractController;
use AkyosUpdates\Service\GeneralConfigsService;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly GeneralConfigsService $generalConfigsService
    )
    {
        parent::__construct();
    }

    #[Route(type: Route::TYPE_MENU_PAGE, pageTitle: 'Akyos Updates', menuTitle: 'Akyos Updates', capability: 'manage_options', slug: AKYOS_UPDATES_NAME, iconUrl: 'dashicons-superhero', position: 99)]
    public function index(): void
    {
        $this->render('akyos-updates.html.twig', [
            'php_version' => $this->generalConfigsService->getPhpVersion(),
            'comments_disabled' => $this->generalConfigsService->getCommentsStatus(),
            'mail_publisher' => $this->generalConfigsService->getMailPublisherStatus(),
            'admin_url' => admin_url('admin-post.php'),
        ]);
    }
}