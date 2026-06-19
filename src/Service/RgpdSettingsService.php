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
            'id_client' => '',

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
        ];
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
            'rgpd_id_client' => 'id_client',
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
            'site_name', 'address', 'id_client',
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
}
