<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Attribute\AdminRoute;
use AkyosUpdates\Class\AbstractController;
use AkyosUpdates\Service\WPMU\BrandaOptionsService;
use AkyosUpdates\Service\WPMU\HummingbirdOptionsService;
use AkyosUpdates\Service\WPMU\SmushOptionsService;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class WPMUOptionsController extends AbstractController
{
	public function __construct(
		private readonly BrandaOptionsService      $brandaOptionsService,
		private readonly SmushOptionsService       $smushOptionsService,
		private readonly HummingbirdOptionsService $hummingbirdOptionsService,
	)
	{
		parent::__construct();
	}

	/**
	 * @throws TransportExceptionInterface
	 */
	#[AdminRoute(type: AdminRoute::TYPE_SUBMENU_PAGE, pageTitle: 'WPMU Branda', menuTitle: 'WPMU Branda', capability: 'manage_options', slug: 'akyos_updates_branda_options', parentSlug: 'akyos_updates', position: 4)]
	public function wpmuBranda(): void
	{
		$this->render('branda_options.html.twig', [
			'branda_admin_message' => $this->brandaOptionsService->getBrandaAdminMessage(),
			'branda_widgets' => $this->brandaOptionsService->getBrandaWidgets(),
			'branda_smtp' => $this->brandaOptionsService->getBandraEmail(),
			'branda_test_email' => $this->brandaOptionsService->getBrandaTestMail(),
		]);
	}

	#[AdminRoute(type: AdminRoute::TYPE_SUBMENU_PAGE, pageTitle: 'WPMU Smush', menuTitle: 'WPMU Smush', capability: 'manage_options', slug: 'akyos_updates_smush_options', parentSlug: 'akyos_updates', position: 5)]
	public function wpmuSmush(): void
	{
		$this->render('smush_options.html.twig', [
			'smush_config' => $this->smushOptionsService->getConfigSmush(),
			'smush_bulk' => $this->smushOptionsService->getBulkSmush(),
		]);
	}

	#[AdminRoute(type: AdminRoute::TYPE_SUBMENU_PAGE, pageTitle: 'WPMU Hummingbird', menuTitle: 'WPMU Hummingbird', capability: 'manage_options', slug: 'akyos_updates_hummingbird_options', parentSlug: 'akyos_updates', position: 6)]
	public function wpmuHummingbird(): void
	{
		$this->render('branda_options.html.twig', [
			'settings' => $this->hummingbirdOptionsService->getSettings(),
		]);
	}

	#[AdminRoute(type: AdminRoute::TYPE_SUBMENU_PAGE, pageTitle: 'WPMU Defender', menuTitle: 'WPMU Defender', capability: 'manage_options', slug: 'akyos_updates_defender_options', parentSlug: 'akyos_updates', position: 7)]
	public function wpmuDefender(): void
	{
		$this->render('branda_options.html.twig', []);
	}
}
