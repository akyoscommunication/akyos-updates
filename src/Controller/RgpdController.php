<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Service\CmpCatalogService;
use AkyosUpdates\Service\CompanyLookupService;
use AkyosUpdates\Service\HostLookupService;
use AkyosUpdates\Service\JsonService;
use AkyosUpdates\Service\RgpdLegalPagesService;
use AkyosUpdates\Service\RgpdSettingsService;
use AkyosUpdates\Service\TarteaucitronCatalogService;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Endpoints REST du module RGPD : réglages, migration, lookup entreprise/hébergeur, génération des pages.
 */
final class RgpdController
{
    public function __construct(
        private RgpdSettingsService $settings,
        private CompanyLookupService $companyLookup,
        private HostLookupService $hostLookup,
        private TarteaucitronCatalogService $tacCatalog,
        private CmpCatalogService $cmpCatalog,
        private RgpdLegalPagesService $legalPages
    ) {
    }

    public function register(): void
    {
        register_rest_route('akyos-updates/v1', '/rgpd/settings', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'getSettings'],
                'permission_callback' => [$this, 'canManage'],
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'saveSettings'],
                'permission_callback' => [$this, 'canManage'],
                'args' => [
                    'settings' => ['required' => true, 'type' => 'object'],
                ],
            ],
        ]);

        register_rest_route('akyos-updates/v1', '/rgpd/pages', [
            'methods' => 'GET',
            'callback' => [$this, 'listPages'],
            'permission_callback' => [$this, 'canManage'],
        ]);

        register_rest_route('akyos-updates/v1', '/rgpd/migrate', [
            'methods' => 'POST',
            'callback' => [$this, 'migrate'],
            'permission_callback' => [$this, 'canManage'],
        ]);

        register_rest_route('akyos-updates/v1', '/rgpd/company-lookup', [
            'methods' => 'POST',
            'callback' => [$this, 'companyLookup'],
            'permission_callback' => [$this, 'canManage'],
            'args' => [
                'query' => ['required' => true, 'type' => 'string'],
            ],
        ]);

        register_rest_route('akyos-updates/v1', '/rgpd/host-lookup', [
            'methods' => 'POST',
            'callback' => [$this, 'hostLookup'],
            'permission_callback' => [$this, 'canManage'],
        ]);

        register_rest_route('akyos-updates/v1', '/rgpd/cmp-catalog', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'cmpCatalog'],
                'permission_callback' => [$this, 'canManage'],
                'args' => [
                    'service_type' => ['required' => false, 'type' => 'string'],
                ],
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'cmpCatalogSync'],
                'permission_callback' => [$this, 'canManage'],
            ],
        ]);

        register_rest_route('akyos-updates/v1', '/rgpd/tarteaucitron-catalog', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'tarteaucitronCatalog'],
                'permission_callback' => [$this, 'canManage'],
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'tarteaucitronCatalogSync'],
                'permission_callback' => [$this, 'canManage'],
            ],
        ]);

        register_rest_route('akyos-updates/v1', '/rgpd/generate-pages', [
            'methods' => 'POST',
            'callback' => [$this, 'generatePages'],
            'permission_callback' => [$this, 'canManage'],
            'args' => [
                'kinds' => ['required' => false, 'type' => 'array'],
            ],
        ]);
    }

    public function canManage(): bool
    {
        return current_user_can('manage_options');
    }

    public function getSettings(): WP_REST_Response|WP_Error
    {
        return JsonService::wrap(fn(): array => [
            'settings' => $this->settings->get(),
            'meta' => $this->meta(),
        ]);
    }

    public function saveSettings(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $input = $request->get_param('settings');
        if (! is_array($input)) {
            return new WP_Error('akyos_updates_rgpd_invalid', 'Paramètre settings invalide.', ['status' => 400]);
        }

        return JsonService::wrap(fn(): array => [
            'success' => true,
            'message' => 'Réglages RGPD enregistrés.',
            'settings' => $this->settings->save($input),
            'meta' => $this->meta(),
        ]);
    }

    public function listPages(): WP_REST_Response|WP_Error
    {
        return JsonService::wrap(fn(): array => ['pages' => $this->pageOptions()]);
    }

    public function migrate(): WP_REST_Response|WP_Error
    {
        return JsonService::wrap(function (): array {
            $result = $this->settings->migrateFromLegacy();

            return [
                'success' => true,
                'migrated' => $result['migrated'],
                'importedFields' => $result['importedFields'],
                'message' => $result['migrated']
                    ? sprintf('Migration effectuée (%d champ(s) importé(s)).', count($result['importedFields']))
                    : 'Aucune donnée aky-gdpr à migrer.',
                'settings' => $result['settings'],
                'meta' => $this->meta(),
            ];
        });
    }

    public function companyLookup(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $query = (string) $request->get_param('query');

        return JsonService::wrap(fn(): array => $this->companyLookup->lookup($query));
    }

    public function hostLookup(): WP_REST_Response|WP_Error
    {
        return JsonService::wrap(fn(): array => $this->hostLookup->detect());
    }

    public function cmpCatalog(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        return JsonService::wrap(function () use ($request): array {
            $type = (string) $request->get_param('service_type');
            if ($type === '' || ! in_array($type, RgpdSettingsService::serviceTypes(), true)) {
                $type = RgpdSettingsService::SERVICE_TARTEAUCITRON;
            }
            $all = $this->cmpCatalog->forServiceType($type);

            return [
                'serviceType' => $type,
                'tagMode' => $all['tagMode'],
                'hint' => $all['hint'],
                'source' => $all['source'],
                'syncedAt' => $all['syncedAt'],
                'cdnBase' => $all['cdnBase'],
                'categories' => $all['categories'],
                'services' => $this->cmpCatalog->forUi($type),
            ];
        });
    }

    public function cmpCatalogSync(): WP_REST_Response|WP_Error
    {
        return JsonService::wrap(function (): array {
            $result = $this->tacCatalog->syncFromCdn(true);
            $all = $this->cmpCatalog->forServiceType(RgpdSettingsService::SERVICE_TARTEAUCITRON);

            return [
                'success' => $result['ok'],
                'message' => $result['message'],
                'count' => $result['count'],
                'serviceType' => RgpdSettingsService::SERVICE_TARTEAUCITRON,
                'tagMode' => $all['tagMode'],
                'hint' => $all['hint'],
                'syncedAt' => $all['syncedAt'],
                'source' => $all['source'],
                'cdnBase' => $all['cdnBase'],
                'categories' => $all['categories'],
                'services' => $this->cmpCatalog->forUi(RgpdSettingsService::SERVICE_TARTEAUCITRON),
            ];
        });
    }

    public function tarteaucitronCatalog(): WP_REST_Response|WP_Error
    {
        return JsonService::wrap(function (): array {
            $all = $this->tacCatalog->all();

            return [
                'source' => $all['source'],
                'cdnBase' => $all['cdnBase'],
                'syncedAt' => $all['syncedAt'],
                'categories' => $all['categories'],
                'services' => $this->tacCatalog->forUi(),
            ];
        });
    }

    public function tarteaucitronCatalogSync(): WP_REST_Response|WP_Error
    {
        return JsonService::wrap(function (): array {
            $result = $this->tacCatalog->syncFromCdn(true);
            $all = $this->tacCatalog->all();

            return [
                'success' => $result['ok'],
                'message' => $result['message'],
                'count' => $result['count'],
                'syncedAt' => $all['syncedAt'],
                'source' => $all['source'],
                'cdnBase' => $all['cdnBase'],
                'categories' => $all['categories'],
                'services' => $this->tacCatalog->forUi(),
            ];
        });
    }

    public function generatePages(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $kindsParam = $request->get_param('kinds');
        $kinds = [];
        if (is_array($kindsParam)) {
            foreach ($kindsParam as $kind) {
                if (is_string($kind)) {
                    $kinds[] = $kind;
                }
            }
        }

        return JsonService::wrap(function () use ($kinds): array {
            $result = $this->legalPages->generate($kinds);
            $successCount = count(array_filter($result['results'], static fn(array $r): bool => ! empty($r['success'])));

            return [
                'success' => $successCount > 0,
                'message' => sprintf('%d page(s) légale(s) générée(s) / mise(s) à jour.', $successCount),
                'results' => $result['results'],
                'settings' => $result['settings'],
                'meta' => $this->meta(),
            ];
        });
    }

    /** @return array{pages: list<array{id: int, title: string}>, serviceTypes: list<string>, serviceTypeMeta: array<string, array<string, string>>} */
    private function meta(): array
    {
        return [
            'pages' => $this->pageOptions(),
            'serviceTypes' => RgpdSettingsService::serviceTypes(),
            'serviceTypeMeta' => RgpdSettingsService::serviceTypeMeta(),
        ];
    }

    /** @return list<array{id: int, title: string}> */
    private function pageOptions(): array
    {
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => ['publish', 'private', 'draft'],
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'suppress_filters' => true,
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        $out = [];
        foreach ($pages as $page) {
            if (! $page instanceof WP_Post) {
                continue;
            }
            $out[] = [
                'id' => (int) $page->ID,
                'title' => $page->post_title !== '' ? $page->post_title : sprintf('(sans titre #%d)', (int) $page->ID),
            ];
        }

        return $out;
    }
}
