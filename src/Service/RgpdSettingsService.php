<?php

namespace AkyosUpdates\Service;

/**
 * Stockage et normalisation des réglages du module RGPD interne d'akyos-updates.
 *
 * Source de vérité unique : option `akyos_updates_rgpd` (tableau, avec sous-tableau `woo_tracking`).
 * Les anciennes options du plugin aky-gdpr (`aky-gdpr`, `aky-gdpr-woo_tracking`) ne sont lues
 * que lors d'une migration explicite (cf. migrateFromLegacy()).
 */
final class RgpdSettingsService
{
    public const OPTION_KEY = 'akyos_updates_rgpd';

    public const LEGACY_OPTION_KEY = 'aky-gdpr';
    public const LEGACY_WOO_OPTION_KEY = 'aky-gdpr-woo_tracking';

    public const SERVICE_TARTEAUCITRON = 'tarteaucitron';
    public const SERVICE_SIRDATA = 'sirdata';
    public const SERVICE_MATOMO_NO_COOKIE = 'matomo_no_cookie';

    /** @var array<string, mixed>|null Cache mémoire par requête. */
    private ?array $cache = null;

    /** @return list<string> */
    public static function serviceTypes(): array
    {
        return [self::SERVICE_TARTEAUCITRON, self::SERVICE_SIRDATA, self::SERVICE_MATOMO_NO_COOKIE];
    }

    /** @return array<string, mixed> Réglages normalisés, valeurs par défaut comprises. */
    public function get(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $stored = get_option(self::OPTION_KEY, []);
        $this->cache = $this->normalize(is_array($stored) ? $stored : []);

        if (empty($this->cache['tac_tags'])) {
            $migrated = $this->tacTagService()->migrateLegacyTags($this->cache);
            $type = (string) ($this->cache['service_type'] ?? self::SERVICE_TARTEAUCITRON);
            if ($type === '') {
                $type = self::SERVICE_TARTEAUCITRON;
            }
            if ($migrated !== []) {
                $this->cache['tac_tags'] = $this->tacTagService()->normalizeTags($migrated, $type);
            }
        }

        return $this->cache;
    }

    /**
     * Valide puis persiste les réglages. Retourne la version normalisée stockée.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function save(array $input): array
    {
        $normalized = $this->normalize($input);
        update_option(self::OPTION_KEY, $normalized, false);
        $this->cache = $normalized;

        return $normalized;
    }

    /** Le module est-il prêt à produire un rendu front (activé + service choisi + nom de site) ? */
    public function isConfigured(): bool
    {
        $settings = $this->get();
        $siteName = trim((string) ($settings['site_name'] ?? ''));
        if ($siteName === '') {
            $siteName = trim((string) get_bloginfo('name'));
        }

        return $this->isActiveOnFrontend()
            && $siteName !== '';
    }

    /** Bannière + tags sur le front : activé + type de CMP choisi (sans exiger le nom du site). */
    public function isActiveOnFrontend(): bool
    {
        $settings = $this->get();

        return ! empty($settings['enabled'])
            && in_array((string) $settings['service_type'], self::serviceTypes(), true);
    }

    /** @return array<string, mixed> Défauts complets du schéma. */
    public static function defaults(): array
    {
        return [
            'enabled' => false,
            'service_type' => '',
            'site_name' => '',
            'mail' => '',
            'address' => '',
            'contact_url' => '',

            'legal_company_name' => '',
            'legal_form' => '',
            'legal_capital' => '',
            'legal_rcs' => '',
            'legal_siret' => '',
            'legal_tva' => '',
            'legal_publication_director' => '',
            'legal_host_name' => '',
            'legal_host_address' => '',
            'legal_host_phone' => '',

            'gtm' => '',
            'pixel_fb' => '',
            'youtube' => false,
            'matomo_url' => '',
            'matomo_js_path' => '',
            'matomo_site_id' => '',
            'matomo_url_tag' => '',

            'sirdata_user' => '',
            'sirdata_site' => '',

            'cookie_button_content' => '',
            'hide_cookie_button' => false,
            'hide_banner' => false,

            'page_mentions_id' => 0,
            'page_privacy_id' => 0,
            'page_cookies_id' => 0,

            'woo_tracking' => [
                'enabled' => false,
                'add_to_cart_btn_class' => '',
                'remove_from_cart_btn_class' => '',
            ],

            /** Tags tarteaucitron configurés dynamiquement (cf. TarteaucitronTagService). */
            'tac_tags' => [],

            /** Charger tarteaucitron depuis jsDelivr (@1 = dernière 1.x). */
            'tac_use_cdn' => true,

            /** Couleurs bannière / panneau cookies (front). */
            'theme_primary' => '#0052FF',
            'theme_primary_light' => '#4D7CFF',
            'theme_text' => '#0F172A',
            'theme_text_muted' => '#64748B',
            'theme_border' => '#E2E8F0',
            'theme_surface' => '#FFFFFF',
            'theme_danger' => '#DC2626',
            'theme_radius' => 16,
            'theme_font' => '',

            /** Textes bannière (vide = libellés tarteaucitron par défaut). */
            'banner_title' => '',

            /** Bouton flottant cookies : bottom-left | bottom-right. */
            'cookie_button_position' => 'bottom-left',
            /** true = toujours visible ; false = apparaît en bas de page au scroll. */
            'cookie_button_always_visible' => false,

            /** Comportement tarteaucitron (orientation, CTAs, GCM…). */
            'tac_orientation' => 'bottom',
            'tac_show_accept_all' => true,
            'tac_show_deny_all' => true,
            'tac_high_privacy' => true,
            'tac_google_consent_mode' => true,
            'tac_group_services' => false,

            /** Signaux GCM v2 cochés (activés auto à l'ajout d'un GTM). */
            'gcm_jobs_enabled' => [],
        ];
    }

    /** @return list<string> */
    public static function defaultGcmJobsEnabled(): array
    {
        return [];
    }

    /** @return array<string, string|int> */
    public function resolvedTheme(): array
    {
        $settings = $this->get();
        $defaults = self::defaults();
        $primary = self::sanitizeHex($settings['theme_primary'] ?? '') ?? '#0052FF';
        $primaryLight = self::sanitizeHex($settings['theme_primary_light'] ?? '') ?? self::mixHex($primary, '#FFFFFF', 0.35);
        [$r, $g, $b] = self::hexToRgb($primary);
        [$dr, $dg, $db] = self::hexToRgb(
            self::sanitizeHex($settings['theme_danger'] ?? '') ?? (string) $defaults['theme_danger']
        );

        $radius = isset($settings['theme_radius']) ? (int) $settings['theme_radius'] : (int) $defaults['theme_radius'];
        $radius = max(8, min(32, $radius));

        $font = self::sanitizeFontStack($settings['theme_font'] ?? '');
        if ($font === '') {
            $font = 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
        }

        $bannerTitle = trim((string) ($settings['banner_title'] ?? ''));

        return [
            'primary' => $primary,
            'primary_hover' => self::darkenHex($primary, 0.08),
            'primary_light' => $primaryLight,
            'primary_soft' => sprintf('rgba(%d, %d, %d, 0.08)', $r, $g, $b),
            'text' => self::sanitizeHex($settings['theme_text'] ?? '') ?? (string) $defaults['theme_text'],
            'text_muted' => self::sanitizeHex($settings['theme_text_muted'] ?? '') ?? (string) $defaults['theme_text_muted'],
            'border' => self::sanitizeHex($settings['theme_border'] ?? '') ?? (string) $defaults['theme_border'],
            'surface' => self::sanitizeHex($settings['theme_surface'] ?? '') ?? (string) $defaults['theme_surface'],
            'danger' => self::sanitizeHex($settings['theme_danger'] ?? '') ?? (string) $defaults['theme_danger'],
            'danger_soft' => sprintf('rgba(%d, %d, %d, 0.08)', $dr, $dg, $db),
            'radius' => $radius,
            'radius_sm' => max(6, (int) round($radius * 0.625)),
            'font' => $font,
            'banner_title' => $bannerTitle,
        ];
    }

    public function themeCssBlock(): string
    {
        $t = $this->resolvedTheme();
        $bannerTitle = (string) ($t['banner_title'] ?? '');
        $bannerTitleCss = $bannerTitle !== ''
            ? sprintf('--aky-rgpd-banner-title:%s;', wp_json_encode($bannerTitle, JSON_UNESCAPED_UNICODE))
            : '';

        return sprintf(
            ':root{--aky-rgpd-primary:%1$s;--aky-rgpd-primary-hover:%2$s;--aky-rgpd-primary-soft:%3$s;'
            . '--aky-rgpd-text:%4$s;--aky-rgpd-text-muted:%5$s;--aky-rgpd-border:%6$s;'
            . '--aky-rgpd-surface:%7$s;--aky-rgpd-danger:%8$s;--aky-rgpd-danger-soft:%9$s;'
            . '--aky-rgpd-radius:%10$dpx;--aky-rgpd-radius-sm:%11$dpx;--aky-rgpd-font:%12$s;%13$s}',
            $t['primary'],
            $t['primary_hover'],
            $t['primary_soft'],
            $t['text'],
            $t['text_muted'],
            $t['border'],
            $t['surface'],
            $t['danger'],
            $t['danger_soft'],
            $t['radius'],
            $t['radius_sm'],
            wp_json_encode($t['font'], JSON_UNESCAPED_UNICODE),
            $bannerTitleCss
        );
    }

    /** @return array<string, mixed> Paramètres tarteaucitron.init() depuis les réglages. */
    public function tarteaucitronInitParams(string $privacyUrl, string $cookiesUrl): array
    {
        $settings = $this->get();
        $orientation = (string) ($settings['tac_orientation'] ?? 'bottom');
        if (! in_array($orientation, ['bottom', 'top', 'middle'], true)) {
            $orientation = 'bottom';
        }

        return [
            'privacyUrl' => $privacyUrl,
            'bodyPosition' => 'bottom',
            'hashtag' => '#tarteaucitron',
            'cookieName' => 'tarteaucitron',
            'orientation' => $orientation,
            'groupServices' => ! empty($settings['tac_group_services']),
            'showDetailsOnClick' => true,
            'serviceDefaultState' => 'wait',
            'showAlertSmall' => false,
            'cookieslist' => false,
            'showIcon' => false,
            'iconPosition' => 'BottomRight',
            'adblocker' => false,
            'DenyAllCta' => ! empty($settings['tac_show_deny_all']),
            'AcceptAllCta' => ! empty($settings['tac_show_accept_all']),
            'highPrivacy' => ! array_key_exists('tac_high_privacy', $settings) || ! empty($settings['tac_high_privacy']),
            'handleBrowserDNTRequest' => false,
            'removeCredit' => true,
            'moreInfoLink' => true,
            'useExternalCss' => true,
            'useExternalJs' => false,
            'readmoreLink' => $cookiesUrl !== '' ? $cookiesUrl : '/utilisation-des-cookies',
            'mandatory' => true,
            'mandatoryCta' => true,
            'googleConsentMode' => ! array_key_exists('tac_google_consent_mode', $settings) || ! empty($settings['tac_google_consent_mode']),
            'partnersList' => false,
        ];
    }

    private static function sanitizeFontStack(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        $value = trim(wp_strip_all_tags($value));
        if ($value === '') {
            return '';
        }

        return substr($value, 0, 200);
    }

    private static function sanitizeHex(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if ($value[0] !== '#') {
            $value = '#' . $value;
        }

        if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
            return null;
        }

        return strtoupper($value);
    }

    /** @return array{0: int, 1: int, 2: int} */
    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private static function rgbToHex(int $r, int $g, int $b): string
    {
        return sprintf('#%02X%02X%02X', max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));
    }

    private static function darkenHex(string $hex, float $amount): string
    {
        [$r, $g, $b] = self::hexToRgb($hex);
        $factor = 1 - max(0, min(1, $amount));

        return self::rgbToHex((int) round($r * $factor), (int) round($g * $factor), (int) round($b * $factor));
    }

    private static function mixHex(string $hexA, string $hexB, float $ratio): string
    {
        [$r1, $g1, $b1] = self::hexToRgb($hexA);
        [$r2, $g2, $b2] = self::hexToRgb($hexB);
        $ratio = max(0, min(1, $ratio));

        return self::rgbToHex(
            (int) round($r1 + ($r2 - $r1) * $ratio),
            (int) round($g1 + ($g2 - $g1) * $ratio),
            (int) round($b1 + ($b2 - $b1) * $ratio)
        );
    }

    /** @return array<string, array{label: string, description: string, docUrl: string, tagMode: string}> */
    public static function serviceTypeMeta(): array
    {
        return [
            self::SERVICE_TARTEAUCITRON => [
                'label' => 'TarteAuCitron',
                'description' => 'CMP open source intégré au plugin. Les tags sont chargés après consentement via tarteaucitron.job.',
                'docUrl' => 'https://tarteaucitron.io/installation-gratuite-open-source/',
                'tagMode' => 'tarteaucitron',
            ],
            self::SERVICE_SIRDATA => [
                'label' => 'SirData',
                'description' => 'CMP externalisé. Les tags GTM / Pixel peuvent être gérés côté SirData ; des champs legacy restent disponibles pour injection directe.',
                'docUrl' => 'https://www.sirdata.com/',
                'tagMode' => 'sirdata',
            ],
            self::SERVICE_MATOMO_NO_COOKIE => [
                'label' => 'Matomo (sans consentement)',
                'description' => 'Matomo Tag Manager chargé directement, sans bannière tarteaucitron. À réserver aux configs Matomo exemptées de consentement.',
                'docUrl' => 'https://matomo.org/guide/tag-manager/',
                'tagMode' => 'matomo_direct',
            ],
        ];
    }

    private function tacTagService(): TarteaucitronTagService
    {
        static $service = null;
        if ($service === null) {
            $service = new TarteaucitronTagService(new TarteaucitronCatalogService());
        }

        return $service;
    }

    /**
     * Migration depuis les anciennes options du plugin aky-gdpr.
     *
     * @return array{migrated: bool, importedFields: list<string>, settings: array<string, mixed>}
     */
    public function migrateFromLegacy(): array
    {
        $legacy = get_option(self::LEGACY_OPTION_KEY, []);
        $legacyWoo = get_option(self::LEGACY_WOO_OPTION_KEY, []);
        $legacy = is_array($legacy) ? $legacy : [];
        $legacyWoo = is_array($legacyWoo) ? $legacyWoo : [];

        if ($legacy === [] && $legacyWoo === []) {
            return [
                'migrated' => false,
                'importedFields' => [],
                'settings' => $this->get(),
            ];
        }

        // Mapping ancien champ aky-gdpr -> nouveau champ.
        $map = [
            'rgpd_service_type' => 'service_type',
            'rgpd_title' => 'site_name',
            'rgpd_mail' => 'mail',
            'rgpd_address' => 'address',
            'rgpd_contact' => 'contact_url',
            'rgpd_legal_company_name' => 'legal_company_name',
            'rgpd_legal_legal_form' => 'legal_form',
            'rgpd_legal_capital' => 'legal_capital',
            'rgpd_legal_rcs' => 'legal_rcs',
            'rgpd_legal_siret' => 'legal_siret',
            'rgpd_legal_tva' => 'legal_tva',
            'rgpd_legal_publication_director' => 'legal_publication_director',
            'rgpd_legal_host_name' => 'legal_host_name',
            'rgpd_legal_host_address' => 'legal_host_address',
            'rgpd_legal_host_phone' => 'legal_host_phone',
            'rgpd_gta' => 'gtm',
            'rgpd_pixelfb' => 'pixel_fb',
            'rgpd_youtube' => 'youtube',
            'rgpd_matomo_url' => 'matomo_url',
            'rgpd_matomo_js_path' => 'matomo_js_path',
            'rgpd_matomo_site_id' => 'matomo_site_id',
            'rgpd_matomo_url_tag' => 'matomo_url_tag',
            'sirdata_user' => 'sirdata_user',
            'sirdata_site' => 'sirdata_site',
            'rgpd_front_logo' => 'cookie_button_content',
            'rgpd_front_logo_display' => 'hide_cookie_button',
            'rgpd_front_display' => 'hide_banner',
        ];

        // Le service type ancien est préfixé "service_" (ex. service_tarteaucitron).
        $serviceTypeAliases = [
            'service_tarteaucitron' => self::SERVICE_TARTEAUCITRON,
            'service_sirdata' => self::SERVICE_SIRDATA,
            'service_matomo_no_cookie' => self::SERVICE_MATOMO_NO_COOKIE,
        ];

        $merged = $this->get();
        $imported = [];

        foreach ($map as $old => $new) {
            if (! array_key_exists($old, $legacy)) {
                continue;
            }
            $value = $legacy[$old];
            if ($old === 'rgpd_service_type' && is_string($value) && isset($serviceTypeAliases[$value])) {
                $value = $serviceTypeAliases[$value];
            }
            $merged[$new] = $value;
            $imported[] = $new;
        }

        if ($legacyWoo !== []) {
            $merged['woo_tracking'] = [
                'enabled' => ! empty($legacyWoo['woo_tracking_enabled']),
                'add_to_cart_btn_class' => (string) ($legacyWoo['add_to_cart_btn_class'] ?? ''),
                'remove_from_cart_btn_class' => (string) ($legacyWoo['remove_from_cart_btn_class'] ?? ''),
            ];
            $imported[] = 'woo_tracking';
        }

        // Une config aky-gdpr existante était de fait active : on active le module.
        if ($imported !== []) {
            $merged['enabled'] = true;
        }

        $stored = $this->save($merged);

        return [
            'migrated' => true,
            'importedFields' => array_values(array_unique($imported)),
            'settings' => $stored,
        ];
    }

    /**
     * Normalise + assainit un tableau d'entrée vers le schéma complet.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function normalize(array $input): array
    {
        $defaults = self::defaults();

        $serviceType = isset($input['service_type']) ? (string) $input['service_type'] : '';
        if (! in_array($serviceType, self::serviceTypes(), true)) {
            $serviceType = '';
        }

        $textKeys = [
            'site_name', 'address',
            'legal_company_name', 'legal_form', 'legal_capital', 'legal_rcs', 'legal_siret',
            'legal_tva', 'legal_publication_director', 'legal_host_name', 'legal_host_address', 'legal_host_phone',
            'gtm', 'pixel_fb', 'matomo_site_id', 'sirdata_user', 'sirdata_site', 'cookie_button_content',
        ];

        $out = $defaults;
        $out['enabled'] = ! empty($input['enabled']);
        $out['service_type'] = $serviceType;
        $out['mail'] = isset($input['mail']) ? sanitize_email((string) $input['mail']) : '';
        $out['contact_url'] = isset($input['contact_url']) ? esc_url_raw((string) $input['contact_url']) : '';
        $out['matomo_url'] = isset($input['matomo_url']) ? esc_url_raw((string) $input['matomo_url']) : '';
        $out['matomo_js_path'] = isset($input['matomo_js_path']) ? esc_url_raw((string) $input['matomo_js_path']) : '';
        $out['matomo_url_tag'] = isset($input['matomo_url_tag']) ? esc_url_raw((string) $input['matomo_url_tag']) : '';
        $out['youtube'] = ! empty($input['youtube']);
        $out['hide_cookie_button'] = ! empty($input['hide_cookie_button']);
        $out['hide_banner'] = ! empty($input['hide_banner']);
        $out['tac_use_cdn'] = ! array_key_exists('tac_use_cdn', $input) || ! empty($input['tac_use_cdn']);
        $out['page_mentions_id'] = isset($input['page_mentions_id']) ? max(0, (int) $input['page_mentions_id']) : 0;
        $out['page_privacy_id'] = isset($input['page_privacy_id']) ? max(0, (int) $input['page_privacy_id']) : 0;
        $out['page_cookies_id'] = isset($input['page_cookies_id']) ? max(0, (int) $input['page_cookies_id']) : 0;

        foreach ($textKeys as $key) {
            $out[$key] = isset($input[$key]) ? sanitize_text_field((string) $input[$key]) : '';
        }

        $woo = is_array($input['woo_tracking'] ?? null) ? $input['woo_tracking'] : [];
        $out['woo_tracking'] = [
            'enabled' => ! empty($woo['enabled']),
            'add_to_cart_btn_class' => isset($woo['add_to_cart_btn_class']) ? sanitize_text_field((string) $woo['add_to_cart_btn_class']) : '',
            'remove_from_cart_btn_class' => isset($woo['remove_from_cart_btn_class']) ? sanitize_text_field((string) $woo['remove_from_cart_btn_class']) : '',
        ];

        $tagService = $this->tacTagService();
        $effectiveType = $serviceType !== '' ? $serviceType : self::SERVICE_TARTEAUCITRON;
        $tags = $tagService->normalizeTags($input['tac_tags'] ?? [], $effectiveType);
        if ($tags === []) {
            $tags = $tagService->migrateLegacyTags($input);
            $tags = $tagService->normalizeTags($tags, $effectiveType);
        }
        $out['tac_tags'] = $tags;

        $legacyAppearance = get_option('akyos_updates_appearance', []);
        $legacyPrimary = is_array($legacyAppearance) ? self::sanitizeHex($legacyAppearance['primary'] ?? null) : null;
        $legacyLight = is_array($legacyAppearance) ? self::sanitizeHex($legacyAppearance['primary_light'] ?? null) : null;

        $out['theme_primary'] = self::sanitizeHex($input['theme_primary'] ?? null)
            ?? $legacyPrimary
            ?? self::sanitizeHex($defaults['theme_primary'])
            ?? '#0052FF';
        $out['theme_primary_light'] = self::sanitizeHex($input['theme_primary_light'] ?? null)
            ?? $legacyLight
            ?? self::sanitizeHex($defaults['theme_primary_light'])
            ?? '#4D7CFF';

        foreach (['theme_text', 'theme_text_muted', 'theme_border', 'theme_surface', 'theme_danger'] as $themeKey) {
            $out[$themeKey] = self::sanitizeHex($input[$themeKey] ?? null)
                ?? self::sanitizeHex($defaults[$themeKey])
                ?? (string) $defaults[$themeKey];
        }

        $radius = isset($input['theme_radius']) ? (int) $input['theme_radius'] : (int) $defaults['theme_radius'];
        $out['theme_radius'] = max(8, min(32, $radius));
        $out['theme_font'] = self::sanitizeFontStack($input['theme_font'] ?? '');

        $out['banner_title'] = isset($input['banner_title']) ? sanitize_text_field((string) $input['banner_title']) : '';

        $position = (string) ($input['cookie_button_position'] ?? $defaults['cookie_button_position']);
        $out['cookie_button_position'] = in_array($position, ['bottom-left', 'bottom-right'], true)
            ? $position
            : (string) $defaults['cookie_button_position'];
        $out['cookie_button_always_visible'] = ! empty($input['cookie_button_always_visible']);

        $orientation = (string) ($input['tac_orientation'] ?? $defaults['tac_orientation']);
        $out['tac_orientation'] = in_array($orientation, ['bottom', 'top', 'middle'], true)
            ? $orientation
            : (string) $defaults['tac_orientation'];
        $out['tac_show_accept_all'] = ! array_key_exists('tac_show_accept_all', $input) || ! empty($input['tac_show_accept_all']);
        $out['tac_show_deny_all'] = ! array_key_exists('tac_show_deny_all', $input) || ! empty($input['tac_show_deny_all']);
        $out['tac_high_privacy'] = ! array_key_exists('tac_high_privacy', $input) || ! empty($input['tac_high_privacy']);
        $out['tac_google_consent_mode'] = ! array_key_exists('tac_google_consent_mode', $input) || ! empty($input['tac_google_consent_mode']);
        $out['tac_group_services'] = ! empty($input['tac_group_services']);

        $out['gcm_jobs_enabled'] = self::normalizeGcmJobs(
            $input['gcm_jobs_enabled'] ?? null,
            self::tagsIncludeGtm($out['tac_tags'])
        );

        foreach ($tagService->syncLegacyFields($tags) as $legacyKey => $legacyValue) {
            if ($legacyKey === 'youtube') {
                $out['youtube'] = (bool) $legacyValue;
            } elseif (in_array($legacyKey, ['matomo_url', 'matomo_url_tag'], true)) {
                $out[$legacyKey] = esc_url_raw((string) $legacyValue);
            } else {
                $out[$legacyKey] = sanitize_text_field((string) $legacyValue);
            }
        }

        return $out;
    }

    /** @return list<string> */
    private static function normalizeGcmJobs(mixed $raw, bool $hasGtm = false): array
    {
        $allowed = TarteaucitronCatalogService::GTM_GCM_JOBS;
        if (! is_array($raw)) {
            return $hasGtm ? $allowed : [];
        }

        $out = [];
        foreach ($raw as $id) {
            $id = sanitize_key((string) $id);
            if ($id !== '' && in_array($id, $allowed, true) && ! in_array($id, $out, true)) {
                $out[] = $id;
            }
        }

        return $out;
    }

    /** @param list<array{id: string, params: array<string, string>}> $tags */
    private static function tagsIncludeGtm(array $tags): bool
    {
        foreach ($tags as $tag) {
            if (in_array((string) ($tag['id'] ?? ''), TarteaucitronCatalogService::GCM_TRIGGER_TAG_IDS, true)) {
                return true;
            }
        }

        return false;
    }
}
