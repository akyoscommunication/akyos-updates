<?php

namespace AkyosUpdates\Service\WPMU;

use AkyosUpdates\Attribute\Hook;
use AkyosUpdates\Trait\ServiceTrait;
use Hummingbird\Core\Utils;

class HummingbirdOptionsService
{
	use ServiceTrait;

	public function __construct()
	{
		$this->redirectRoute = 'akyos_updates_hummingbird_options';
	}

	/**
	 * @throws \JsonException
	 */
	public function getSettings()
	{
		$module = Utils::get_module('page_cache');
		$options = $module->get_options();
		$existing_settings = $module->get_settings();
		$message = '';

		if ($options['enabled']) {
			$message .= '<p>✅ Page Caching activé</p>';
		} else {
			$message .= '<p>⭕ Page Caching désactivé</p>';
		}

		$message .= '<ul>';

		if ($options['preload']) {
			$message .= '<li><p>✅ Page Caching : Préchargement activé</p></li>';
		} else {
			$message .= '<li><p>⭕ Page Caching : Préchargement désactivé</p></li>';
		}

		if ($options['integrations']['varnish']) {
			$message .= '<li><p>✅ Page Caching : Varnish activé</p></li>';
		} else {
			$message .= '<li><p>⭕ Page Caching : Varnish désactivé</p></li>';
		}


		if ($existing_settings['clear_interval']['enabled']) {
			$message .= '<li><p>✅ Page Caching : Clear Interval activé</p></li>';
		} else {
			$message .= '<li><p>⭕ Page Caching : Clear Interval désactivé</p></li>';
		}

		if ($existing_settings['settings']['clear_update']) {
			$message .= '<li><p>✅ Page Caching : Clear full cache when post/page is updated activé</p></li>';
		} else {
			$message .= '<li><p>⭕ Page Caching : Clear full cache when post/page is updated désactivé</p></li>';
		}

		$message .= '</ul>';

		return [
			'message' => $message,
			'action_required' => true
		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_set_settings')]
	public function setSettings()
	{
		$module = Utils::get_module('page_cache');
		$options = $module->get_options();
		$existing_settings = $module->get_settings();

		$options['enabled'] = true;
		$options['preload'] = true;
		$options['integrations']['varnish'] = '1';
		$existing_settings['clear_interval']['enabled'] = true;
		$existing_settings['settings']['clear_update'] = 1;

		$module->update_options($options);
		$module->save_settings($existing_settings);


		return wp_redirect(admin_url('admin.php?page='.$this->redirectRoute));
	}
}
