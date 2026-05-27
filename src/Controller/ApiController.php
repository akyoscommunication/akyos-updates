<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Core\Maintenance;
use AkyosUpdates\Service\CrmApiAuthService;
use AkyosUpdates\Service\JsonService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class ApiController
{
    private const RATE_LIMIT_SECONDS = 300;

    /** @var list<string> */
    private const CRM_SITE_FIELDS = [
        'installationType',
        'activeTheme',
        'phpVersion',
        'wordpressVersion',
        'wpCliAvailable',
        'composerAvailable',
    ];

    public function __construct(private Maintenance $analyzer)
    {
    }

    public function register(): void
    {
        register_rest_route('akyos-updates/v1', '/crm/site-status', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'runAnalysis'],
                'permission_callback' => [CrmApiAuthService::class, 'permissionCallback'],
                'args' => [
                    'force' => ['required' => false, 'type' => 'boolean'],
                    'detail' => ['required' => false, 'type' => 'string'],
                    'categories' => [
                        'required' => false,
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                    ],
                ],
            ],
            [
                'methods' => 'GET',
                'callback' => [$this, 'getCachedStatus'],
                'permission_callback' => [CrmApiAuthService::class, 'permissionCallback'],
                'args' => [
                    'detail' => ['required' => false, 'type' => 'string'],
                ],
            ],
        ]);
    }

    public function runAnalysis(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $force = (bool) $request->get_param('force');
        $rateCheck = $this->checkRateLimit($force);
        if ($rateCheck instanceof WP_Error) {
            return $rateCheck;
        }

        $categoriesParam = $request->get_param('categories');
        $fullCategories = null;
        if (is_array($categoriesParam) && $categoriesParam !== []) {
            $validated = [];
            foreach ($categoriesParam as $item) {
                if (!is_string($item)) {
                    continue;
                }
                $t = trim($item);
                if ($t === '') {
                    continue;
                }
                if (!in_array($t, Maintenance::knownReportCategories(), true)) {
                    return new WP_Error('akyos_updates_invalid_category', 'Catégorie de rapport inconnue.', ['status' => 400]);
                }
                $validated[$t] = true;
            }
            $list = array_keys($validated);
            if ($list === []) {
                return new WP_Error('akyos_updates_empty_categories', 'Sélectionne au moins une catégorie.', ['status' => 400]);
            }
            $fullCategories = $list;
        }

        return JsonService::wrap(function () use ($request, $fullCategories, $force): array {
            $payload = $this->analyzer->runFullAnalysis($fullCategories);
            if (!$force) {
                $this->markRateLimited();
            }

            return $this->formatCrmResponse($payload, $this->isSummaryOnly($request));
        });
    }

    public function getCachedStatus(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        return JsonService::wrap(function () use ($request): array|WP_Error {
            $stored = $this->analyzer->getPersistedReportPayload();
            if ($stored === []) {
                return new WP_Error(
                    'akyos_updates_no_report',
                    'Aucun rapport disponible. Lance une analyse via POST /crm/site-status.',
                    ['status' => 404]
                );
            }

            $report = is_array($stored['report'] ?? null) ? $stored['report'] : [];
            $categoriesStats = is_array($report['categories'] ?? null) ? $report['categories'] : [];
            $payload = [
                'updatedAt' => is_string($stored['updatedAt'] ?? null) ? $stored['updatedAt'] : gmdate('c'),
                'overview' => is_array($stored['overview'] ?? null) ? $stored['overview'] : $this->analyzer->getSiteOverview(),
                'summary' => Maintenance::computeGlobalSummary($categoriesStats),
                'categories' => $categoriesStats,
                'checks' => is_array($report['results'] ?? null) ? $report['results'] : (is_array($stored['results'] ?? null) ? $stored['results'] : []),
                'pluginPresence' => $report['pluginPresence'] ?? null,
            ];

            return $this->formatCrmResponse($payload, $this->isSummaryOnly($request));
        });
    }

    /**
     * @param array{updatedAt?: string, overview?: array, summary?: array, categories?: array, checks?: array, pluginPresence?: mixed} $payload
     *
     * @return array<string, mixed>
     */
    private function formatCrmResponse(array $payload, bool $summaryOnly): array
    {
        $overview = is_array($payload['overview'] ?? null) ? $payload['overview'] : [];
        $summary = is_array($payload['summary'] ?? null) ? $payload['summary'] : ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0];

        $response = [
            'site' => $this->filterSiteOverview($overview),
            'summary' => $summary,
            'health' => $this->computeHealth($summary),
            'categories' => is_array($payload['categories'] ?? null) ? $payload['categories'] : [],
            'updatedAt' => is_string($payload['updatedAt'] ?? null) ? $payload['updatedAt'] : gmdate('c'),
            'reportUrl' => admin_url('admin.php?page=akyos-updates'),
        ];

        if (!$summaryOnly) {
            $response['checks'] = is_array($payload['checks'] ?? null) ? $payload['checks'] : [];
            $response['pluginPresence'] = $payload['pluginPresence'] ?? null;
        }

        return JsonService::mixed($response);
    }

    /**
     * @param array<string, mixed> $overview
     *
     * @return array<string, mixed>
     */
    private function filterSiteOverview(array $overview): array
    {
        $site = [];
        foreach (self::CRM_SITE_FIELDS as $field) {
            if (array_key_exists($field, $overview)) {
                $site[$field] = $overview[$field];
            }
        }

        return $site;
    }

    /**
     * @param array{total?: int, ok?: int, warn?: int, fail?: int} $summary
     *
     * @return array{status: string, score: int}
     */
    private function computeHealth(array $summary): array
    {
        $total = (int) ($summary['total'] ?? 0);
        $ok = (int) ($summary['ok'] ?? 0);
        $warn = (int) ($summary['warn'] ?? 0);
        $fail = (int) ($summary['fail'] ?? 0);

        $score = $total > 0 ? (int) round(($ok / $total) * 100) : 100;

        if ($fail > 0) {
            $status = 'critical';
        } elseif ($warn > 0) {
            $status = 'warn';
        } else {
            $status = 'ok';
        }

        return ['status' => $status, 'score' => $score];
    }

    private function isSummaryOnly(WP_REST_Request $request): bool
    {
        return trim((string) $request->get_param('detail')) === 'summary';
    }

    private function rateLimitTransientKey(): string
    {
        return 'akyos_updates_crm_rate_' . md5(CrmApiAuthService::getConfiguredKey());
    }

    private function checkRateLimit(bool $force): true|WP_Error
    {
        if ($force) {
            return true;
        }

        if (get_transient($this->rateLimitTransientKey()) !== false) {
            return new WP_Error(
                'akyos_updates_rate_limited',
                'Analyse récente : réessaie dans quelques minutes ou utilise ?force=1.',
                ['status' => 429]
            );
        }

        return true;
    }

    private function markRateLimited(): void
    {
        set_transient($this->rateLimitTransientKey(), 1, self::RATE_LIMIT_SECONDS);
    }
}
