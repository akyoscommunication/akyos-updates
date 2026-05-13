<?php

namespace AkyosUpdates\Service;

use Throwable;
use WP_Defender\Model\Setting\Blacklist_Lockout;
use WP_Defender\Model\Setting\Captcha;
use WP_Defender\Model\Setting\Login_Lockout;
use WP_Defender\Model\Setting\Mask_Login;
use WP_Defender\Model\Setting\Notfound_Lockout;
use WP_Defender\Model\Setting\Password_Protection;
use WP_Defender\Model\Setting\Security_Headers;
use WP_Defender\Model\Setting\User_Agent_Lockout;

final class DefenderService
{
    public const PLUGIN_FILE = 'wp-defender/wp-defender.php';

    public static function isInstalled(): bool
    {
        return PluginService::isPluginInstalled(self::PLUGIN_FILE);
    }

    public static function isPluginActive(): bool
    {
        return PluginService::isPluginActiveFile(self::PLUGIN_FILE);
    }

    public static function canUseApi(): bool
    {
        return function_exists('wd_di')
            && class_exists('\WP_Defender\Model\Setting\Mask_Login')
            && class_exists('\WP_Defender\Model\Setting\Security_Headers')
            && class_exists('\WP_Defender\Model\Setting\Password_Protection');
    }

    public static function getMaskLoginState(): array
    {
        if (! self::canUseApi()) {
            return [
                'enabled' => false,
                'active' => false,
                'maskUrl' => '',
                'loginUrl' => '',
            ];
        }

        /** @var Mask_Login $model */
        $model = wd_di()->get(Mask_Login::class);
        return [
            'enabled' => (bool) $model->enabled,
            'active' => (bool) $model->is_active(),
            'maskUrl' => (string) $model->mask_url,
            'loginUrl' => (string) $model->get_new_login_url(),
        ];
    }

    public static function saveMaskLoginSlug(string $slug): array
    {
        if (! self::canUseApi()) {
            return [
                'success' => false,
                'message' => 'API Defender indisponible.',
            ];
        }

        $normalizedSlug = trim(sanitize_text_field($slug), "/ \t\n\r\0\x0B");
        if ($normalizedSlug === '') {
            return [
                'success' => false,
                'message' => 'Le slug Mask Login ne peut pas être vide.',
            ];
        }

        /** @var Mask_Login $model */
        $model = wd_di()->get(Mask_Login::class);
        $model->enabled = true;
        $model->mask_url = $normalizedSlug;

        if (! $model->validate()) {
            $message = method_exists($model, 'get_formatted_errors')
                ? (string) $model->get_formatted_errors()
                : 'Configuration Mask Login invalide.';

            return [
                'success' => false,
                'message' => $message,
            ];
        }

        $model->save();

        return [
            'success' => true,
            'message' => 'Mask Login Defender enregistré.',
            'enabled' => (bool) $model->enabled,
            'active' => (bool) $model->is_active(),
            'maskUrl' => (string) $model->mask_url,
            'loginUrl' => (string) $model->get_new_login_url(),
        ];
    }

    public static function getSecurityHeadersState(): array
    {
        if (! self::canUseApi()) {
            return [
                'xFrameOptions' => false,
                'xXssProtection' => false,
                'xContentTypeOptions' => false,
                'strictTransport' => false,
            ];
        }

        /** @var Security_Headers $model */
        $model = wd_di()->get(Security_Headers::class);
        return [
            'xFrameOptions' => (bool) $model->sh_xframe,
            'xXssProtection' => (bool) $model->sh_xss_protection,
            'xContentTypeOptions' => (bool) $model->sh_content_type_options,
            'strictTransport' => (bool) $model->sh_strict_transport,
        ];
    }

    public static function getPwnedPasswordsState(): array
    {
        if (! self::canUseApi()) {
            return [
                'enabled' => false,
                'active' => false,
            ];
        }

        /** @var Password_Protection $model */
        $model = wd_di()->get(Password_Protection::class);
        return [
            'enabled' => (bool) $model->enabled,
            'active' => (bool) $model->is_active(),
        ];
    }

    public static function getRecaptchaState(): array
    {
        if (! self::canUseApi() || ! class_exists('\WP_Defender\Model\Setting\Captcha')) {
            return [
                'enabled' => false,
                'provider' => '',
                'activeType' => '',
                'siteKey' => '',
                'secretKey' => '',
                'hasKeys' => false,
                'keysValid' => false,
                'validationReason' => 'api_unavailable',
            ];
        }

        /** @var Captcha $model */
        $model = wd_di()->get(Captcha::class);
        $provider = (string) ($model->provider ?? 'recaptcha');
        $activeType = (string) ($model->active_type ?? 'v2_checkbox');
        $activeData = $model->get_active_captcha_data($provider, $activeType);
        $siteKey = trim((string) ($activeData['key'] ?? ''));
        $secretKey = trim((string) ($activeData['secret'] ?? ''));
        $hasKeys = $siteKey !== '' && $secretKey !== '';
        $secretValidation = self::validateRecaptchaSecret($secretKey);

        return [
            'enabled' => (bool) $model->enabled,
            'provider' => $provider,
            'activeType' => $activeType,
            'siteKey' => $siteKey,
            'secretKey' => $secretKey,
            'hasKeys' => $hasKeys,
            'keysValid' => $hasKeys && ($secretValidation['valid'] ?? false),
            'validationReason' => (string) ($secretValidation['reason'] ?? 'unknown'),
        ];
    }

    public static function saveRecaptchaKeys(string $siteKey, string $secretKey): array
    {
        if (! self::canUseApi() || ! class_exists('\WP_Defender\Model\Setting\Captcha')) {
            return [
                'success' => false,
                'message' => 'API Defender reCAPTCHA indisponible.',
            ];
        }

        $normalizedSiteKey = trim(sanitize_text_field($siteKey));
        $normalizedSecretKey = trim(sanitize_text_field($secretKey));
        if ($normalizedSiteKey === '' || $normalizedSecretKey === '') {
            return [
                'success' => false,
                'message' => 'Les deux clés reCAPTCHA sont requises.',
            ];
        }

        /** @var Captcha $model */
        $model = wd_di()->get(Captcha::class);
        $model->enabled = true;
        $model->provider = 'recaptcha';
        if (! in_array((string) $model->active_type, ['v2_checkbox', 'v2_invisible', 'v3_recaptcha'], true)) {
            $model->active_type = 'v2_checkbox';
        }

        $activeType = (string) $model->active_type;
        if ($activeType === 'v2_invisible') {
            $model->data_v2_invisible['key'] = $normalizedSiteKey;
            $model->data_v2_invisible['secret'] = $normalizedSecretKey;
        } elseif ($activeType === 'v3_recaptcha') {
            $model->data_v3_recaptcha['key'] = $normalizedSiteKey;
            $model->data_v3_recaptcha['secret'] = $normalizedSecretKey;
        } else {
            $model->active_type = 'v2_checkbox';
            $model->data_v2_checkbox['key'] = $normalizedSiteKey;
            $model->data_v2_checkbox['secret'] = $normalizedSecretKey;
        }

        if (! $model->validate()) {
            $message = method_exists($model, 'get_formatted_errors')
                ? (string) $model->get_formatted_errors()
                : 'Configuration reCAPTCHA invalide.';

            return [
                'success' => false,
                'message' => $message,
            ];
        }

        $validation = self::validateRecaptchaSecret($normalizedSecretKey);
        if (! ($validation['valid'] ?? false)) {
            return [
                'success' => false,
                'message' => 'Clé secrète reCAPTCHA invalide.',
                'validationReason' => (string) ($validation['reason'] ?? 'unknown'),
            ];
        }

        $model->save();

        return array_merge(
            [
                'success' => true,
                'message' => 'Clés reCAPTCHA Defender enregistrées.',
            ],
            self::getRecaptchaState()
        );
    }

    private static function validateRecaptchaSecret(string $secretKey): array
    {
        $secret = trim($secretKey);
        if ($secret === '') {
            return ['valid' => false, 'reason' => 'empty_secret'];
        }

        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $secret,
                'response' => 'akyos_updates_probe',
            ],
            'timeout' => 10,
            'sslverify' => true,
        ]);

        if (is_wp_error($response)) {
            return ['valid' => false, 'reason' => 'request_error'];
        }

        $rawBody = wp_remote_retrieve_body($response);
        $decoded = json_decode((string) $rawBody, true);
        if (! is_array($decoded)) {
            return ['valid' => false, 'reason' => 'invalid_response'];
        }

        $errors = is_array($decoded['error-codes'] ?? null) ? $decoded['error-codes'] : [];
        if (in_array('invalid-input-secret', $errors, true) || in_array('missing-input-secret', $errors, true)) {
            return ['valid' => false, 'reason' => 'invalid_secret'];
        }
        if (in_array('missing-input-response', $errors, true) || in_array('invalid-input-response', $errors, true)) {
            return ['valid' => true, 'reason' => 'secret_valid'];
        }
        if (($decoded['success'] ?? false) === true) {
            return ['valid' => true, 'reason' => 'verified'];
        }

        return ['valid' => false, 'reason' => 'unknown'];
    }

    public static function getGlobalIpLockoutState(): array
    {
        if (! function_exists('wd_di') || ! class_exists('\WP_Defender\Model\Setting\Global_Ip_Lockout')) {
            return [
                'enabled' => false,
                'blocklistAutosync' => false,
                'allowSelfUnlock' => true,
                'settingsOptionKey' => 'wd_global_ip_settings',
            ];
        }

        /** @var \WP_Defender\Model\Setting\Global_Ip_Lockout $model */
        $model = wd_di()->get(\WP_Defender\Model\Setting\Global_Ip_Lockout::class);

        return [
            'enabled' => (bool) $model->enabled,
            'blocklistAutosync' => (bool) $model->blocklist_autosync,
            'allowSelfUnlock' => (bool) $model->allow_self_unlock,
            'settingsOptionKey' => 'wd_global_ip_settings',
        ];
    }

    public static function enableGlobalIpBlocker(): array
    {
        if (! self::isPluginActive() || ! function_exists('wd_di') || ! class_exists('\WP_Defender\Model\Setting\Global_Ip_Lockout')) {
            return [
                'success' => false,
                'message' => 'Defender Global IP Blocker indisponible.',
            ];
        }

        /** @var \WP_Defender\Model\Setting\Global_Ip_Lockout $model */
        $model = wd_di()->get(\WP_Defender\Model\Setting\Global_Ip_Lockout::class);
        $model->enabled = true;

        if (! $model->validate()) {
            $message = method_exists($model, 'get_formatted_errors')
                ? (string) $model->get_formatted_errors()
                : 'Configuration Global IP invalide.';

            return [
                'success' => false,
                'message' => $message,
            ];
        }

        $model->save();

        if (class_exists('\WP_Defender\Component\Config\Config_Hub_Helper')) {
            \WP_Defender\Component\Config\Config_Hub_Helper::set_clear_active_flag();
        }

        return array_merge(
            [
                'success' => true,
                'message' => 'Global IP Blocker Defender activé.',
            ],
            self::getGlobalIpLockoutState()
        );
    }

    private static function canReadFirewallMessageModels(): bool
    {
        return self::isPluginActive()
            && function_exists('wd_di')
            && class_exists(Blacklist_Lockout::class)
            && class_exists(Login_Lockout::class)
            && class_exists(Notfound_Lockout::class)
            && class_exists(User_Agent_Lockout::class);
    }

    private static function firewallEnglishDefaults(): array
    {
        return [
            'ipBan' => 'The administrator has blocked your IP from accessing this website.',
            'loginLockout' => 'You have been locked out due to too many invalid login attempts.',
            'notfoundLockout' => 'You have been locked out due to too many attempts to access a file that doesn`t exist.',
            'userAgent' => 'You have been blocked from accessing this website.',
        ];
    }

    private static function firewallFrenchTargets(): array
    {
        return [
            'ipBan' => "L'administrateur a bloqué l'accès de votre IP à ce site web.",
            'loginLockout' => 'Vous avez été bloqué en raison de trop de tentatives de connexion invalides.',
            'notfoundLockout' => "Vous avez été bloqué en raison de trop de tentatives d'accès à un fichier qui n'existe pas.",
            'userAgent' => "L'accès à ce site a été bloqué.",
        ];
    }

    private static function firewallMessageNeedsTranslation(string $current, string $englishDefault, string $frenchTarget): bool
    {
        $c = trim($current);
        if ($c === '') {
            return true;
        }
        if ($c === $frenchTarget) {
            return false;
        }
        if ($c === $englishDefault) {
            return true;
        }

        return false;
    }

    public static function getFirewallMessagesState(): array
    {
        if (! self::canReadFirewallMessageModels()) {
            return self::emptyFirewallMessagesState(true);
        }

        if (! function_exists('wd_di')) {
            return self::emptyFirewallMessagesState(true);
        }

        try {
            return self::readFirewallMessagesStateFromDefender();
        } catch (Throwable $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('akyos-updates: getFirewallMessagesState — ' . $e->getMessage());
            }

            return array_merge(self::emptyFirewallMessagesState(false), [
                'unavailableReason' => 'exception',
            ]);
        }
    }

    private static function emptyFirewallMessagesState(bool $withReasonInactive): array
    {
        $out = [
            'readable' => false,
            'fullyTranslated' => false,
            'needsTranslation' => false,
            'ipBan' => ['current' => '', 'needsTranslation' => false],
            'loginLockout' => ['current' => '', 'needsTranslation' => false],
            'notfoundLockout' => ['current' => '', 'needsTranslation' => false],
            'userAgent' => [
                'message' => '',
                'maliciousBotMessage' => '',
                'fakeBotsMessage' => '',
                'needsTranslation' => false,
            ],
        ];
        if ($withReasonInactive) {
            $out['unavailableReason'] = 'inactive';
        }

        return $out;
    }

    private static function strProp(object $obj, string ...$candidates): string
    {
        foreach ($candidates as $name) {
            try {
                $v = $obj->{$name};
                if (is_scalar($v) || $v === null) {
                    return (string) $v;
                }
            } catch (Throwable) {
            }
        }

        return '';
    }

    private static function readFirewallMessagesStateFromDefender(): array
    {
        $en = self::firewallEnglishDefaults();
        $fr = self::firewallFrenchTargets();

        $container = wd_di();
        if (! is_object($container) || ! method_exists($container, 'get')) {
            return array_merge(self::emptyFirewallMessagesState(false), ['unavailableReason' => 'exception']);
        }

        $blacklist = $container->get(Blacklist_Lockout::class);
        $login = $container->get(Login_Lockout::class);
        $notfound = $container->get(Notfound_Lockout::class);
        $ua = $container->get(User_Agent_Lockout::class);

        if (! is_object($blacklist) || ! is_object($login) || ! is_object($notfound) || ! is_object($ua)) {
            return array_merge(self::emptyFirewallMessagesState(false), ['unavailableReason' => 'exception']);
        }

        $ipCurrent = self::strProp($blacklist, 'ip_lockout_message');
        $loginCurrent = self::strProp($login, 'lockout_message');
        $nfCurrent = self::strProp($notfound, 'lockout_message');
        $uaMsg = self::strProp($ua, 'message');
        $uaMal = self::strProp($ua, 'malicious_bot_message', 'malicious_bot_lockout_message');
        $uaFake = self::strProp($ua, 'fake_bots_message', 'fake_bot_message');

        $ipBanNeeds = self::firewallMessageNeedsTranslation($ipCurrent, $en['ipBan'], $fr['ipBan']);
        $loginNeeds = self::firewallMessageNeedsTranslation($loginCurrent, $en['loginLockout'], $fr['loginLockout']);
        $notfoundNeeds = self::firewallMessageNeedsTranslation($nfCurrent, $en['notfoundLockout'], $fr['notfoundLockout']);

        $uaMsgNeeds = self::firewallMessageNeedsTranslation($uaMsg, $en['userAgent'], $fr['userAgent']);
        $uaMalNeeds = self::firewallMessageNeedsTranslation($uaMal, $en['userAgent'], $fr['userAgent']);
        $uaFakeNeeds = self::firewallMessageNeedsTranslation($uaFake, $en['userAgent'], $fr['userAgent']);
        $uaNeeds = $uaMsgNeeds || $uaMalNeeds || $uaFakeNeeds;

        $fully = ! $ipBanNeeds && ! $loginNeeds && ! $notfoundNeeds && ! $uaNeeds;

        return [
            'readable' => true,
            'fullyTranslated' => $fully,
            'needsTranslation' => ! $fully,
            'ipBan' => [
                'current' => $ipCurrent,
                'needsTranslation' => $ipBanNeeds,
            ],
            'loginLockout' => [
                'current' => $loginCurrent,
                'needsTranslation' => $loginNeeds,
            ],
            'notfoundLockout' => [
                'current' => $nfCurrent,
                'needsTranslation' => $notfoundNeeds,
            ],
            'userAgent' => [
                'message' => $uaMsg,
                'maliciousBotMessage' => $uaMal,
                'fakeBotsMessage' => $uaFake,
                'needsTranslation' => $uaNeeds,
            ],
        ];
    }

    public static function applyFirewallFrenchTranslations(): array
    {
        if (! self::canReadFirewallMessageModels()) {
            return [
                'success' => false,
                'message' => 'Messages firewall Defender indisponibles.',
            ];
        }

        $fr = self::firewallFrenchTargets();

        /** @var Blacklist_Lockout $blacklist */
        $blacklist = wd_di()->get(Blacklist_Lockout::class);
        $blacklist->ip_lockout_message = sanitize_textarea_field($fr['ipBan']);

        /** @var Login_Lockout $login */
        $login = wd_di()->get(Login_Lockout::class);
        $login->lockout_message = sanitize_textarea_field($fr['loginLockout']);

        /** @var Notfound_Lockout $notfound */
        $notfound = wd_di()->get(Notfound_Lockout::class);
        $notfound->lockout_message = sanitize_textarea_field($fr['notfoundLockout']);

        /** @var User_Agent_Lockout $ua */
        $ua = wd_di()->get(User_Agent_Lockout::class);
        $uaMsg = sanitize_textarea_field($fr['userAgent']);
        $ua->message = $uaMsg;
        $ua->malicious_bot_message = $uaMsg;
        $ua->fake_bots_message = $uaMsg;

        if (! $blacklist->validate()) {
            return [
                'success' => false,
                'message' => method_exists($blacklist, 'get_formatted_errors')
                    ? (string) $blacklist->get_formatted_errors()
                    : 'Validation liste IP Defender échouée.',
            ];
        }
        if (! $login->validate()) {
            return [
                'success' => false,
                'message' => method_exists($login, 'get_formatted_errors')
                    ? (string) $login->get_formatted_errors()
                    : 'Validation login lockout Defender échouée.',
            ];
        }
        if (! $notfound->validate()) {
            return [
                'success' => false,
                'message' => method_exists($notfound, 'get_formatted_errors')
                    ? (string) $notfound->get_formatted_errors()
                    : 'Validation 404 lockout Defender échouée.',
            ];
        }
        if (! $ua->validate()) {
            return [
                'success' => false,
                'message' => method_exists($ua, 'get_formatted_errors')
                    ? (string) $ua->get_formatted_errors()
                    : 'Validation user-agent Defender échouée.',
            ];
        }

        $blacklist->save();
        $login->save();
        $notfound->save();
        $ua->save();

        if (class_exists('\WP_Defender\Component\Config\Config_Hub_Helper')) {
            \WP_Defender\Component\Config\Config_Hub_Helper::set_clear_active_flag();
        }

        return array_merge(
            [
                'success' => true,
                'message' => 'Messages firewall Defender mis à jour.',
            ],
            self::getFirewallMessagesState()
        );
    }
}
