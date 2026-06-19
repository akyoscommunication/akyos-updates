<?php

namespace AkyosUpdates\Service;

/**
 * Catalogue tarteaucitron : sync CDN jsDelivr (services à jour) + snippets doc locaux en overlay.
 *
 * ponytail: pas d'API doc officielle → on parse tarteaucitron.services.js du CDN
 * et on garde le JSON local pour les exemples add/remove de la doc.
 */
final class TarteaucitronCatalogService
{
    private const CATALOG_FILE = AKYOS_UPDATES_PLUGIN_DIR . 'assets/rgpd/tarteaucitron-catalog.json';
    private const CACHE_KEY = 'akyos_updates_tac_cdn_catalog';
    private const CACHE_TTL = DAY_IN_SECONDS;
    private const CDN_PACKAGE = 'tarteaucitronjs';

    /** Jobs Google Consent Mode poussés avec GTM. */
    public const GTM_GCM_JOBS = [
        'gcmadsuserdata',
        'gcmadstorage',
        'gcmanalyticsstorage',
        'gcmpersonalization',
        'gcmfunctionality',
        'gcmsecurity',
    ];

    /** @var array<string, string> */
    private const TYPE_LABELS = [
        'analytic' => 'Mesure d\'audience',
        'api' => 'API',
        'ads' => 'Publicité',
        'comment' => 'Commentaires',
        'other' => 'Autre',
        'social' => 'Réseaux sociaux',
        'support' => 'Support',
        'video' => 'Vidéo',
        'google' => 'Google Consent Mode',
    ];

    /** @var array<string, mixed>|null */
    private ?array $catalog = null;

    public static function cdnBase(string $majorVersion = '1'): string
    {
        $major = preg_replace('/[^0-9]/', '', $majorVersion) ?: '1';

        return 'https://cdn.jsdelivr.net/npm/' . self::CDN_PACKAGE . '@' . $major;
    }

    /** @return array{ok: bool, message: string, count: int, syncedAt: string} */
    public function syncFromCdn(bool $force = false): array
    {
        if (! $force) {
            $cached = get_transient(self::CACHE_KEY);
            if (is_array($cached) && ! empty($cached['services'])) {
                return [
                    'ok' => true,
                    'message' => 'Catalogue CDN déjà en cache.',
                    'count' => count($cached['services']),
                    'syncedAt' => (string) ($cached['syncedAt'] ?? ''),
                ];
            }
        }

        $base = self::cdnBase();
        $response = wp_remote_get($base . '/tarteaucitron.services.js', [
            'timeout' => 20,
            'headers' => ['Accept' => 'application/javascript, text/javascript, */*'],
        ]);

        if (is_wp_error($response)) {
            return [
                'ok' => false,
                'message' => 'CDN injoignable : ' . $response->get_error_message(),
                'count' => 0,
                'syncedAt' => '',
            ];
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) {
            return [
                'ok' => false,
                'message' => 'CDN a répondu HTTP ' . $code . '.',
                'count' => 0,
                'syncedAt' => '',
            ];
        }

        $parsed = $this->parseServicesJs((string) wp_remote_retrieve_body($response));
        if ($parsed === []) {
            return [
                'ok' => false,
                'message' => 'Impossible de parser tarteaucitron.services.js depuis le CDN.',
                'count' => 0,
                'syncedAt' => '',
            ];
        }

        $catalog = $this->buildCatalog($parsed, $base);
        set_transient(self::CACHE_KEY, $catalog, self::CACHE_TTL);
        $this->catalog = $catalog;

        return [
            'ok' => true,
            'message' => sprintf('%d service(s) synchronisé(s) depuis jsDelivr.', count($parsed)),
            'count' => count($parsed),
            'syncedAt' => (string) $catalog['syncedAt'],
        ];
    }

    /** @return array{categories: list<string>, services: list<array<string, mixed>>, source: string, syncedAt: string, cdnBase: string} */
    public function all(): array
    {
        $catalog = $this->load();

        return [
            'categories' => is_array($catalog['categories'] ?? null) ? $catalog['categories'] : [],
            'services' => is_array($catalog['services'] ?? null) ? $catalog['services'] : [],
            'source' => (string) ($catalog['source'] ?? ''),
            'syncedAt' => (string) ($catalog['syncedAt'] ?? ''),
            'cdnBase' => (string) ($catalog['cdnBase'] ?? self::cdnBase()),
        ];
    }

    /** @return array<string, mixed>|null */
    public function get(string $id): ?array
    {
        foreach ($this->all()['services'] as $service) {
            if (($service['id'] ?? '') === $id) {
                return $service;
            }
        }

        return null;
    }

    /** @return list<array<string, mixed>> */
    public function forUi(): array
    {
        return array_map(static function (array $service): array {
            return [
                'id' => (string) ($service['id'] ?? ''),
                'name' => (string) ($service['name'] ?? ''),
                'category' => (string) ($service['category'] ?? ''),
                'jobs' => is_array($service['jobs'] ?? null) ? $service['jobs'] : [],
                'fields' => is_array($service['fields'] ?? null) ? $service['fields'] : [],
                'addCode' => (string) ($service['addCode'] ?? ''),
                'addCodePlacement' => (string) ($service['addCodePlacement'] ?? ''),
                'removeCode' => (string) ($service['removeCode'] ?? ''),
            ];
        }, $this->all()['services']);
    }

    /** @return array<string, mixed> */
    private function load(): array
    {
        if ($this->catalog !== null) {
            return $this->catalog;
        }

        $cached = get_transient(self::CACHE_KEY);
        if (is_array($cached) && ! empty($cached['services'])) {
            $this->catalog = $cached;

            return $this->catalog;
        }

        $sync = $this->syncFromCdn(true);
        if ($sync['ok'] && $this->catalog !== null) {
            return $this->catalog;
        }

        if (is_readable(self::CATALOG_FILE)) {
            $raw = json_decode((string) file_get_contents(self::CATALOG_FILE), true);
            if (is_array($raw)) {
                $raw['source'] = (string) ($raw['source'] ?? 'local');
                $raw['syncedAt'] = '';
                $raw['cdnBase'] = self::cdnBase();
                $this->catalog = $raw;

                return $this->catalog;
            }
        }

        $this->catalog = ['categories' => [], 'services' => [], 'source' => 'empty', 'syncedAt' => '', 'cdnBase' => self::cdnBase()];

        return $this->catalog;
    }

    /**
     * @param list<array{id: string, name: string, type: string, fields: list<string>}> $parsed
     * @return array<string, mixed>
     */
    private function buildCatalog(array $parsed, string $cdnBase): array
    {
        $localSnippets = $this->localSnippetsById();
        $services = [];

        foreach ($parsed as $item) {
            $id = $item['id'];
            $local = $localSnippets[$id] ?? [];
            $fields = [];
            foreach ($item['fields'] as $fieldKey) {
                $fields[] = [
                    'key' => $fieldKey,
                    'label' => $this->humanizeFieldKey($fieldKey),
                    'type' => str_contains(strtolower($fieldKey), 'url') ? 'url' : 'text',
                ];
            }

            $services[] = [
                'id' => $id,
                'name' => $item['name'],
                'category' => self::TYPE_LABELS[$item['type']] ?? ucfirst($item['type']),
                'jobs' => [$id],
                'fields' => $fields,
                'addCode' => (string) ($local['addCode'] ?? $this->generateAddCode($id, $item['fields'])),
                'addCodePlacement' => (string) ($local['addCodePlacement'] ?? ''),
                'removeCode' => (string) ($local['removeCode'] ?? ''),
            ];
        }

        usort($services, static fn(array $a, array $b): int => strcmp($a['name'], $b['name']));
        $categories = array_values(array_unique(array_map(static fn(array $s): string => $s['category'], $services)));
        sort($categories);

        return [
            'generatedAt' => gmdate('Y-m-d'),
            'syncedAt' => gmdate('c'),
            'source' => 'cdn.jsdelivr.net/npm/' . self::CDN_PACKAGE,
            'cdnBase' => $cdnBase,
            'categories' => $categories,
            'services' => $services,
        ];
    }

    /** @return array<string, array{addCode?: string, addCodePlacement?: string, removeCode?: string}> */
    private function localSnippetsById(): array
    {
        if (! is_readable(self::CATALOG_FILE)) {
            return [];
        }

        $raw = json_decode((string) file_get_contents(self::CATALOG_FILE), true);
        if (! is_array($raw['services'] ?? null)) {
            return [];
        }

        $map = [];
        foreach ($raw['services'] as $service) {
            if (! is_array($service) || empty($service['id'])) {
                continue;
            }
            $map[(string) $service['id']] = [
                'addCode' => (string) ($service['addCode'] ?? ''),
                'addCodePlacement' => (string) ($service['addCodePlacement'] ?? ''),
                'removeCode' => (string) ($service['removeCode'] ?? ''),
            ];
        }

        return $map;
    }

    /**
     * @return list<array{id: string, name: string, type: string, fields: list<string>}>
     */
    private function parseServicesJs(string $js): array
    {
        $blocks = preg_split('/(?=tarteaucitron\.services\.\w+\s*=\s*\{)/', $js) ?: [];
        $out = [];

        foreach ($blocks as $block) {
            if (! preg_match('/^tarteaucitron\.services\.(\w+)\s*=\s*\{/s', $block, $idMatch)) {
                continue;
            }

            $id = $idMatch[1];
            if (! preg_match('/"key"\s*:\s*"([^"]+)"/', $block, $keyMatch)) {
                continue;
            }
            if (! preg_match('/"name"\s*:\s*"([^"]+)"/', $block, $nameMatch)) {
                continue;
            }

            $type = 'other';
            if (preg_match('/"type"\s*:\s*"([^"]+)"/', $block, $typeMatch)) {
                $type = $typeMatch[1];
            }

            $fields = [];
            if (preg_match_all('/tarteaucitron\.user\.(\w+)/', $block, $fieldMatches)) {
                foreach ($fieldMatches[1] as $field) {
                    if (str_ends_with($field, 'More')) {
                        continue;
                    }
                    if (! in_array($field, $fields, true)) {
                        $fields[] = $field;
                    }
                }
            }

            $out[] = [
                'id' => $keyMatch[1] !== '' ? $keyMatch[1] : $id,
                'name' => $nameMatch[1],
                'type' => $type,
                'fields' => $fields,
            ];
        }

        return $out;
    }

    /** @param list<string> $fields */
    private function generateAddCode(string $id, array $fields): string
    {
        $parts = [];
        foreach ($fields as $field) {
            $parts[] = 'tarteaucitron.user.' . $field . " = '**" . $field . "**';";
        }
        $parts[] = "(tarteaucitron.job = tarteaucitron.job || []).push('" . $id . "');";

        return '<script> ' . implode(' ', $parts) . ' </script>';
    }

    private function humanizeFieldKey(string $key): string
    {
        $label = preg_replace('/([a-z])([A-Z])/', '$1 $2', $key) ?? $key;

        return ucfirst(strtolower(str_replace('_', ' ', $label)));
    }
}
