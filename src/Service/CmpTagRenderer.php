<?php

namespace AkyosUpdates\Service;

/**
 * Rendu front des tags selon le CMP actif.
 */
final class CmpTagRenderer
{
    public function __construct(
        private TarteaucitronTagService $tacTags,
        private CmpCatalogService $catalog
    ) {
    }

    /**
     * @param array<string, mixed> $settings
     * @return list<array{id: string, params: array<string, string>}>
     */
    public function resolveTags(array $settings): array
    {
        $serviceType = (string) ($settings['service_type'] ?? RgpdSettingsService::SERVICE_TARTEAUCITRON);
        $tags = $this->tacTags->normalizeTags($settings['tac_tags'] ?? [], $serviceType);
        if ($tags !== []) {
            return $tags;
        }

        return $this->tacTags->migrateLegacyTags($settings);
    }

    /**
     * Scripts pour wp_head (SirData legacy GTM / Pixel).
     *
     * @param list<array{id: string, params: array<string, string>}> $tags
     * @return list<string>
     */
    public function renderHeadScripts(array $tags): array
    {
        $scripts = [];

        foreach ($tags as $tag) {
            if ($tag['id'] === 'multiplegoogletagmanager' || $tag['id'] === 'googletagmanager') {
                $raw = $tag['params']['multiplegoogletagmanagerId']
                    ?? $tag['params']['googletagmanagerId']
                    ?? '';
                $ids = array_values(array_filter(array_map('trim', explode('|', $raw))));
                foreach ($ids as $gtmId) {
                    $scripts[] = $this->gtmScript($gtmId);
                }
            } elseif ($tag['id'] === 'facebookpixel') {
                $pixelId = $tag['params']['facebookpixelId'] ?? '';
                if ($pixelId !== '') {
                    $scripts[] = $this->facebookPixelScript($pixelId);
                }
            }
        }

        return $scripts;
    }

    /**
     * Scripts pour wp_footer (tarteaucitron jobs ou Matomo direct).
     *
     * @param list<array{id: string, params: array<string, string>}> $tags
     * @return list<string>
     */
    public function renderFooterScripts(string $serviceType, array $tags): array
    {
        if ($serviceType === RgpdSettingsService::SERVICE_TARTEAUCITRON) {
            return $this->tacTags->renderScripts($tags);
        }

        if ($serviceType === RgpdSettingsService::SERVICE_MATOMO_NO_COOKIE) {
            return $this->renderMatomoDirect($tags);
        }

        return [];
    }

    /**
     * @param list<array{id: string, params: array<string, string>}> $tags
     * @return list<string>
     */
    private function renderMatomoDirect(array $tags): array
    {
        $scripts = [];

        foreach ($tags as $tag) {
            if ($tag['id'] === 'matomotm') {
                $url = $tag['params']['matomotmUrl'] ?? '';
                if ($url !== '') {
                    $scripts[] = $this->matomoTmScript($url);
                }
                continue;
            }

            if (in_array($tag['id'], ['matomo', 'matomocloud', 'matomohightrack'], true)) {
                $siteId = $tag['params']['matomoId'] ?? '';
                $host = rtrim($tag['params']['matomoHost'] ?? '', '/') . '/';
                if ($siteId !== '' && $host !== '/') {
                    $scripts[] = $this->matomoTrackerScript($host, $siteId, $tag['id'] === 'matomohightrack');
                }
            }
        }

        return $scripts;
    }

    private function gtmScript(string $gtmId): string
    {
        $id = wp_json_encode($gtmId);

        return "(function (w, d, s, l, i) {\n"
            . "                w[l] = w[l] || [];\n"
            . "                w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });\n"
            . "                var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';\n"
            . "                j.async = true;\n"
            . "                j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;\n"
            . "                f.parentNode.insertBefore(j, f);\n"
            . "            })(window, document, 'script', 'dataLayer', {$id});";
    }

    private function facebookPixelScript(string $pixelId): string
    {
        $id = wp_json_encode($pixelId);

        return "!function (f, b, e, v, n, t, s) {\n"
            . "                if (f.fbq) return; n = f.fbq = function () { n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments); };\n"
            . "                if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = [];\n"
            . "                t = b.createElement(e); t.async = !0; t.src = v; s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s);\n"
            . "            }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');\n"
            . "            fbq('init', {$id});\n"
            . "            fbq('track', 'PageView');";
    }

    private function matomoTmScript(string $url): string
    {
        $src = wp_json_encode($url);

        return "var _mtm = window._mtm = window._mtm || [];\n"
            . "        _mtm.push({ 'mtm.startTime': (new Date().getTime()), 'event': 'mtm.Start' });\n"
            . "        (function () {\n"
            . "            var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];\n"
            . "            g.async = true;\n"
            . "            g.src = {$src};\n"
            . "            s.parentNode.insertBefore(g, s);\n"
            . "        })();";
    }

    private function matomoTrackerScript(string $host, string $siteId, bool $highPrivacy): string
    {
        $hostJson = wp_json_encode($host);
        $idJson = wp_json_encode($siteId);
        $phpFile = str_contains($host, 'matomo.cloud') ? 'matomo.php' : 'piwik.php';
        $jsFile = str_contains($host, 'matomo.cloud') ? 'matomo.js' : 'piwik.js';
        $consentLine = $highPrivacy ? "        _paq.push(['requireConsent']);\n" : '';

        return "var _paq = window._paq = window._paq || [];\n"
            . $consentLine
            . "        _paq.push(['setSiteId', {$idJson}]);\n"
            . "        _paq.push(['setTrackerUrl', {$hostJson} + '{$phpFile}']);\n"
            . "        _paq.push(['enableLinkTracking']);\n"
            . "        _paq.push(['trackPageView']);\n"
            . "        (function () {\n"
            . "            var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];\n"
            . "            g.async = true; g.defer = true; g.src = {$hostJson} + '{$jsFile}';\n"
            . "            s.parentNode.insertBefore(g, s);\n"
            . "        })();";
    }
}
