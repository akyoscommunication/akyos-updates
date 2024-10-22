<?php

namespace AkyosUpdates\Service;

use AkyosUpdates\Attribute\Hook;
use Symfony\Component\HttpFoundation\Request;

class PageIndexService
{
	public function getPages(): array
	{
		// get pages
		$pages = get_pages();
		$datas = [];

		foreach ($pages as $key => $page) {
			$pluginList = get_plugins();
			$noindex = get_post_meta($page->ID, $this->getMetaKeys()['noindex'], true);

			if ($noindex) {
				$action = true;
				$message = '<p>⭕ '.$page->post_title.'</p>';
			} else {
				$action = false;
				$message = '<p>✅ '.$page->post_title.'</p>';
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

	public function getMetaKeys()
	{
		$pluginList = get_plugins();
		$metaKeys = [];

		if (array_key_exists('wpmu-dev-seo/wpmu-dev-seo.php', $pluginList)) {
			$metaKeys = [
				'noindex' => '_wds_meta-robots-noindex',
				'nofollow' => '_wds_meta-robots-nofollow'
			];

		} elseif (array_key_exists('wordpress-seo/wp-seo.php', $pluginList)) {
			$metaKeys = [
				'noindex' => '_yoast_wpseo_meta-robots-noindex',
				'nofollow' => '_yoast_wpseo_meta-robots-nofollow'
			];
		}

		return $metaKeys;
	}
}
