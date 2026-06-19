<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Service\JsonService;
use AkyosUpdates\Service\LinkSettingsService;
use AkyosUpdates\Service\MawRegistrationService;
use WP_REST_Request;
use WP_REST_Response;

final class LinkController
{
    public function __construct(
        private LinkSettingsService $link,
        private MawRegistrationService $registration
    ) {
    }

    public function register(): void
    {
        register_rest_route('akyos-updates/v1', '/link', [
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
                    'pairing_key' => ['required' => false, 'type' => 'string'],
                    'register' => ['required' => false, 'type' => 'boolean'],
                    'regenerate_api_key' => ['required' => false, 'type' => 'boolean'],
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
        return JsonService::wrap(function (): array {
            $this->link->ensureApiKey();

            return [
                'link' => $this->link->publicView(),
                'identity' => \AkyosUpdates\Service\SiteIdentityService::collect(),
            ];
        });
    }

    public function saveSettings(WP_REST_Request $request): WP_REST_Response
    {
        return JsonService::wrap(function () use ($request): array {
            if ((bool) $request->get_param('regenerate_api_key')) {
                $this->link->regenerateApiKey();
            } else {
                $this->link->save([
                    'pairing_key' => (string) $request->get_param('pairing_key'),
                ]);
            }

            $registration = null;
            if ((bool) $request->get_param('register')) {
                $registration = $this->registration->register();
            }

            return [
                'link' => $this->link->publicView(),
                'identity' => \AkyosUpdates\Service\SiteIdentityService::collect(),
                'registration' => $registration,
            ];
        });
    }
}
