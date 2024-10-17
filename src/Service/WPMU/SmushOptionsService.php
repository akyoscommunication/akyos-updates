<?php

namespace AkyosUpdates\Service\WPMU;

use AkyosUpdates\Attribute\Hook;
use AkyosUpdates\Traits\ServiceTrait;

class SmushOptionsService
{
	use ServiceTrait;

	public function __construct()
	{
		$this->redirectRoute = 'akyos_updates_smush_options';

	}

	public function getConfigSmush()
	{
		$smush = get_option('wp-smush-settings');

		$message[0] = '<p>⭕ Le Lazy load n\'est pas activé</p>';
		$message[1] = '<p>⭕ Les options d\'intégrations ne sont pas activées</p>';
		$message[2] = '<p>⭕ La configuration Bulk Smush est incomplète</p>';
		$message[3] = '<p>⭕ La configuration WebP est incomplète</p>';

		if ($smush['lazy_load'] && get_option('wp-smush-lazy_load')) {
			$message[0] = '<p>✅ Le Lazy load est activé</p>';
		}

		// TODO : Revoir pour ça
		if ($smush['gutenberg']) {
			$message[1] = '<p>✅ Les options d\'intégrations sont activées</p>';
		}

		if ($smush['lossy'] && $smush['resize'] && $smush['original'] && $smush['png_to_jpg'] && $smush['webp'] && $smush['keep_data'] && get_option('wp-smush-resize_sizes')) {
			$message[2] = '<p>✅ La configuration Bulk Smush est complète</p>';
		}

		// TODO : Revoir pour ça
		if ($smush['webp'] && $smush['webp_mod'] && $smush['webp_direct_conversion'] && $smush['strip_exif']) {
			$message[3] = '<p>✅ La configuration WebP est complète</p>';
		}

		return [
			'message' => implode('<br>', $message),
			'action_required' => !$smush['lazy_load'] || !$smush['gutenberg'] || !$smush['lossy'] || !get_option('wp-smush-resize_sizes') || !$smush['resize'] || !$smush['original'] || !$smush['png_to_jpg'] || !$smush['webp'] || !$smush['keep_data'] || !$smush['webp_mod'] || !$smush['webp_direct_conversion'] || !$smush['strip_exif']
		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_set_smush_options')]
	public function setConfigSmush()
	{
		$config_smush = get_option('wp-smush-settings');
		$config_smush['lazy_load'] = true;
		$config_smush['gutenberg'] = true;
		$config_smush['lossy'] = "2";
		$config_smush['resize'] = true;
		$config_smush['original'] = true;
		$config_smush['png_to_jpg'] = true;
		$config_smush['webp'] = true;
		$config_smush['keep_data'] = true;
		$config_smush['webp_mod'] = true;
		$config_smush['webp_direct_conversion'] = true;
		$config_smush['strip_exif'] = true;


		update_option('wp-smush-settings', $config_smush);
		delete_option('wp-smush-lazy_load');
		add_option('wp-smush-lazy_load', [
			"format" => [
				"jpeg" => true,
				"png" => true,
				"webp" => true,
				"gif" => true,
				"svg" => true,
				"iframe" => true
			],
			"output" => [
				"content" => true,
				"widgets" => true,
				"thumbnails" => true,
				"gravatars" => true
			],
			"animation" => [
				"selected" => "fadein",
				"fadein" => [
					"duration" => 400,
					"delay" => 0
				],
				"spinner" => [
					"selected" => 1,
					"custom" => []
				],
				"placeholder" => [
					"selected" => 1,
					"custom" => [],
					"color" => "#F3F3F3"
				]
			],
			"include" => [
				"frontpage" => true,
				"home" => true,
				"page" => true,
				"single" => true,
				"archive" => true,
				"category" => true,
				"tag" => true
			],
			"exclude-pages" => [],
			"exclude-classes" => [],
			"footer" => true,
			"native" => false,
			"noscript" => false
		]);

		delete_option('wp-smush-resize_sizes');
		add_option('wp-smush-resize_sizes', [
			"width" => 1920,
			"height" => 1920
		]);

		return wp_redirect(admin_url('admin.php?page='.$this->redirectRoute));
	}

	public function getBulkSmush()
	{
		return [
			'message' => '<p>Appuyez sur le bouton pour lancer le Bulk Smush en tâche de fond, si ca ne fonctionne pas clique <a href="'.admin_url('admin.php').'?page=smush-bulk">Ici</a></p>',
			'action_required' => true
		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_exec_bulk_smush')]
	public function execBulkSmush()
	{
		try {
			shell_exec('wp smush compress');
		} catch (\Exception $e) {
			dd($e->getMessage());
		}

		return wp_redirect(admin_url('admin.php?page='.$this->redirectRoute));
	}
}
