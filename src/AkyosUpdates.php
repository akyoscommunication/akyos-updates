<?php

namespace AkyosUpdates;

use AkyosUpdates\Attribute\Hook;

readonly class AkyosUpdates
{
	public function __construct()
	{
		register_activation_hook(__FILE__, [$this, 'activate']);
		register_deactivation_hook(__FILE__, [$this, 'deactivate']);
	}

	public function activate()
	{
		// Add any activation code here
	}

	public function deactivate()
	{
		// Add any deactivation code here
	}

	#[Hook(hook: 'init')]
	public function init(): void
	{
		// If this file is called directly, abort.
		if (!defined('WPINC')) {
			die;
		}
	}

	#[Hook(hook: 'plugins_loaded')]
	public function loadPluginTextdomain(): void
	{
		load_plugin_textdomain(
			'akyos-updates',
			false,
			dirname(plugin_basename(__FILE__), 2).'/languages/'
		);
	}

	#[Hook(hook: 'plugin_action_links_akyos-updates/src/akyos-updates.php')]
	public function addActionLinks($links): array
	{
		/*
		*  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
		*/
		$settings_link = array(
			'<a href="'.admin_url('options-general.php?page='.AKYOS_UPDATES_NAME).'">'.__('Settings', AKYOS_UPDATES_NAME).'</a>',
		);

		return array_merge($settings_link, $links);
	}


	#[Hook(hook: 'admin_enqueue_scripts')]
	public function enqueueAssets(): void
	{
		wp_enqueue_style(AKYOS_UPDATES_NAME, plugins_url().'/'.AKYOS_UPDATES_NAME.'/dist/css/main.css', [], AKYOS_UPDATES_VERSION, false);
		wp_enqueue_script(AKYOS_UPDATES_NAME, plugins_url().'/'.AKYOS_UPDATES_NAME.'/dist/js/app.js', [], AKYOS_UPDATES_VERSION, false);
	}

	#[Hook(hook: 'admin_notices', type: Hook::TYPE_ACTION)]
	public function akyos_notice()
	{
		if ($message = get_transient('akyos_success_notice')) {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php echo esc_html($message); ?></p>
			</div>
			<?php
			// Supprime la transitoire pour éviter une réaffichage multiple
			delete_transient('akyos_success_notice');
		}
	}
}
