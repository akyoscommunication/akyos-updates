<?php

namespace AkyosUpdates\Frontend;

use AkyosUpdates\Service\CmpCatalogService;
use AkyosUpdates\Service\CmpTagRenderer;
use AkyosUpdates\Service\RgpdSettingsService;
use AkyosUpdates\Service\TarteaucitronCatalogService;
use AkyosUpdates\Service\TarteaucitronTagService;

/**
 * Module front du consentement RGPD : bannière tarteaucitron, SirData ou Matomo no-cookie,
 * + injection des tags (GTM / Pixel FB / Matomo / YouTube) pilotée par le consentement.
 */
final class RgpdFrontend
{
    public const STYLE_HANDLE = 'akyos-updates-rgpd';
    public const TAC_STYLE_HANDLE = 'akyos-updates-rgpd-tac';
    public const TAC_HANDLE = 'akyos-updates-rgpd-tac';
    public const SCRIPT_HANDLE = 'akyos-updates-rgpd-main';

    private const VENDOR_BASE = AKYOS_UPDATES_PLUGIN_URL . 'assets/rgpd/vendor/tarteaucitronjs/';

    private ?CmpTagRenderer $tagRenderer = null;

    public function __construct(private RgpdSettingsService $settings)
    {
    }

    public function register(): void
    {
        if (is_admin()) {
            return;
        }

        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets'], 99999);
        add_action('wp_head', [$this, 'renderHead'], PHP_INT_MAX);
        add_action('wp_footer', [$this, 'renderFooter']);
    }

    public function enqueueAssets(): void
    {
        if (! $this->settings->isActiveOnFrontend()) {
            return;
        }

		wp_enqueue_style(
            self::STYLE_HANDLE,
            AKYOS_UPDATES_PLUGIN_URL . 'assets/rgpd/rgpd.css',
            [self::TAC_STYLE_HANDLE],
            AKYOS_UPDATES_VERSION
        );
        wp_add_inline_style(self::STYLE_HANDLE, $this->settings->themeCssBlock());

        $settings = $this->settings->get();
        if (($settings['service_type'] ?? '') !== RgpdSettingsService::SERVICE_TARTEAUCITRON) {
            return;
        }

        $useCdn = ! empty($settings['tac_use_cdn']);
        $base = $useCdn ? TarteaucitronCatalogService::cdnBase() . '/' : self::VENDOR_BASE;
        $version = $useCdn ? null : AKYOS_UPDATES_VERSION;

        wp_enqueue_style(
            self::TAC_STYLE_HANDLE,
            $base . 'css/tarteaucitron.min.css',
            [],
            $version
        );

        wp_enqueue_script(
            self::TAC_HANDLE,
            $base . 'tarteaucitron.js',
            [],
            $version,
            false
        );

        $forceLanguage = $this->resolveTarteaucitronLanguage();
        wp_add_inline_script(
            self::TAC_HANDLE,
            'window.tarteaucitronForceLanguage = ' . wp_json_encode($forceLanguage) . ';',
            'before'
        );

        $tags = $this->tagRenderer()->resolveTags($settings);
        $gcmJobs = is_array($settings['gcm_jobs_enabled'] ?? null)
            ? $settings['gcm_jobs_enabled']
            : RgpdSettingsService::defaultGcmJobsEnabled();
        $tagScripts = $this->tagRenderer()->renderFooterScripts(RgpdSettingsService::SERVICE_TARTEAUCITRON, $tags, $gcmJobs);
        if ($tagScripts !== []) {
            wp_add_inline_script(
                self::TAC_HANDLE,
                implode("\n", $tagScripts),
                'after'
            );
        }

        wp_add_inline_script(
            self::TAC_HANDLE,
            $this->tarteaucitronInitScript($this->resolvePrivacyUrl($settings)),
            'after'
        );

        wp_enqueue_script(
            self::SCRIPT_HANDLE,
            AKYOS_UPDATES_PLUGIN_URL . 'assets/rgpd/main.js',
            [self::TAC_HANDLE],
            AKYOS_UPDATES_VERSION,
            false
        );
    }

    public function renderHead(): void
    {
        if (! $this->settings->isActiveOnFrontend()) {
            return;
        }

        $settings = $this->settings->get();
        $privacyUrl = $this->resolvePrivacyUrl($settings);
        $forceLanguage = function_exists('pll_current_language') ? (string) pll_current_language() : '';

        include AKYOS_UPDATES_PLUGIN_DIR . 'assets/rgpd/head.php';
    }

    public function renderFooter(): void
    {
        if (! $this->settings->isActiveOnFrontend()) {
            return;
        }

        $settings = $this->settings->get();

        include AKYOS_UPDATES_PLUGIN_DIR . 'assets/rgpd/footer.php';
    }

    private function tagRenderer(): CmpTagRenderer
    {
        if ($this->tagRenderer === null) {
            $catalog = new TarteaucitronCatalogService();
            $this->tagRenderer = new CmpTagRenderer(new TarteaucitronTagService($catalog), new CmpCatalogService($catalog));
        }

        return $this->tagRenderer;
    }

    private function tarteaucitronInitScript(string $privacyUrl): string
    {
        $params = wp_json_encode([
            'privacyUrl' => $privacyUrl,
            'bodyPosition' => 'bottom',
            'hashtag' => '#tarteaucitron',
            'cookieName' => 'tarteaucitron',
            'orientation' => 'bottom',
            'groupServices' => false,
            'showDetailsOnClick' => true,
            'serviceDefaultState' => 'wait',
            'showAlertSmall' => false,
            'cookieslist' => false,
            'showIcon' => false,
            'iconPosition' => 'BottomRight',
            'adblocker' => false,
            'DenyAllCta' => true,
            'AcceptAllCta' => true,
            'highPrivacy' => true,
            'handleBrowserDNTRequest' => false,
            'removeCredit' => true,
            'moreInfoLink' => true,
            'useExternalCss' => true,
            'useExternalJs' => false,
            'readmoreLink' => '/utilisation-des-cookies',
            'mandatory' => true,
            'mandatoryCta' => true,
            'googleConsentMode' => true,
            'partnersList' => false,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return 'tarteaucitron.init(' . $params . ');';
    }

    private function resolveTarteaucitronLanguage(): string
    {
        if (function_exists('pll_current_language')) {
            $lang = (string) pll_current_language();
            if ($lang !== '') {
                return $lang;
            }
        }

        $locale = (string) get_locale();
        if (str_starts_with($locale, 'fr')) {
            return 'fr';
        }
        if (str_starts_with($locale, 'en')) {
            return 'en';
        }

        return substr($locale, 0, 2) ?: 'fr';
    }

    /** @param array<string, mixed> $settings */
    private function resolvePrivacyUrl(array $settings): string
    {
        $privacyId = (int) ($settings['page_privacy_id'] ?? 0);
        if ($privacyId > 0) {
            if (function_exists('pll_get_post')) {
                $translated = pll_get_post($privacyId, get_locale());
                if ($translated) {
                    $permalink = get_permalink($translated);
                    if (is_string($permalink) && $permalink !== '') {
                        return $permalink;
                    }
                }
            }
            $permalink = get_permalink($privacyId);
            if (is_string($permalink) && $permalink !== '') {
                return $permalink;
            }
        }

        return home_url('/politique-de-conservation-de-donnees');
    }
}
