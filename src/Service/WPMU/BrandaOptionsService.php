<?php

namespace AkyosUpdates\Service\WPMU;


use AkyosUpdates\Attribute\Hook;

class BrandaOptionsService
{
	public function getBrandaAdminMessage()
	{
		$isMessageAdmin = get_option('ub_admin_message');

		if (!$isMessageAdmin || !$isMessageAdmin['admin']['message']) {
			$isMessageAdminMessage = '⭕ Le message d\'administration pour les mails est désactivé';
		} else {
			$isMessageAdminMessage = '✅ Le message d\'administration pour les mails est activé';
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


	public function getBrandaWidgets()
	{

		//get all dashboard widgets


		return [
			'message' => 'Oui',
			'action_required' => true,
		];
	}
}
