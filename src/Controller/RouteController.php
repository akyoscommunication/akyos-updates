<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Core\Maintenance;
use AkyosUpdates\Service\JsonService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class RouteController
{
    public function __construct(private Maintenance $analyzer)
    {
    }

    public function register(): void
    {
        register_rest_route('akyos-updates/v1', '/overview', [
            'methods' => 'GET',
            'callback' => [$this, 'overview'],
            'permission_callback' => [$this, 'canManage'],
        ]);

        register_rest_route('akyos-updates/v1', '/analyze/start', [
            'methods' => 'POST',
            'callback' => [$this, 'start'],
            'permission_callback' => [$this, 'canManage'],
            'args' => [
                'mode' => ['required' => false, 'type' => 'string'],
                'category' => ['required' => false, 'type' => 'string'],
                'categories' => [
                    'required' => false,
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
        ]);

        register_rest_route('akyos-updates/v1', '/analyze/step', [
            'methods' => 'POST',
            'callback' => [$this, 'step'],
            'permission_callback' => [$this, 'canManage'],
            'args' => [
                'sessionId' => ['required' => true, 'type' => 'string'],
            ],
        ]);

        register_rest_route('akyos-updates/v1', '/report', [
            'methods' => 'GET',
            'callback' => [$this, 'report'],
            'permission_callback' => [$this, 'canManage'],
            'args' => [
                'sessionId' => ['required' => false, 'type' => 'string'],
            ],
        ]);
    }

    public function canManage(): bool
    {
        return current_user_can('manage_options');
    }

    public function overview(): WP_REST_Response|WP_Error
    {
        return JsonService::wrap(fn(): array => $this->analyzer->getSiteOverview());
    }

    public function start(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $mode = trim((string) $request->get_param('mode'));
        $category = trim((string) $request->get_param('category'));
        $categoriesParam = $request->get_param('categories');

        if ($mode === Maintenance::ANALYSIS_MODE_CATEGORY) {
            if ($category === '') {
                return new WP_Error('akyos_updates_missing_category', 'Paramètre category requis pour ce mode.', ['status' => 400]);
            }

            if (!in_array($category, Maintenance::knownReportCategories(), true)) {
                return new WP_Error('akyos_updates_invalid_category', 'Catégorie de rapport inconnue.', ['status' => 400]);
            }
        }

        $effectiveMode = $mode === '' ? Maintenance::ANALYSIS_MODE_FULL : $mode;
        $fullCategories = null;

        if ($effectiveMode === Maintenance::ANALYSIS_MODE_FULL && is_array($categoriesParam)) {
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

        return JsonService::wrap(fn(): array => $this->analyzer->startAnalysisByMode(
            $mode,
            $category !== '' ? $category : null,
            $fullCategories
        ));
    }

    public function step(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $sessionId = (string) $request->get_param('sessionId');
        if ($sessionId === '') {
            return new WP_Error('akyos_updates_missing_session', 'sessionId requis.', ['status' => 400]);
        }

        return JsonService::wrap(fn(): array => $this->analyzer->runNextStep($sessionId));
    }

    public function report(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $sessionId = (string) $request->get_param('sessionId');

        return JsonService::wrap(fn(): array => $this->analyzer->getReport($sessionId));
    }
}
