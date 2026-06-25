<?php

namespace AkyosUpdates\Service;

/**
 * Normalisation des tags tarteaucitron configurés + rendu des scripts footer.
 */
final class TarteaucitronTagService
{
    public function __construct(
        private TarteaucitronCatalogService $catalog
    ) {
    }

    /**
     * Migre les champs legacy (gtm, pixel_fb…) vers tac_tags si vide.
     *
     * @param array<string, mixed> $settings
     * @return list<array{id: string, params: array<string, string>}>
     */
    public function migrateLegacyTags(array $settings): array
    {
        $existing = $this->normalizeTags($settings['tac_tags'] ?? []);
        if ($existing !== []) {
            return $existing;
        }

        $tags = [];

        $gtms = array_values(array_filter(array_map('trim', explode('|', (string) ($settings['gtm'] ?? '')))));
        if ($gtms !== []) {
            $tags[] = [
                'id' => 'multiplegoogletagmanager',
                'params' => ['multiplegoogletagmanagerId' => implode('|', $gtms)],
            ];
        }

        $pixel = trim((string) ($settings['pixel_fb'] ?? ''));
        if ($pixel !== '') {
            $tags[] = ['id' => 'facebookpixel', 'params' => ['facebookpixelId' => $pixel]];
        }

        if (! empty($settings['youtube'])) {
            $tags[] = ['id' => 'youtube', 'params' => []];
        }

        $matomoSiteId = trim((string) ($settings['matomo_site_id'] ?? ''));
        $matomoUrl = trim((string) ($settings['matomo_url'] ?? ''));
        if ($matomoSiteId !== '') {
            $params = ['matomoId' => $matomoSiteId];
            if ($matomoUrl !== '') {
                $params['matomoHost'] = $matomoUrl;
            }
            $tags[] = ['id' => 'matomocloud', 'params' => $params];
        }

        $matomoTm = trim((string) ($settings['matomo_url_tag'] ?? ''));
        if ($matomoTm !== '') {
            $tags[] = ['id' => 'matomotm', 'params' => ['matomotmUrl' => $matomoTm]];
        }

        return $tags;
    }

    /**
     * @param mixed $raw
     * @return list<array{id: string, params: array<string, string>}>
     */
    public function normalizeTags(mixed $raw, string $serviceType = RgpdSettingsService::SERVICE_TARTEAUCITRON): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $cmpCatalog = new CmpCatalogService($this->catalog);
        $out = [];

        foreach ($raw as $item) {
            if (! is_array($item)) {
                continue;
            }
            $id = sanitize_key((string) ($item['id'] ?? ''));
            if ($id === '' || ! $cmpCatalog->hasService($serviceType, $id)) {
                continue;
            }
            $params = [];
            if (is_array($item['params'] ?? null)) {
                foreach ($item['params'] as $key => $value) {
                    $k = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $key) ?? '';
                    if ($k === '') {
                        continue;
                    }
                    $params[$k] = sanitize_text_field((string) $value);
                }
            }
            $out[] = ['id' => $id, 'params' => $params];
        }

        return $out;
    }

    /**
     * Rend les blocs <script> tarteaucitron pour le footer.
     *
     * @param list<array{id: string, params: array<string, string>}> $tags
     * @param list<string> $gcmJobsEnabled
     * @return list<string>
     */
    public function renderScripts(array $tags, array $gcmJobsEnabled = []): array
    {
        $scripts = [];
        $pushedJobs = [];
        $needsGcm = false;

        foreach ($tags as $tag) {
            if (in_array($tag['id'], TarteaucitronCatalogService::GCM_TRIGGER_TAG_IDS, true)) {
                $needsGcm = true;
            }

            $def = $this->catalog->get($tag['id']);
            if ($def === null) {
                continue;
            }

            $lines = [];
            foreach ($tag['params'] as $key => $value) {
                if ($value === '') {
                    continue;
                }
                $jsValue = $this->toJsValue($key, $value);
                $lines[] = 'tarteaucitron.user.' . $key . ' = ' . $jsValue . ';';
            }

            if ($tag['id'] === 'facebookpixel' && ! isset($tag['params']['facebookpixelMore'])) {
                $lines[] = 'tarteaucitron.user.facebookpixelMore = function () {};';
            }

            $jobs = is_array($def['jobs'] ?? null) ? $def['jobs'] : [$tag['id']];

            foreach ($jobs as $job) {
                $job = (string) $job;
                if ($job === '' || isset($pushedJobs[$job])) {
                    continue;
                }
                $lines[] = "(tarteaucitron.job = tarteaucitron.job || []).push('" . esc_js($job) . "');";
                $pushedJobs[$job] = true;
            }

            if ($lines !== []) {
                $scripts[] = implode("\n            ", $lines);
            }
        }

        if ($needsGcm && $gcmJobsEnabled !== []) {
            $gcmLines = [];
            foreach ($gcmJobsEnabled as $job) {
                if (! in_array($job, TarteaucitronCatalogService::GTM_GCM_JOBS, true) || isset($pushedJobs[$job])) {
                    continue;
                }
                $gcmLines[] = "(tarteaucitron.job = tarteaucitron.job || []).push('" . esc_js($job) . "');";
                $pushedJobs[$job] = true;
            }
            if ($gcmLines !== []) {
                $scripts[] = implode("\n            ", $gcmLines);
            }
        }

        return $scripts;
    }

    /**
     * @param list<array{id: string, params: array<string, string>}> $tags
     * @return array<string, mixed>
     */
    public function syncLegacyFields(array $tags): array
    {
        $legacy = [
            'gtm' => '',
            'pixel_fb' => '',
            'youtube' => false,
            'matomo_url' => '',
            'matomo_site_id' => '',
            'matomo_url_tag' => '',
        ];

        foreach ($tags as $tag) {
            $id = $tag['id'];
            $params = $tag['params'];

            if ($id === 'multiplegoogletagmanager') {
                $legacy['gtm'] = $params['multiplegoogletagmanagerId'] ?? '';
            } elseif ($id === 'googletagmanager') {
                $legacy['gtm'] = $params['googletagmanagerId'] ?? '';
            } elseif ($id === 'facebookpixel') {
                $legacy['pixel_fb'] = $params['facebookpixelId'] ?? '';
            } elseif ($id === 'youtube') {
                $legacy['youtube'] = true;
            } elseif ($id === 'matomocloud') {
                $legacy['matomo_site_id'] = $params['matomoId'] ?? '';
                $legacy['matomo_url'] = $params['matomoHost'] ?? '';
            } elseif ($id === 'matomotm') {
                $legacy['matomo_url_tag'] = $params['matomotmUrl'] ?? '';
            }
        }

        return $legacy;
    }

    private function toJsValue(string $key, string $value): string
    {
        if ($key === 'multiplegoogletagmanagerId') {
            $ids = array_values(array_filter(array_map('trim', explode('|', $value))));
            if ($ids === []) {
                return wp_json_encode([]);
            }

            return wp_json_encode($ids);
        }

        if (str_ends_with(strtolower($key), 'id') && preg_match('/^\d+$/', $value)) {
            return $value;
        }

        return wp_json_encode($value);
    }
}
