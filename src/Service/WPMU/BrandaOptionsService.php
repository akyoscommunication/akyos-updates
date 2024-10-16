<?php

namespace AkyosUpdates\Service\WPMU;


use AkyosUpdates\Attribute\Hook;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class BrandaOptionsService
{

	public function getBrandaAdminMessage()
	{
		$isMessageAdmin = get_option('ub_admin_message');

		if (!$isMessageAdmin || !$isMessageAdmin['admin']['message']) {
			$isMessageAdminMessage = '<p>⭕ Le message d\'administration pour les mails est désactivé</p>';
		} else {
			$isMessageAdminMessage = '<p>✅ Le message d\'administration pour les mails est activé</p>';
		}

		return [
			'message' => $isMessageAdminMessage,
			'action_required' => !$isMessageAdmin || !$isMessageAdmin['admin']['message'],
			'reverse_action' => isset($isMessageAdmin['admin'])
		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_enable_branda_admin_message')]
	public function enableBrandaAdminMessage(): bool
	{
		$activated_modules = get_option('ultimatebranding_activated_modules');

		if (!array_key_exists('admin/custom-css.php', $activated_modules)) {
			$activated_modules['admin/custom-css.php'] = 'yes';
		}

		if (!array_key_exists('admin/message.php', $activated_modules)) {
			$activated_modules['admin/message.php'] = 'yes';
		}

		update_option('ultimatebranding_activated_modules', $activated_modules);


		$adminMessage = get_option('ub_admin_message');
		$adminCss = get_option('ub_admin_css');
		$message = 'Aucun mail n\'a été envoyé depuis votre site Internet. En effet, vous avez dû recevoir un mail (il y a quelques jours) vous informant que nous changions d\'outil d\'envoi de mails pour des raisons de sécurité. Si vous ne retrouvez pas ce mail, pas de panique, vous pouvez toujours accéder au document via ce <a href="https://docs.google.com/document/d/1EGYFh8Jf7zgUtWbonzHWe1cFexEtdwyT/edit?usp=sharing&amp;ouid=118364990256481163057&amp;rtpof=true&amp;sd=true">lien</a> et suivre la démarche. Merci de nous tenir informés <a href="mailto:support@akyos.com">support@akyos.com</a> une fois la démarche effectuée.';

		if ($adminMessage) {
			delete_option('ub_admin_message');
		}

		if ($adminCss) {
			delete_option('ub_admin_css');
		}

		add_option('ub_admin_message', $message);
		add_option('ub_admin_css', '#branda-message {border-left-color: red;background: red;color: white;font-weight: bold;}#branda-message a {color: white;text-decoration: underline;}');

		return wp_redirect(admin_url('admin.php?page=akyos_updates_wpmu_options'));
	}

	#[Hook(hook: 'admin_post_akyos_updates_disable_branda_admin_message')]
	public function disableBrandaMessage()
	{
		$activated_modules = get_option('ultimatebranding_activated_modules');

		if (array_key_exists('admin/custom-css.php', $activated_modules)) {
			unset($activated_modules['admin/custom-css.php']);
		}

		if (array_key_exists('admin/message.php', $activated_modules)) {
			unset($activated_modules['admin/message.php']);
		}

		update_option('ultimatebranding_activated_modules', $activated_modules);

		$adminMessage = get_option('ub_admin_message');
		$adminCss = get_option('ub_admin_css');

		if ($adminMessage) {
			delete_option('ub_admin_message');
		}

		if ($adminCss) {
			delete_option('ub_admin_css');
		}

		return wp_redirect(admin_url('admin.php?page=akyos_updates_wpmu_options'));
	}


	/**
	 * @throws TransportExceptionInterface
	 */
	public function getBrandaWidgets()
	{
		$widgets = get_option('ub_rwp_all_active_dashboard_widgets');
		$widgetsToHide = get_option('ub_dashboard_widgets');

		$activated_modules = get_option('ultimatebranding_activated_modules');
		$message[0] = '<p>⭕ La fonctionnalité pour masquer les Widgets n\'est pas activée</p>';
		$message[1] = '<p>⭕ Les Widgets ne sont pas chargés, <a href="'.admin_url('index.php').'">Charger les widgets ici</a></p>';
		$message[2] = '<p>⭕ Les Widgets ne sont pas masqués</p>';

		if (array_key_exists('widgets/dashboard-widgets.php', $activated_modules)) {
			$message[0] = '<p>✅ La fonctionnalité pour masquer les Widgets est activée</p>';
		}
		if ($widgets) {
			$message[1] = '<p>✅ Les Widgets sont chargé</p>';
		}
		if ($widgetsToHide) {
			$message[2] = '<p>✅ Les Widgets sont masqués</p>';
		}

		return [
			'message' => implode('<br>', $message),
			'action_required' => !$widgetsToHide,
		];
	}

	/**
	 * @throws TransportExceptionInterface
	 */
	#[Hook(hook: 'admin_post_akyos_updates_hide_branda_widgets')]
	public function hideWidgets()
	{
		$activated_modules = get_option('ultimatebranding_activated_modules');

		if (!array_key_exists('widgets/dashboard-widgets.php', $activated_modules)) {
			$activated_modules['widgets/dashboard-widgets.php'] = 'yes';
		}

		update_option('ultimatebranding_activated_modules', $activated_modules);

		$widgets = get_option('ub_rwp_all_active_dashboard_widgets');

		if ($widgets) {

			foreach ($widgets as $key => $widget) {
				$widgets[$key] = 'on';
			}

			unset($widgets['custom_help_widget']);

			$widgetsToHide = [
				'visibility' => [
					'wp_widgets' => $widgets
				],
				'welcome' => [
					'shortcode' => 'off',
					'text' => "",
					'text_meta' => "",
				],
				'text' => [],
				'plugin_version' => get_option('ub_version')
			];

			add_option('ub_dashboard_widgets', $widgetsToHide);
		} else {
			delete_option('ub_dashboard_widgets');
		}

		return wp_redirect(admin_url('admin.php?page=akyos_updates_wpmu_options'));
	}
}
