<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Core\Actions\ActionRegistry;
use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Context\InstallationContextDetector;
use AkyosUpdates\Service\PluginService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class FixRestController
{
    /** @var array<string, CheckInterface> */
    private array $checksById = [];

    /** @param CheckInterface[] $checks */
    public function __construct(
        private ActionRegistry $actions,
        private InstallationContextDetector $detector,
        array $checks
    ) {
        foreach ($checks as $check) {
            $this->checksById[$check->getId()] = $check;
        }
    }

    public function register(): void
    {
        register_rest_route('akyos-updates/v1', '/fix', [
            'methods' => 'POST',
            'callback' => [$this, 'run'],
            'permission_callback' => [$this, 'canManage'],
            'args' => [
                'actionId' => ['required' => true, 'type' => 'string'],
                'checkId' => ['required' => false, 'type' => 'string'],
                'payload' => ['required' => false, 'type' => 'object'],
            ],
        ]);
    }

    public function canManage(): bool
    {
        return current_user_can('manage_options');
    }

    public function run(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $actionId = (string) $request->get_param('actionId');
        if ($actionId === '') {
            return new WP_Error('akyos_updates_missing_action', 'actionId requis.', ['status' => 400]);
        }

        $payload = $request->get_param('payload');
        $payload = is_array($payload) ? $payload : [];
        $checkId = (string) $request->get_param('checkId');

        $response = $this->actions->run($actionId, $payload);

        if ($checkId !== '') {
            $updatedReport = $this->refreshReportFromCheck($checkId);
            if ($updatedReport !== []) {
                $response['updatedReport'] = $updatedReport;
            }
        }

        return new WP_REST_Response($response);
    }

    private function refreshReportFromCheck(string $checkId): array
    {
        $last = get_option('akyos_updates_last_report', []);
        if (!is_array($last)) {
            return [];
        }

        $results = is_array($last['results'] ?? null) ? $last['results'] : [];
        $check = $this->checksById[$checkId] ?? null;
        if (!$check instanceof CheckInterface) {
            return [];
        }

        $context = $this->detector->detect();
        $freshResult = $check->run($context)->toArray();
        $updated = false;

        foreach ($results as &$result) {
            if (($result['id'] ?? '') !== $checkId) {
                continue;
            }
            $result = $freshResult;
            $updated = true;
            break;
        }
        unset($result);

        if (!$updated) {
            $results[] = $freshResult;
        }

        $report = $this->buildReportFromResults($results, $last);
        $stored = [
            'updatedAt' => gmdate('c'),
            'overview' => is_array($last['overview'] ?? null) ? $last['overview'] : $context->toArray(),
            'results' => $results,
            'report' => $report,
        ];

        update_option('akyos_updates_last_report', $stored, false);
        return $report;
    }

    private function buildReportFromResults(array $results, array $previousStore = []): array
    {
        $categories = [
            'WordPress' => ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0],
            'Images' => ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0],
            'Plugins' => ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0],
            'Performance' => ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0],
            'Sécurité' => ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0],
            'SEO' => ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0],
            'Back-office' => ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0],
            'RGPD' => ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0],
        ];

        foreach ($results as $result) {
            if (($result['countsTowardCategoryStats'] ?? true) === false || ($result['id'] ?? '') === 'seo.indexability') {
                continue;
            }
            $category = $result['category'] ?? 'Back-office';
            if (!isset($categories[$category])) {
                $categories[$category] = ['total' => 0, 'ok' => 0, 'warn' => 0, 'fail' => 0];
            }

            $status = $result['status'] ?? 'warn';
            if (!isset($categories[$category][$status])) {
                $status = 'warn';
            }

            $categories[$category]['total']++;
            $categories[$category][$status]++;
        }

        $out = [
            'generatedAt' => gmdate('c'),
            'categories' => $categories,
            'results' => $results,
            'pluginPresence' => PluginService::capture(),
        ];
        $prevReport = is_array($previousStore['report'] ?? null) ? $previousStore['report'] : [];
        if (is_array($prevReport['includedCategories'] ?? null) && $prevReport['includedCategories'] !== []) {
            $out['includedCategories'] = $prevReport['includedCategories'];
        }

        return $out;
    }

}
