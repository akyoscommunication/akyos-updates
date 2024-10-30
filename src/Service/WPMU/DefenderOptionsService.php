<?php

namespace AkyosUpdates\Service\WPMU;

use AkyosUpdates\Attribute\Hook;
use AkyosUpdates\Trait\ServiceTrait;
use Symfony\Component\HttpFoundation\Request;

class DefenderOptionsService
{
	use ServiceTrait;

	public function __construct()
	{
		$this->redirectRoute = 'akyos_updates_defender_options';
	}

	/**
	 * @throws \JsonException
	 */
	public function getTranslate()
	{
		$message = '<p>Traduction des messages Firewall</p>';

		$message .= $this->checkTrans(
			option: 'wd_blacklist_lockout_settings',
			entry: 'ip_lockout_message',
			defaultMessage: 'The administrator has blocked your IP from accessing this website.',
			title: 'Blacklist IP'
		);

		$message .= $this->checkTrans(
			option: 'wd_login_lockout_settings',
			entry: 'lockout_message',
			defaultMessage: 'You have been locked out due to too many invalid login attempts.',
			title: 'Login Lockout'
		);

		$message .= $this->checkTrans(
			option: 'wd_notfound_lockout_settings',
			entry: 'lockout_message',
			defaultMessage: 'You have been locked out due to too many attempts to access a file that doesn`t exist.',
			title: '404 Lockout'
		);

		$message .= $this->checkTrans(
			option: 'wd_user_agent_settings',
			entry: 'message',
			defaultMessage: 'You have been blocked from accessing this website.',
			title: 'User Agent Banning'
		);

		return [
			'message' => $message,
			'action_required' => true,
			'ajax' => [
				'hook' => 'admin_post_akyos_updates_defender_translate'
			]
		];
	}

	/**
	 * @throws \JsonException
	 */
	public function getGlobalIP()
	{
		$settings = get_option('wd_global_ip_settings', true);

		if (!$settings) {
			$message = '<p>⭕ Global IP Blocker n\'est pas configurée et activé</p>';
		} else {
			$settings = json_decode($settings, true, 512, JSON_THROW_ON_ERROR);

			if ($settings['blocklist_autosync']) {
				$message = '<p>✅ Global IP Blocker est configurée et activé</p>';
			} else {
				$message = '<p>⭕ Global IP Blocker n\'est pas configurée et activé</p>';
			}
		}

		return [
			'message' => $message,
			'action_required' => false,
			'ajax' => [
				'hook' => 'admin_post_akyos_updates_defender_global_ip'
			]
		];
	}

	/**
	 * @throws \JsonException
	 */
	#[Hook(hook: 'admin_post_akyos_updates_defender_global_ip')]
	public function enabledBlockerIP()
	{
		delete_option('wd_global_ip_settings');
		add_option('wd_global_ip_settings', json_encode([
			'enabled' => true,
			'blocklist_autosync' => true,
		], JSON_THROW_ON_ERROR));
	}

	/**
	 * @throws \JsonException
	 */
	#[Hook(hook: 'admin_post_akyos_updates_defender_translate')]
	public function translate()
	{
		$this->setTrans(
			option: 'wd_blacklist_lockout_settings',
			entry: 'ip_lockout_message',
			newMessage: 'L\'administrateur a bloqué l\'accès de votre IP à ce site web.',
			data: [
				"ip_blacklist" => "",
				"ip_whitelist" => "",
				"ip_lockout_message" => "L'administrateur a bloqué l'accès de votre IP à ce site web.",
				"country_blacklist" => [],
				"country_whitelist" => [],
				"geodb_path" => "",
				"maxmind_license_key" => ""
			]
		);

		$this->setTrans(
			option: 'wd_login_lockout_settings',
			entry: 'lockout_message',
			newMessage: 'Vous avez été bloqué en raison de trop de tentatives de connexion invalides.',
			data: [
				"enabled" => true,
				"attempt" => 5,
				"timeframe" => 300,
				"duration" => 300,
				"duration_unit" => "seconds",
				"lockout_type" => "timeframe",
				"lockout_message" => "Vous avez été bloqué en raison de trop de tentatives de connexion invalides.",
				"username_blacklist" => ""
			]
		);

		$this->setTrans(
			option: 'wd_notfound_lockout_settings',
			entry: 'lockout_message',
			newMessage: 'Vous avez été bloqué en raison de trop de tentatives d\'accès à un fichier qui n\'existe pas.',
			data: [
				"enabled" => true,
				"attempt" => 20,
				"timeframe" => 300,
				"duration" => 300,
				"duration_unit" => "seconds",
				"lockout_type" => "timeframe",
				"blacklist" => "",
				"whitelist" => [
					'.css',
					'.js',
					'.map',
				],
				"lockout_message" => "Vous avez été bloqué en raison de trop de tentatives d\'accès à un fichier qui n\'existe pas.",
				"detect_logged" => false,
			]
		);

		$this->setTrans(
			option: 'wd_user_agent_settings',
			entry: 'message',
			newMessage: 'L\'accès à ce site a été bloqué.',
			data: [
				"enabled" => true,
				"blacklist" => [
					"MJ12Bot",
					"AhrefsBot",
					"SEMrushBot",
					"DotBot"
				],
				"whitelist" => [
					"a6-indexer",
					"adsbot-google",
					"aolbuild",
					"apis-google",
					"baidu",
					"bingbot",
					"bingpreview",
					"butterfly",
					"cloudflare",
					"chrome",
					"duckduckbot",
					"embedly",
					"facebookexternalhit",
					"facebot",
					"google page speed",
					"googlebot",
					"ia_archiver",
					"linkedinbot",
					"mediapartners-google",
					"msnbot",
					"netcraftsurvey",
					"outbrain",
					"pinterest",
					"quora",
					"slackbot",
					"slurp",
					"tweetmemebot",
					"twitterbot",
					"uptimerobot",
					"urlresolver",
					"vkshare",
					"w3c_validator",
					"wordpress",
					"wp rocket",
					"yandex"
				],
				"message" => "L\'accès à ce site a été bloqué.",
				"empty_headers" => false
			]
		);

		return wp_redirect(admin_url('admin.php?page='.$this->redirectRoute));
	}

	/**
	 * @throws \JsonException
	 */
	public function checkTrans(string $option, string $entry, string $defaultMessage, string $title): string
	{
		$data = get_option($option, true);

		if (!$data) {
			$message = '<p>⭕ Le message de '.$title.' n\'est pas traduit</p>';
		} else {
			$data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

			if ($data[$entry] && $data[$entry] !== $defaultMessage) {
				$message = '<p>✅ Le message de '.$title.' est traduit</p>';
			} else {
				$message = '<p>⭕ Le message de '.$title.' n\'est pas traduit</p>';
			}
		}

		return $message;
	}


	/**
	 * @throws \JsonException
	 */
	public function setTrans(string $option, string $entry, string $newMessage, array $data): void
	{
		$dataOpt = get_option($option);

		if (!$dataOpt) {
			add_option($option, json_encode($data, JSON_THROW_ON_ERROR));
		} else {
			$dataOpt = json_decode($dataOpt, true, 512, JSON_THROW_ON_ERROR);

			$dataOpt[$entry] = $newMessage;

			delete_option($option);
			add_option($option, json_encode($dataOpt, JSON_THROW_ON_ERROR));
		}
	}

	/**
	 * @throws \JsonException
	 */
	public function getMaskLogin()
	{
		$settings = get_option('wd_masking_login_settings');

		if (!$settings) {
			$message = '<p>⭕ Le masquage de login n\'est pas configuré</p>';
		} else {
			$settings = json_decode($settings, true, 512, JSON_THROW_ON_ERROR);
			$message = '<p>✅ Le masquage de login est configuré</p>';
		}

		return [
			'message' => $message,
			'action_required' => true,
			'fields' => [
				[
					'label' => 'Mask login URL',
					'name' => 'url_login',
					'value' => $settings['mask_url'] ?? '',
					'type' => 'text',
					'required' => true
				]
			],
			'ajax' => [
				'hook' => 'admin_post_akyos_updates_defender_mask_login'
			],
		];
	}

	/**
	 * @throws \JsonException
	 */
	#[Hook(hook: 'admin_post_akyos_updates_defender_mask_login')]
	public function setMaskLogin()
	{
		$request = Request::createFromGlobals();
		$urlLogin = $request->get('url_login');

		delete_option('wd_masking_login_settings');
		add_option('wd_masking_login_settings', json_encode([
			"mask_url" => $urlLogin,
			"redirect_traffic" => "off",
			"redirect_traffic_url" => "",
			"redirect_traffic_page_id" => 0,
			"enabled" => true
		], JSON_THROW_ON_ERROR));
	}

	/**
	 * @throws \JsonException
	 */
	public function getSecurityHeaders()
	{
		$settings = get_option('wd_security_headers_settings');

		$message = '<p>Configuration des headers security : </p>';
		$actionRequired = false;

		if (!$settings) {
			$message .= '<p>⭕ X-Frame-Options n\'est pas activé</p>';
			$message .= '<p>⭕ X-XSS-Protection n\'est pas activé</p>';
			$message .= '<p>⭕ X-Content-Type-Options n\'est pas activé</p>';
			$message .= '<p>⭕ Strict Transport n\'est pas activé</p>';
			$actionRequired = true;
		} else {
			$settings = json_decode($settings, true, 512, JSON_THROW_ON_ERROR);

			$message .= '<ul>';

			if ($settings['sh_xframe']) {
				$message .= '<p>✅ X-Frame-Options est activé</p>';
			} else {
				$message .= '<p>⭕ X-Frame-Options n\'est pas activé</p>';
				$actionRequired = true;
			}

			if ($settings['sh_xss_protection']) {
				$message .= '<p>✅ X-XSS-Protection est activé</p>';
			} else {
				$message .= '<p>⭕ X-XSS-Protection n\'est pas activé</p>';
				$actionRequired = true;
			}

			if ($settings['sh_content_type_options']) {
				$message .= '<p>✅ X-Content-Type-Options est activé</p>';
			} else {
				$message .= '<p>⭕ X-Content-Type-Options n\'est pas activé</p>';
				$actionRequired = true;
			}

			if ($settings['sh_strict_transport']) {
				$message .= '<p>✅ Strict Transport est activé</p>';
			} else {
				$message .= '<p>⭕ Strict Transport n\'est pas activé</p>';
				$actionRequired = true;
			}

			$message .= '</ul>';
		}

		return [
			'message' => $message,
			'action_required' => $actionRequired,
			'ajax' => [
				'hook' => 'admin_post_akyos_updates_defender_security_headers'
			]
		];
	}

	/**
	 * @throws \JsonException
	 */
	#[Hook(hook: 'admin_post_akyos_updates_defender_security_headers')]
	public function setSecurityHeaders()
	{
		delete_option('wd_security_headers_settings');
		add_option('wd_security_headers_settings', json_encode([
			"sh_xframe" => true,
			"sh_xframe_mode" => "sameorigin",
			"sh_xss_protection" => true,
			"sh_xss_protection_mode" => "sanitize",
			"sh_content_type_options" => true,
			"sh_content_type_options_mode" => "nosniff",
			"sh_strict_transport" => true,
			"hsts_preload" => 0,
			"include_subdomain" => 0,
			"hsts_cache_duration" => "30 days",
			"sh_referrer_policy" => false,
			"sh_referrer_policy_mode" => "origin-when-cross-origin",
			"sh_feature_policy" => false,
			"sh_feature_policy_mode" => "self",
			"sh_feature_policy_urls" => "",
		], JSON_THROW_ON_ERROR));
	}
}
