<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Service\FixRunnerService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class FixRestController
{
    public function __construct(private FixRunnerService $fixRunner)
    {
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

        return new WP_REST_Response($this->fixRunner->run($actionId, $payload, $checkId));
    }
}
