<?php

namespace AkyosUpdates\Service\WPMU;

use AkyosUpdates\Attribute\Hook;
use AkyosUpdates\Trait\ServiceTrait;

class DefenderOptionsService
{
	use ServiceTrait;

	public function getTranslate()
	{
		$blacklist = get_option('wd_blacklist_lockout_settings');

		if (!$blacklist) {
			$message = '<p>⭕ Blacklist lockout désactivée</p>';
		} else {
			$message = '<p>✅ Blacklist lockout activée</p>';
		}

		dd($blacklist);

		return [
			'message' => $message,
			'action_required' => true
		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_defender_translate')]
	public function translate()
	{
		$blacklist = get_option('wd_blacklist_lockout_settings');

	}
}
