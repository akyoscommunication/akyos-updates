<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Attribute\AdminRoute;
use AkyosUpdates\Class\AbstractController;
use AkyosUpdates\Service\SEO\PageIndexService;

class PageIndexController extends AbstractController
{
	public function __construct(
		private readonly PageIndexService $pageIndexService
	)
	{
		parent::__construct();
	}

	#[AdminRoute(type: AdminRoute::TYPE_SUBMENU_PAGE, pageTitle: 'Configuration de l\'hébergement', menuTitle: 'Indexation pages', capability: 'manage_options', slug: 'akyos_updates_page_index', parentSlug: 'akyos_updates', position: 10)]
	public function pageIndex(): void
	{
		$this->render('page_index.html.twig', [
			'pages' => $this->pageIndexService->getPages(),
			'seo_plugins' => $this->pageIndexService->getSEOPluginsInstalled()
		]);
	}
}
