<?php

namespace AkyosUpdates\Service\WPMU;

use AkyosUpdates\Attribute\Hook;
use Symfony\Component\HttpFoundation\Request;

class SeoOptionsService
{
	public function getPages(): array
	{
		// get pages
		$pages = get_pages();
		$datas = [];

		foreach ($pages as $key => $page) {
			$noindex = get_post_meta($page->ID, $this->getMetaKeys()['noindex'], true);
			$meta_title = get_post_meta($page->ID, $this->getMetaKeys()['title'], true) ?: $page->post_title.' | '.get_bloginfo('name');
			$meta_description = get_post_meta($page->ID, $this->getMetaKeys()['description'], true);

			if ($noindex) {
				$action = true;
				$message = '<p>⭕ '.$page->post_title.'</p>';
			} else {
				$action = false;
				$message = '<p>✅ '.$page->post_title.'</p>';
				$message .= '<ul>';
				$message .= '<li><em>Meta title: <strong>'.$meta_title.'</strong></em></li>';
				$message .= '<li><em>Meta description: <strong>'.$meta_description.'</strong></em></li>';
				$message .= '</ul>';
			}

			$datas[] = [
				'message' => $message,
				'action_required' => $action,
				'reverse_action' => !$action,
				'ajax' => [
					'hook' => $action ? 'admin_post_akyos_updates_index_page' : 'admin_post_akyos_updates_noindex_page'
				],
				'fields' => [
					[
						'label' => 'page_id',
						'name' => 'page_id',
						'type' => 'hidden',
						'value' => $page->ID
					]
				]
			];
		}

		return $datas;
	}

	#[Hook(hook: 'admin_post_akyos_updates_index_page')]
	public function indexPage()
	{
		$request = Request::createFromGlobals();
		$page_id = $request->request->get('page_id');

		foreach ($this->getMetaKeys() as $metaKey) {
			delete_post_meta($page_id, $metaKey);
		}
	}

	#[Hook(hook: 'admin_post_akyos_updates_noindex_page')]
	public function noindexPage()
	{
		$request = Request::createFromGlobals();
		$page_id = $request->request->get('page_id');

		foreach ($this->getMetaKeys() as $metaKey) {
			add_post_meta($page_id, $metaKey, '1');
		}
	}

	public function configSmartCrawl()
	{
		$pluginList = get_plugins();

		if (array_key_exists('wpmu-dev-seo/wpmu-dev-seo.php', $pluginList)) {
			$settings = get_option('wds_sitemap_options');
			$settings['sitemap-disable-automatic-regeneration'] = 'auto';

			update_option('wds_sitemap_options', $settings);
		}
	}

	public function schemaAdvanced(): array
	{
		return [
			'message' => '<p>Schéma > Avancé : sélectionnez les pages correspondantes dans les prmeiers champs puis désactiver le balisage pour les post type et contenus inutiles + la page recherche s\'il n\'y en a pas</p>',
			'action_required' => true,
		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_schema_advanced')]
	public function goToSchemaAdvanced()
	{
		return wp_redirect(admin_url('admin.php?page=wds_schema&tab=tab_advanced'));
	}

	public function getSEOPluginsInstalled()
	{
		$pluginList = get_plugins();
		$seoPlugins = [];

		if (array_key_exists('wpmu-dev-seo/wpmu-dev-seo.php', $pluginList)) {
			$seoPlugins[] = 'SmartCrawl';
		}

		if (array_key_exists('wordpress-seo/wp-seo.php', $pluginList)) {
			$seoPlugins[] = 'Yoast SEO';
		}

		$message = '<ul>';
		foreach ($seoPlugins as $seoPlugin) {
			$message .= '<li><p>'.$seoPlugin.'</p></li>';
		}
		$message .= '</ul>';

		return [
			'message' => $message,
			'action_required' => false,
		];
	}

	public function getMetaKeys(): array
	{
		$pluginList = get_plugins();
		$metaKeys = [
			'noindex' => '',
			'nofollow' => ''
		];

		if (array_key_exists('wpmu-dev-seo/wpmu-dev-seo.php', $pluginList)) {
			$metaKeys = [
				'noindex' => '_wds_meta-robots-noindex',
				'nofollow' => '_wds_meta-robots-nofollow',
				'title' => '_wds_title',
				'description' => '_wds_metadesc'
			];

		} elseif (array_key_exists('wordpress-seo/wp-seo.php', $pluginList)) {
			$metaKeys = [
				'noindex' => '_yoast_wpseo_meta-robots-noindex',
				'nofollow' => '_yoast_wpseo_meta-robots-nofollow',
				'title' => '_yoast_wpseo_title',
				'description' => '_yoast_wpseo_metadesc'
			];
		}

		return $metaKeys;
	}
}
