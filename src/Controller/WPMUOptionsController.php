<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Attribute\AdminRoute;
use AkyosUpdates\Class\AbstractController;
use AkyosUpdates\Service\WPMU\BrandaOptionsService;
use AkyosUpdates\Service\WPMU\DefenderOptionsService;
use AkyosUpdates\Service\WPMU\HummingbirdOptionsService;
use AkyosUpdates\Service\WPMU\SeoOptionsService;
use AkyosUpdates\Service\WPMU\SmushOptionsService;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class WPMUOptionsController extends AbstractController
{
	public function __construct(
		private readonly BrandaOptionsService      $brandaOptionsService,
		private readonly SmushOptionsService       $smushOptionsService,
		private readonly HummingbirdOptionsService $hummingbirdOptionsService,
		private readonly SeoOptionsService         $seoOptionsService,
		private readonly DefenderOptionsService    $defenderOptionsService
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
			'branda_admin_lite' => $this->brandaOptionsService->getAdminLite()
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

	/**
	 * @throws \JsonException
	 */
	#[AdminRoute(type: AdminRoute::TYPE_SUBMENU_PAGE, pageTitle: 'WPMU Hummingbird', menuTitle: 'WPMU Hummingbird', capability: 'manage_options', slug: 'akyos_updates_hummingbird_options', parentSlug: 'akyos_updates', position: 6)]
	public function wpmuHummingbird(): void
	{
		$this->render('hummingbird_options.html.twig', [
			'settings' => $this->hummingbirdOptionsService->getSettings(),
			'gzip' => $this->hummingbirdOptionsService->getGzip(),
			'assets_optimization' => $this->hummingbirdOptionsService->getAssetsOptimization(),
			'advanced_tools' => $this->hummingbirdOptionsService->getAdvancedTools(),
		]);
	}

	/**
	 * @throws \JsonException
	 */
	#[AdminRoute(type: AdminRoute::TYPE_SUBMENU_PAGE, pageTitle: 'WPMU Defender', menuTitle: 'WPMU Defender', capability: 'manage_options', slug: 'akyos_updates_defender_options', parentSlug: 'akyos_updates', position: 7)]
	public function wpmuDefender(): void
	{
		$this->render('defender_options.html.twig', [
			'translate' => $this->defenderOptionsService->getTranslate(),
			'blocker_ip' => $this->defenderOptionsService->getGlobalIP(),
			'mask_login' => $this->defenderOptionsService->getMaskLogin(),
			'security_headers' => $this->defenderOptionsService->getSecurityHeaders(),
			'pwned_passwords' => $this->defenderOptionsService->getPwnedPassword(),
			'recaptcha' => $this->defenderOptionsService->recaptcha()
		]);
	}

	#[AdminRoute(type: AdminRoute::TYPE_SUBMENU_PAGE, pageTitle: 'WPMU SmartCrawl\Yoast SEO', menuTitle: 'WPMU SmartCrawl\Yoast SEO', capability: 'manage_options', slug: 'akyos_updates_seo_options', parentSlug: 'akyos_updates', position: 8)]
	public function wpmuSeo(): void
	{
		$this->seoOptionsService->configSmartCrawl();

		$this->render('seo_options.html.twig', [
			'pages' => $this->seoOptionsService->getPages(),
			'seo_plugins' => $this->seoOptionsService->getSEOPluginsInstalled(),
			'schema_advanced' => $this->seoOptionsService->schemaAdvanced(),
		]);
	}
}
