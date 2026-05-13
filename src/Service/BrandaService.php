<?php

namespace AkyosUpdates\Service;

use ReflectionClass;

final class BrandaService
{
    public const PLUGIN_FILE = 'ultimate-branding/ultimate-branding.php';

    public const MODULE_SMTP = 'emails/smtp.php';

    public const MODULE_DASHBOARD_WIDGETS = 'widgets/dashboard-widgets.php';

    public const ROLE_ADMIN_LITE = 'admin-lite';

    public static function isPluginActive(): bool
    {
        return PluginService::isPluginActiveFile(self::PLUGIN_FILE);
    }

    public static function isBrandaLoaded(): bool
    {
        return function_exists('branda_get_option') && function_exists('branda_is_active_module');
    }

    public static function isModuleActive(string $modulePath): bool
    {
        if (!self::isBrandaLoaded()) {
            return false;
        }

        return branda_is_active_module($modulePath);
    }

    public static function activateModule(string $modulePath): bool
    {
        if (!self::isBrandaLoaded() || !function_exists('branda_load_single_module')) {
            return false;
        }

        $modules = get_branda_activated_modules('raw');
        if (!is_array($modules)) {
            $modules = [];
        }
        $modules[$modulePath] = 'yes';
        update_branda_activated_modules($modules);
        branda_load_single_module($modulePath);

        if ($modulePath === self::MODULE_DASHBOARD_WIDGETS) {
            self::primeDashboardWidgetCatalog();
        }

        return self::isModuleActive($modulePath);
    }

    private static function primeDashboardWidgetCatalog(): void
    {
        if (!function_exists('wp_get_current_user')) {
            return;
        }

        $user = wp_get_current_user();
        if (!$user || !$user->ID) {
            return;
        }

        if (!function_exists('wp_dashboard_setup')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
            require_once ABSPATH . 'wp-admin/includes/screen.php';
            require_once ABSPATH . 'wp-admin/includes/template.php';
            require_once ABSPATH . 'wp-admin/includes/dashboard.php';
        }

        $previousScreen = isset($GLOBALS['current_screen']) ? $GLOBALS['current_screen'] : null;

        try {
            set_current_screen('dashboard');
            wp_dashboard_setup();
        } catch (\Throwable) {
        } finally {
            if ($previousScreen instanceof \WP_Screen) {
                $previousScreen->set_current_screen();
            } else {
                unset($GLOBALS['current_screen']);
            }
        }
    }

    public static function getUsersWithAdminLiteRole(): array
    {
        $role = get_role(self::ROLE_ADMIN_LITE);
        if ($role === null) {
            return [];
        }

        return get_users([
            'role' => self::ROLE_ADMIN_LITE,
            'fields' => ['ID', 'user_email', 'display_name'],
        ]);
    }

    public static function ensureAdminLiteRoleExists(): void
    {
        if (get_role(self::ROLE_ADMIN_LITE) !== null) {
            return;
        }
        $editor = get_role('editor');
        $caps = $editor !== null ? $editor->capabilities : ['read' => true];
        add_role(self::ROLE_ADMIN_LITE, 'Admin lite', $caps);
    }

    /**
     * @return array<string, mixed>
     */
    public static function getSmtpState(): array
    {
        $defaults = [
            'brandaActive' => false,
            'smtpModuleActive' => false,
            'fromEmail' => '',
            'fromName' => '',
            'fromNameForce' => 'on',
            'smtpHost' => '',
            'smtpPort' => '587',
            'smtpEncryption' => 'tls',
            'smtpAuth' => 'on',
            'smtpUsername' => '',
            'passwordSet' => false,
            'configured' => false,
        ];

        if (!self::isPluginActive() || !self::isBrandaLoaded()) {
            return $defaults;
        }

        $defaults['brandaActive'] = true;
        $defaults['smtpModuleActive'] = self::isModuleActive(self::MODULE_SMTP);

        $raw = branda_get_option_filtered('ub_smtp');
        if (!is_array($raw)) {
            return $defaults;
        }

        $header = is_array($raw['header'] ?? null) ? $raw['header'] : [];
        $server = is_array($raw['server'] ?? null) ? $raw['server'] : [];
        $auth = is_array($raw['smtp_authentication'] ?? null) ? $raw['smtp_authentication'] : [];

        $pwd = isset($auth['smtp_password']) ? (string) $auth['smtp_password'] : '';
        $defaults['fromEmail'] = isset($header['from_email']) ? (string) $header['from_email'] : '';
        $defaults['fromName'] = isset($header['from_name']) ? (string) $header['from_name'] : '';
        $defaults['fromNameForce'] = isset($header['from_name_force']) ? (string) $header['from_name_force'] : 'on';
        $defaults['smtpHost'] = isset($server['smtp_host']) ? (string) $server['smtp_host'] : '';
        $defaults['smtpPort'] = isset($server['smtp_port']) ? (string) $server['smtp_port'] : '587';
        $defaults['smtpEncryption'] = isset($server['smtp_type_encryption']) ? (string) $server['smtp_type_encryption'] : 'tls';
        $defaults['smtpAuth'] = isset($auth['smtp_authentication']) ? (string) $auth['smtp_authentication'] : 'on';
        $defaults['smtpUsername'] = isset($auth['smtp_username']) ? (string) $auth['smtp_username'] : '';
        $defaults['passwordSet'] = $pwd !== '';

        $hasEmail = $defaults['fromEmail'] !== '' && is_email($defaults['fromEmail']);
        $hasHost = trim($defaults['smtpHost']) !== '';
        $hasPort = trim($defaults['smtpPort']) !== '';
        $authOn = $defaults['smtpAuth'] === 'on';
        $hasAuth = !$authOn || ($defaults['smtpUsername'] !== '' && $defaults['passwordSet']);

        $defaults['configured'] = $defaults['smtpModuleActive'] && $hasEmail && $hasHost && $hasPort && $hasAuth;

        return $defaults;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{success: bool, message: string}
     */
    public static function saveSmtp(array $payload): array
    {
        if (!self::isPluginActive() || !self::isBrandaLoaded()) {
            return ['success' => false, 'message' => 'Branda n’est pas disponible.'];
        }

        if (!self::isModuleActive(self::MODULE_SMTP)) {
            self::activateModule(self::MODULE_SMTP);
        }

        $current = branda_get_option_filtered('ub_smtp');
        if (!is_array($current)) {
            $current = [];
        }

        $header = is_array($current['header'] ?? null) ? $current['header'] : [];
        $server = is_array($current['server'] ?? null) ? $current['server'] : [];
        $auth = is_array($current['smtp_authentication'] ?? null) ? $current['smtp_authentication'] : [];

        $fromEmail = sanitize_email((string) ($payload['fromEmail'] ?? ''));
        if ($fromEmail === '' || !is_email($fromEmail)) {
            return ['success' => false, 'message' => 'Adresse expéditeur invalide.'];
        }

        $header['from_email'] = $fromEmail;
        $header['from_name'] = sanitize_text_field((string) ($payload['fromName'] ?? $header['from_name'] ?? ''));
        $header['from_name_force'] = $header['from_name'] !== '' ? 'on' : ($header['from_name_force'] ?? 'off');

        $host = sanitize_text_field((string) ($payload['smtpHost'] ?? ''));
        if ($host === '') {
            return ['success' => false, 'message' => 'L’hôte SMTP est obligatoire.'];
        }

        $server['smtp_host'] = $host;
        $port = preg_replace('/\D/', '', (string) ($payload['smtpPort'] ?? ''));
        $server['smtp_port'] = $port !== '' ? $port : '587';
        $server['smtp_type_encryption'] = sanitize_text_field((string) ($payload['smtpEncryption'] ?? $server['smtp_type_encryption'] ?? 'tls'));
        if (!in_array($server['smtp_type_encryption'], ['none', 'ssl', 'tls'], true)) {
            $server['smtp_type_encryption'] = 'tls';
        }
        $server['smtp_insecure_ssl'] = ($payload['smtpInsecureSsl'] ?? null) === 'on' || ($server['smtp_insecure_ssl'] ?? '') === 'on' ? 'on' : 'off';

        $auth['smtp_authentication'] = 'on';
        $auth['smtp_username'] = sanitize_text_field((string) ($payload['smtpUsername'] ?? ''));

        $plainPassword = (string) ($payload['smtpPassword'] ?? '');
        if ($plainPassword !== '') {
            self::ensureBrandaSmtpClassLoaded();
            $encrypted = self::encryptSmtpPassword($plainPassword);
            $auth['smtp_password'] = $encrypted !== '' ? $encrypted : $plainPassword;
        }

        $merged = [
            'header' => $header,
            'server' => $server,
            'smtp_authentication' => $auth,
        ];

        branda_update_option('ub_smtp', $merged);

        return ['success' => true, 'message' => 'Configuration SMTP Branda enregistrée.'];
    }

    private static function ensureBrandaSmtpClassLoaded(): void
    {
        if (class_exists(\Branda_SMTP::class, false)) {
            return;
        }
        if (!defined('WPMUDEV_BRANDA_DIR')) {
            return;
        }
        $path = WPMUDEV_BRANDA_DIR . 'inc/modules/emails/smtp.php';
        if (is_readable($path)) {
            require_once $path;
        }
    }

    private static function encryptSmtpPassword(string $plain): string
    {
        if ($plain === '' || !class_exists(\Branda_SMTP::class)) {
            return '';
        }

        try {
            $ref = new ReflectionClass(\Branda_SMTP::class);
            $instance = $ref->newInstanceWithoutConstructor();
            if (!method_exists($instance, 'encrypt')) {
                return '';
            }

            return $instance->encrypt($plain);
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * @return array{success: bool, message: string}
     */
    public static function sendTestEmail(string $to): array
    {
        $to = sanitize_email($to);
        if ($to === '' || !is_email($to)) {
            return ['success' => false, 'message' => 'Destinataire invalide.'];
        }

        $state = self::getSmtpState();
        if (!$state['configured']) {
            return ['success' => false, 'message' => 'SMTP incomplet ou module Branda SMTP inactif.'];
        }

        $subject = sprintf('[Test SMTP] %s', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));
        $body = sprintf(
            "Email de test envoyé depuis %s.\nSi vous recevez ce message, la configuration SMTP fonctionne.",
            esc_url(home_url('/'))
        );

        $sent = wp_mail($to, $subject, $body);
        if (!$sent) {
            return ['success' => false, 'message' => 'Échec envoi (wp_mail). Vérifiez les logs serveur et les identifiants SMTP.'];
        }

        return ['success' => true, 'message' => sprintf('Email de test envoyé à %s.', $to)];
    }

    /**
     * @return array<string, mixed>
     */
    public static function getDashboardWidgetsState(): array
    {
        $defaults = [
            'brandaActive' => false,
            'moduleActive' => false,
            'availableIds' => [],
            'hiddenIds' => [],
            'allHidden' => false,
            'availableCount' => 0,
            'hiddenCount' => 0,
        ];

        if (!self::isPluginActive() || !self::isBrandaLoaded()) {
            return $defaults;
        }

        $defaults['brandaActive'] = true;
        $defaults['moduleActive'] = self::isModuleActive(self::MODULE_DASHBOARD_WIDGETS);

        $available = branda_get_option_filtered('ub_rwp_all_active_dashboard_widgets');
        if (!is_array($available)) {
            $available = [];
        }

        $dashboard = branda_get_option_filtered('ub_dashboard_widgets');
        $hiddenMap = [];
        if (is_array($dashboard['visibility']['wp_widgets'] ?? null)) {
            foreach ($dashboard['visibility']['wp_widgets'] as $id => $val) {
                if ($val === 'on' || $val === true || $val === '1' || $val === 1) {
                    $hiddenMap[(string) $id] = true;
                }
            }
        }

        $ids = array_keys($available);
        $defaults['availableIds'] = $ids;
        $defaults['availableCount'] = count($ids);
        $defaults['hiddenIds'] = array_keys($hiddenMap);
        $defaults['hiddenCount'] = count($hiddenMap);

        if ($defaults['availableCount'] === 0) {
            $defaults['allHidden'] = false;
        } else {
            $all = true;
            foreach ($ids as $id) {
                if (!isset($hiddenMap[(string) $id])) {
                    $all = false;
                    break;
                }
            }
            $defaults['allHidden'] = $all;
        }

        return $defaults;
    }

    /**
     * @return array{success: bool, message: string}
     */
    public static function hideAllDashboardWidgets(): array
    {
        if (!self::isPluginActive() || !self::isBrandaLoaded()) {
            return ['success' => false, 'message' => 'Branda indisponible.'];
        }

        if (!self::isModuleActive(self::MODULE_DASHBOARD_WIDGETS)) {
            return ['success' => false, 'message' => 'Activez d’abord le module Dashboard Widgets dans Branda.'];
        }

        $available = branda_get_option_filtered('ub_rwp_all_active_dashboard_widgets');
        if (!is_array($available) || $available === []) {
            self::primeDashboardWidgetCatalog();
            $available = branda_get_option_filtered('ub_rwp_all_active_dashboard_widgets');
        }
        if (!is_array($available) || $available === []) {
            return [
                'success' => false,
                'message' => 'Aucun widget listé : ouvre le tableau de bord WordPress une fois ou réessaie après analyse.',
            ];
        }

        $data = branda_get_option_filtered('ub_dashboard_widgets');
        if (!is_array($data)) {
            $data = [];
        }
        if (!isset($data['visibility']) || !is_array($data['visibility'])) {
            $data['visibility'] = [];
        }
        if (!isset($data['visibility']['wp_widgets']) || !is_array($data['visibility']['wp_widgets'])) {
            $data['visibility']['wp_widgets'] = [];
        }

        foreach (array_keys($available) as $widgetId) {
            $data['visibility']['wp_widgets'][(string) $widgetId] = 'on';
        }

        branda_update_option('ub_dashboard_widgets', $data);

        return ['success' => true, 'message' => 'Tous les widgets du tableau de bord sont masqués via Branda.'];
    }
}
