<?php

namespace AkyosUpdates\Service\OptionsWP;

use AkyosUpdates\Attribute\Hook;
use AkyosUpdates\Trait\ServiceTrait;

class FaviconService
{
	use ServiceTrait;

	public function __construct()
	{
		$this->redirectRoute = 'akyos_updates_generic_wp_options';
	}

	public function getFavicon()
	{
		$favicon = get_site_icon_url();

		if (!$favicon) {
			$message = '<p>⭕ Aucune favicon n\'a été définie pour ce site.</p>';
		} else {
			$message = '<p>✅ La favicon actuelle est : </p><img width="50" height="50" src="'.$favicon.'" alt="favicon">';
		}

		return [
			'message' => $message,
			'action_required' => true,
			'fields' => [
				[
					'name' => 'favicon',
					'label' => null,
					'type' => 'file',
					'accept' => 'image/png, image/jpeg'
				]
			]
		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_change_favicon')]
	public function changeFavicon()
	{
		if (!empty($_FILES['favicon']) && $_FILES['favicon']['error'] === 0) {
			$attachment_id = media_handle_upload('favicon', 0);
			if (is_wp_error($attachment_id)) {
				wp_die('Erreur lors de l\'upload de la favicon : '.$attachment_id->get_error_message());
			}

			update_option('site_icon', $attachment_id);
		}

		return wp_redirect(admin_url('admin.php?page='.$this->redirectRoute));
	}
}
