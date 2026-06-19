<?php

namespace AkyosUpdates\Service;

/**
 * Catalogue de tags par mode CMP (tarteaucitron, SirData, Matomo direct).
 */
final class CmpCatalogService
{
    /** Tags injectés en head avec SirData (complément legacy). */
    private const SIRDATA_TAG_IDS = [
        'multiplegoogletagmanager',
        'googletagmanager',
        'facebookpixel',
    ];

    /** Tags Matomo chargés directement (sans tarteaucitron). */
    private const MATOMO_TAG_IDS = [
        'matomotm',
        'matomo',
        'matomocloud',
        'matomohightrack',
    ];

    public function __construct(
        private TarteaucitronCatalogService $tacCatalog
    ) {
    }

    /**
     * @return array{
     *     tagMode: string,
     *     hint: string,
     *     source: string,
     *     syncedAt: string,
     *     cdnBase: string,
     *     categories: list<string>,
     *     services: list<array<string, mixed>>
     * }
     */
    public function forServiceType(string $serviceType): array
    {
        return match ($serviceType) {
            RgpdSettingsService::SERVICE_SIRDATA => $this->sirdataCatalog(),
            RgpdSettingsService::SERVICE_MATOMO_NO_COOKIE => $this->matomoCatalog(),
            default => $this->tarteaucitronCatalog(),
        };
    }

    public function hasService(string $serviceType, string $id): bool
    {
        return $this->get($serviceType, $id) !== null;
    }

    /** @return array<string, mixed>|null */
    public function get(string $serviceType, string $id): ?array
    {
        foreach ($this->forServiceType($serviceType)['services'] as $service) {
            if (($service['id'] ?? '') === $id) {
                return $service;
            }
        }

        return null;
    }

    /** @return list<array<string, mixed>> */
    public function forUi(string $serviceType): array
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
                'renderMode' => (string) ($service['renderMode'] ?? ''),
            ];
        }, $this->forServiceType($serviceType)['services']);
    }

    /** @return array<string, mixed> */
    private function tarteaucitronCatalog(): array
    {
        $all = $this->tacCatalog->all();

        return [
            'tagMode' => 'tarteaucitron',
            'hint' => 'Tags chargés après consentement via tarteaucitron.job (footer). Catalogue synchronisé depuis jsDelivr.',
            'source' => (string) ($all['source'] ?? ''),
            'syncedAt' => (string) ($all['syncedAt'] ?? ''),
            'cdnBase' => (string) ($all['cdnBase'] ?? TarteaucitronCatalogService::cdnBase()),
            'categories' => $all['categories'],
            'services' => array_map(fn(array $s): array => $s + ['renderMode' => 'tarteaucitron_job'], $all['services']),
        ];
    }

    /** @return array<string, mixed> */
    private function sirdataCatalog(): array
    {
        $all = $this->tacCatalog->all();
        $services = [];

        foreach ($all['services'] as $service) {
            $id = (string) ($service['id'] ?? '');
            if (! in_array($id, self::SIRDATA_TAG_IDS, true)) {
                continue;
            }
            $service['renderMode'] = 'sirdata_head';
            $service['addCode'] = $this->sirdataAddCode($id, $service);
            $service['category'] = 'Injection head (legacy)';
            $services[] = $service;
        }

        return [
            'tagMode' => 'sirdata_head',
            'hint' => 'SirData gère le consentement via son CMP. Les tags ci-dessous sont injectés en complément dans le head — privilégie l’interface SirData quand c’est possible.',
            'source' => (string) ($all['source'] ?? ''),
            'syncedAt' => (string) ($all['syncedAt'] ?? ''),
            'cdnBase' => '',
            'categories' => ['Injection head (legacy)'],
            'services' => $services,
        ];
    }

    /** @return array<string, mixed> */
    private function matomoCatalog(): array
    {
        $all = $this->tacCatalog->all();
        $services = [];

        foreach ($all['services'] as $service) {
            $id = (string) ($service['id'] ?? '');
            if (! in_array($id, self::MATOMO_TAG_IDS, true)) {
                continue;
            }
            $service['renderMode'] = 'matomo_direct';
            $service['addCode'] = $this->matomoDirectAddCode($id, $service);
            $service['category'] = 'Matomo (direct)';
            $services[] = $service;
        }

        if ($services === []) {
            $services = $this->fallbackMatomoServices();
        }

        return [
            'tagMode' => 'matomo_direct',
            'hint' => 'Scripts Matomo injectés directement sans tarteaucitron. Réservé aux déploiements exemptés de consentement cookies.',
            'source' => (string) ($all['source'] ?? ''),
            'syncedAt' => (string) ($all['syncedAt'] ?? ''),
            'cdnBase' => '',
            'categories' => ['Matomo (direct)'],
            'services' => $services,
        ];
    }

    /** @param array<string, mixed> $service */
    private function sirdataAddCode(string $id, array $service): string
    {
        if ($id === 'multiplegoogletagmanager' || $id === 'googletagmanager') {
            return '<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);})(window,document,\'script\',\'dataLayer\',\'**GTM-XXXX**\');</script>';
        }
        if ($id === 'facebookpixel') {
            return '<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version=\'2.0\';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,\'script\',\'https://connect.facebook.net/en_US/fbevents.js\');fbq(\'init\',\'**YOUR-ID**\');fbq(\'track\',\'PageView\');</script>';
        }

        return (string) ($service['addCode'] ?? '');
    }

    /** @param array<string, mixed> $service */
    private function matomoDirectAddCode(string $id, array $service): string
    {
        if ($id === 'matomotm') {
            return '<script>var _mtm=window._mtm=window._mtm||[];_mtm.push({\'mtm.startTime\':(new Date().getTime()),\'event\':\'mtm.Start\'});(function(){var d=document,g=d.createElement(\'script\'),s=d.getElementsByTagName(\'script\')[0];g.async=true;g.src=\'**matomotmUrl**\';s.parentNode.insertBefore(g,s);})();</script>';
        }

        return (string) ($service['addCode'] ?? '');
    }

    /** @return list<array<string, mixed>> */
    private function fallbackMatomoServices(): array
    {
        return [
            [
                'id' => 'matomotm',
                'name' => 'Matomo Tag Manager',
                'category' => 'Matomo (direct)',
                'jobs' => ['matomotm'],
                'fields' => [['key' => 'matomotmUrl', 'label' => 'URL script MTM', 'type' => 'url']],
                'addCode' => $this->matomoDirectAddCode('matomotm', []),
                'addCodePlacement' => '',
                'removeCode' => '',
                'renderMode' => 'matomo_direct',
            ],
            [
                'id' => 'matomocloud',
                'name' => 'Matomo Cloud',
                'category' => 'Matomo (direct)',
                'jobs' => ['matomocloud'],
                'fields' => [
                    ['key' => 'matomoId', 'label' => 'ID site', 'type' => 'text'],
                    ['key' => 'matomoHost', 'label' => 'URL Matomo', 'type' => 'url'],
                ],
                'addCode' => '<script>/* Matomo cloud direct — matomoId + matomoHost */</script>',
                'addCodePlacement' => '',
                'removeCode' => '',
                'renderMode' => 'matomo_direct',
            ],
        ];
    }
}
