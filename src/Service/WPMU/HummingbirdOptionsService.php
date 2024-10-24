<?php

namespace AkyosUpdates\Service\WPMU;

use AkyosUpdates\Attribute\Hook;
use AkyosUpdates\Class\AbstractController;
use AkyosUpdates\Trait\ServiceTrait;

class HummingbirdOptionsService extends AbstractController
{
	use ServiceTrait;

	public function __construct()
	{
		parent::__construct();
	}

	public function getSettings()
	{
//		wphb_settings

		$settings = get_option('wphb_settings');

		if (array_key_exists('page_cache', $settings)) {
			
		}

		return [

		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_set_settings')]
	public function setSettings()
	{

	}
}
