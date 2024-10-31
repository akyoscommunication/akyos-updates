<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Attribute\AdminRoute;
use AkyosUpdates\Class\AbstractController;
use AkyosUpdates\Service\RapportService;

class RapportController extends AbstractController
{
	public function __construct(
		private readonly RapportService $rapportService
	)
	{
		parent::__construct();
	}

	#[AdminRoute(type: AdminRoute::TYPE_SUBMENU_PAGE, pageTitle: 'Rapports', menuTitle: 'Rapports', capability: 'manage_options', slug: 'akyos_updates_rapport', parentSlug: 'akyos_updates', position: 99)]
	public function index(): void
	{
		$this->render('rapport.html.twig', [
			'comments' => $this->rapportService->getComments(),
			'page_speed' => $this->rapportService->pageSpeed()
		]);
	}
}
