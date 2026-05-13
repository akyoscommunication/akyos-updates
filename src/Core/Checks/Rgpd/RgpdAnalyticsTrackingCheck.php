<?php

namespace AkyosUpdates\Core\Checks\Rgpd;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use AkyosUpdates\Service\PluginService;

final class RgpdAnalyticsTrackingCheck implements CheckInterface
{
    private const HOMEPAGE_SIGNALS_TRANSIENT = 'akyos_updates_rgpd_analytics_homepage_signals_v2';
    private const HOMEPAGE_SIGNALS_TTL = 15 * MINUTE_IN_SECONDS;

    public function getId(): string
    {
        return 'rgpd.analytics_tracking';
    }

    public function getCategory(): string
    {
        return 'RGPD';
    }

    public function getTitle(): string
    {
        return 'Google Analytics / Tag Manager';
    }
    public function getSuccessMessage(): string
    {
        return 'Point validé après action corrective.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $pluginSignals = $this->signalsFromActivePlugins();
        $home = $pluginSignals !== [] ? ['signals' => [], 'fetchError' => null] : $this->signalsFromHomepageHtml();

        $signals = array_merge($pluginSignals, $home['signals']);
        $unique = [];
        foreach ($signals as $signal) {
            $k = ($signal['type'] ?? '') . '|' . ($signal['detail'] ?? '');
            $unique[$k] = $signal;
        }
        $signals = array_values($unique);

        $detected = $signals !== [];
        $fetchError = $home['fetchError'];

        if ($detected) {
            $message = 'Traceurs ou réglages typiques GA / GTM repérés : ' . implode(
                ' ; ',
                array_map(
                    static fn(array $s): string => sprintf('%s (%s)', $s['label'] ?? '', $s['detail'] ?? ''),
                    $signals
                )
            ) . '.';
        } elseif ($fetchError !== null && $fetchError !== '') {
            $message = sprintf(
                'Impossible d’analyser le HTML de la page d’accueil (%s). Aucune extension GA/GTM listée ci-dessous non plus.',
                $fetchError
            );
        } else {
            $message = 'Aucun script GA / GTM évident sur la page d’accueil ni extension Analytics/GTM courante listée comme active.';
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'info',
            'neutral',
            $message,
            false,
            null,
            [
                'detected' => $detected,
                'signals' => $signals,
                'fetchError' => $fetchError,
            ],
            false
        );
    }

    /** @return array<int, array{type: string, label: string, detail: string}> */
    private function signalsFromActivePlugins(): array
    {
        $out = [];
        $files = PluginService::listActivePluginFiles();
        $markers = [
            'google-analytics-for-wordpress' => ['label' => 'MonsterInsights', 'detail' => 'Extension active'],
            'duracelltomi-google-tag-manager' => ['label' => 'GTM4WP', 'detail' => 'Extension active'],
            'google-site-kit' => ['label' => 'Site Kit', 'detail' => 'Extension active'],
            'gtm-kit' => ['label' => 'GTM Kit', 'detail' => 'Extension active'],
            'woocommerce-google-analytics-integration' => ['label' => 'WooCommerce Google Analytics', 'detail' => 'Extension active'],
            'pixelyoursite' => ['label' => 'PixelYourSite', 'detail' => 'Extension active'],
        ];

        foreach ($files as $file) {
            $fileLower = mb_strtolower((string) $file);
            foreach ($markers as $needle => $meta) {
                if (str_contains($fileLower, $needle)) {
                    $out[] = [
                        'type' => 'plugin',
                        'label' => $meta['label'],
                        'detail' => $meta['detail'] . ' — ' . $file,
                    ];
                }
            }
        }

        return $out;
    }

    /** @return array{signals: array<int, array{type: string, label: string, detail: string}>, fetchError: ?string} */
    private function signalsFromHomepageHtml(): array
    {
        $cached = get_transient(self::HOMEPAGE_SIGNALS_TRANSIENT);
        if (is_array($cached)) {
            $signals = is_array($cached['signals'] ?? null) ? $cached['signals'] : [];
            $fetchError = $cached['fetchError'] ?? null;
            return [
                'signals' => $signals,
                'fetchError' => is_string($fetchError) || $fetchError === null ? $fetchError : null,
            ];
        }

        $url = home_url('/');
        $response = wp_remote_get($url, [
            'timeout' => 4,
            'redirection' => 3,
            'sslverify' => apply_filters('https_local_ssl_verify', false),
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; AkyosUpdates/1.0; +https://akyos.com)',
            ],
        ]);

        if (is_wp_error($response)) {
            $result = [
                'signals' => [],
                'fetchError' => $response->get_error_message(),
            ];
            set_transient(self::HOMEPAGE_SIGNALS_TRANSIENT, $result, self::HOMEPAGE_SIGNALS_TTL);
            return $result;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 400) {
            $result = [
                'signals' => [],
                'fetchError' => 'HTTP ' . (string) $code,
            ];
            set_transient(self::HOMEPAGE_SIGNALS_TRANSIENT, $result, self::HOMEPAGE_SIGNALS_TTL);
            return $result;
        }

        $body = (string) wp_remote_retrieve_body($response);
        if ($body === '') {
            $result = ['signals' => [], 'fetchError' => null];
            set_transient(self::HOMEPAGE_SIGNALS_TRANSIENT, $result, self::HOMEPAGE_SIGNALS_TTL);
            return $result;
        }

        $lower = mb_strtolower($body);
        $out = [];
        $matchedUas = $this->extractMatches($body, '/\bUA-\d{4,}-\d+\b/i');
        $matchedGa4 = $this->extractMatches($body, '/\bG-[A-Z0-9]{4,}\b/i');
        $matchedGtm = $this->extractMatches($body, '/\bGTM-[A-Z0-9]{4,}\b/i');

        if (str_contains($lower, 'googletagmanager.com')) {
            $out[] = [
                'type' => 'html',
                'label' => 'Google Tag Manager',
                'detail' => 'Référence googletagmanager.com dans le HTML',
            ];
        }

        if (str_contains($lower, 'google-analytics.com') || str_contains($lower, '/gtag/js')) {
            $ids = array_values(array_unique(array_merge($matchedGa4, $matchedGtm)));
            $out[] = [
                'type' => 'html',
                'label' => 'Google Analytics (gtag.js)',
                'detail' => $ids !== []
                    ? 'Script analytics / gtag détecté — identifiant(s) : ' . implode(', ', $ids)
                    : 'Script analytics / gtag détecté',
            ];
        }

        if ($matchedUas !== []) {
            $out[] = [
                'type' => 'html',
                'label' => 'Universal Analytics',
                'detail' => 'Identifiant(s) détecté(s) : ' . implode(', ', $matchedUas),
            ];
        }

        if ($matchedGa4 !== []) {
            $out[] = [
                'type' => 'html',
                'label' => 'Google Analytics 4',
                'detail' => 'Identifiant(s) détecté(s) : ' . implode(', ', $matchedGa4),
            ];
        }

        if ($matchedGtm !== []) {
            $out[] = [
                'type' => 'html',
                'label' => 'Google Tag Manager',
                'detail' => 'Conteneur(s) détecté(s) : ' . implode(', ', $matchedGtm),
            ];
        }

        if (str_contains($lower, 'googletagservices.com')) {
            $out[] = [
                'type' => 'html',
                'label' => 'Google Tag Services',
                'detail' => 'Référence googletagservices.com',
            ];
        }

        $result = ['signals' => $out, 'fetchError' => null];
        set_transient(self::HOMEPAGE_SIGNALS_TRANSIENT, $result, self::HOMEPAGE_SIGNALS_TTL);

        return $result;
    }

    private function extractMatches(string $body, string $pattern): array
    {
        $matches = [];
        if (! preg_match_all($pattern, $body, $rawMatches) || ! isset($rawMatches[0])) {
            return [];
        }
        foreach ((array) $rawMatches[0] as $value) {
            $normalized = mb_strtoupper((string) $value);
            if ($normalized !== '') {
                $matches[$normalized] = true;
            }
        }

        return array_keys($matches);
    }
}
