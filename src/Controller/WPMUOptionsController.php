<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Attribute\AdminRoute;
use AkyosUpdates\Class\AbstractController;
use AkyosUpdates\Service\WPMU\BrandaOptionsService;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class WPMUOptionsController extends AbstractController
{
	public function __construct(
		private readonly BrandaOptionsService $brandaOptionsService,
	)
	{
		parent::__construct();
	}

	/**
	 * @throws TransportExceptionInterface
	 */
	#[AdminRoute(type: AdminRoute::TYPE_SUBMENU_PAGE, pageTitle: 'Options WPMU', menuTitle: 'Options WPMU', capability: 'manage_options', slug: 'akyos_updates_wpmu_options', parentSlug: 'akyos_updates', position: 4)]
	public function wpmuOptions(): void
	{
		$this->render('wpmu_options.html.twig', [
			'branda_admin_message' => $this->brandaOptionsService->getBrandaAdminMessage(),
			'branda_widgets' => $this->brandaOptionsService->getBrandaWidgets(),
		]);
	}
}
