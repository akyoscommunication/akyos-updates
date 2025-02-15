<?php

namespace AkyosUpdates\Service\WPMU;


use AkyosUpdates\Attribute\Hook;
use AkyosUpdates\Class\AbstractController;
use AkyosUpdates\Trait\ServiceTrait;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\HttpFoundation\Request;
use WP_Roles;

class BrandaOptionsService extends AbstractController
{
	use ServiceTrait;

	public function __construct()
	{
		$this->redirectRoute = 'akyos_updates_branda_options';

		parent::__construct();

	}

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
			'reverse_action' => isset($isMessageAdmin['admin']),
			'ajax' => [
				'hook' => !$isMessageAdmin || !$isMessageAdmin['admin']['message'] ? 'admin_post_akyos_updates_enable_branda_admin_message' : 'admin_post_akyos_updates_disable_branda_admin_message',
				'success_message' => !$isMessageAdmin || !$isMessageAdmin['admin']['message'] ? 'Le message de Branda a bien été désactivé.' : 'Le message de Branda a bien été activé.',
				'error_message' => 'Une erreur est survenue lors de l\'activation du message de Branda.'
			]
		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_enable_branda_admin_message')]
	public function enableBrandaAdminMessage()
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

		return wp_redirect(admin_url('admin.php?page='.$this->redirectRoute));
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

		return wp_redirect(admin_url('admin.php?page='.$this->redirectRoute));
	}

	/**
	 * @throws TransportExceptionInterface
	 */
	public function getBrandaWidgets()
	{
		global $branda_modules;
		if (empty($branda_modules)) {
			$branda_modules = branda_get_option('ultimatebranding_activated_modules', array());
		}

		$widgets = get_option('ub_rwp_all_active_dashboard_widgets');
		$widgetsToHide = get_option('ub_dashboard_widgets');

		$activated_modules = get_option('ultimatebranding_activated_modules');

		if (!$activated_modules) {
			add_option('ultimatebranding_activated_modules', $branda_modules);
			$activated_modules = get_option('ultimatebranding_activated_modules');
		}

		$message[0] = ' <p>⭕ La fonctionnalité pour masquer les Widgets n\'est pas activée</p>';
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
			'action_required' => !array_key_exists('widgets/dashboard-widgets.php', $activated_modules) || !$widgetsToHide,
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

		return wp_redirect(admin_url('admin.php?page='.$this->redirectRoute));
	}

	public function getBandraEmail()
	{
		$ub_smtp = get_option('ub_smtp');

		if (!$ub_smtp) {
			$message = '<p>⭕ Le SMTP n\'est pas configuré</p>';
		} else {
			$message = '<p>✅ Le SMTP est configuré, vous pouvez le modifier ici</p>';
		}

		return [
			'message' => $message,
			'action_required' => true,
			'fields' => [
				[
					'label' => 'Sender email address',
					'name' => 'from_email',
					'type' => 'text',
					'value' => $ub_smtp ? $ub_smtp['header']['from_email'] : ''
				],
				[
					'label' => 'SMTP Host',
					'name' => 'smtp_host',
					'type' => 'text',
					'value' => $ub_smtp ? $ub_smtp['server']['smtp_host'] : ''
				],
				[
					'label' => 'SMTP Port',
					'name' => 'smtp_port',
					'type' => 'text',
					'value' => $ub_smtp ? $ub_smtp['server']['smtp_port'] : ''
				],
				[
					'label' => 'SMTP Username',
					'name' => 'smtp_username',
					'type' => 'text',
					'value' => $ub_smtp ? $ub_smtp['smtp_authentication']['smtp_username'] : ''
				],
				[
					'label' => 'SMTP Password',
					'name' => 'smtp_password',
					'type' => 'password',
					'value' => $ub_smtp ? $ub_smtp['smtp_authentication']['smtp_password'] : ''
				]
			],
			'ajax' => [
				'hook' => 'admin_post_akyos_updates_configure_branda_email'
			]
		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_configure_branda_email')]
	public function configureBrandaEmail()
	{
		$request = Request::createFromGlobals();
		$activated_modules = get_option('ultimatebranding_activated_modules');

		if (!array_key_exists('emails/smtp.php', $activated_modules)) {
			$activated_modules['emails/smtp.php'] = 'yes';
		}

		update_option('ultimatebranding_activated_modules', $activated_modules);

		$smtp = [
			'header' => [
				'from_email' => $request->request->get('from_email'),
				'from_name_force' => 'on',
				'from_name' => ""
			],
			"server" => [
				"smtp_host" => $request->request->get('smtp_host'),
				"smtp_type_encryption" => "ssl",
				"smtp_port" => $request->request->get('smtp_port'),
				"smtp_insecure_ssl" => "off"
			],
			"smtp_authentication" => [
				"smtp_authentication" => "on",
				"smtp_username" => $request->request->get('smtp_username'),
				"smtp_password" => $request->request->get('smtp_password')
			],
			'plugin_version' => get_option('ub_version')
		];

		delete_option('ub_smtp');
		add_option('ub_smtp', $smtp);

		return wp_redirect(admin_url('admin.php?page='.$this->redirectRoute));
	}

	public function getBrandaTestMail()
	{

		$message = '<p>Vous pouvez tester l\'envoi d\'un mail en cliquant sur le bouton ci-dessous.</p>';

		return [
			'message' => $message,
			'action_required' => true,
			'fields' => [
				[
					'label' => 'Email',
					'name' => 'email',
					'type' => 'email'
				]
			],
			'ajax' => [
				'hook' => 'admin_post_akyos_updates_test_branda_mail',
				'success_message' => 'Le mail a bien été envoyé. Vous devriez le recevoir dans quelques instants.',
				'error_message' => 'Problème de connexion au serveur SMTP. Veuillez vérifier les informations saisies.'
			]
		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_test_branda_mail')]
	public function testBrandaMail()
	{
		$ub_smtp = get_option('ub_smtp');
		$request = Request::createFromGlobals();
		$email = $request->request->get('email');

		$mail = new PHPMailer(true);

		try {
			$mail->isSMTP();
			$mail->Host = $ub_smtp['server']['smtp_host'];
			$mail->SMTPAuth = true;
			$mail->Username = $ub_smtp['smtp_authentication']['smtp_username'];
			$mail->Password = $ub_smtp['smtp_authentication']['smtp_password'];
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			$mail->Port = $ub_smtp['server']['smtp_port'];

			$mail->setFrom($ub_smtp['header']['from_email'], 'Akyos');
			$mail->addAddress($email);

			$mail->isHTML();
			$mail->Subject = 'Exemple d\'email avec SMTP Gmail';
			$mail->Body = '<p>Ceci est un test d\'email envoyé via SMTP avec Gmail et PHPMailer.</p>';
			$mail->AltBody = 'Ceci est le texte en brut, pour les clients qui ne supportent pas le HTML.';

			$mail->send();
		} catch (\PHPMailer\PHPMailer\Exception $e) {
			dd($e);
		}


		return wp_redirect(admin_url('admin.php?page='.$this->redirectRoute));
	}

	public function getAdminLite()
	{
		global $wp_roles;
		if (!isset($wp_roles)) {
			$wp_roles = new WP_Roles();
		}
		$adm = $wp_roles->get_role('administrator');
		$wp_roles->add_role('admin-lite', 'Admin Lite', $adm?->capabilities);

		$users = get_users([
			'role' => 'admin-lite'
		]);

		if (empty($users)) {
			$message = '<p>⭕ Aucun compte trouvé avec le role Admin Lite</p>';
		} else {
			$message = '<p>✅ Le rôle Admin Lite est attribué au(x) compte(s) :</p>';
			$message .= '<ul>';
			foreach ($users as $user) {
				$message .= '<li><p>'.$user->user_email.'</p></li>';
			}
			$message .= '</ul>';
			$message .= '<p>Vous pouvez <a href="'.admin_url('admin.php?page=branding_group_admin&module=admin_menu').'">configurer les accès du rôle Admin Lite ici</a></p>';
		}

		return [
			'message' => $message,
			'action_required' => empty($users),
		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_create_admin_lite')]
	public function redirectToCreateAccount(): bool
	{
		$activated_modules = get_option('ultimatebranding_activated_modules');

		if (!array_key_exists('admin/menu.php', $activated_modules)) {
			$activated_modules['admin/menu.php'] = 'yes';
		}

		update_option('ultimatebranding_activated_modules', $activated_modules);


		return wp_redirect(admin_url('users.php'));
	}
}
