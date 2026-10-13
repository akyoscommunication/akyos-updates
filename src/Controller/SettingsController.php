<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Service\DisplaySettingsService;
use AkyosUpdates\Service\JsonService;
use WP_REST_Request;
use WP_REST_Response;

final class SettingsController
{
    public function __construct(private DisplaySettingsService $display)
    {
    }

    public function register(): void
    {
        register_rest_route('akyos-updates/v1', '/settings', [
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
                    'show_maintenance_admin_bar' => [
                        'required' => true,
                        'type' => 'boolean',
                    ],
                ],
            ],
        ]);
    }

    public function canManage(): bool
    {
        return current_user_can('manage_options');
    }

    public function getSettings(): WP_REST_Response
    {
        return JsonService::wrap(fn (): array => $this->display->publicView());
    }

    public function saveSettings(WP_REST_Request $request): WP_REST_Response
    {
        return JsonService::wrap(function () use ($request): array {
            $this->display->save((bool) $request->get_param('show_maintenance_admin_bar'));

            return $this->display->publicView();
        });
    }
}
