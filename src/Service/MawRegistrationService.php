<?php

namespace AkyosUpdates\Service;

final class MawRegistrationService
{
    public function __construct(private LinkSettingsService $link)
    {
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function register(): array
    {
        $settings = $this->link->get();
        $pairingKey = trim((string) ($settings['pairing_key'] ?? ''));
        if ($pairingKey === '') {
            return [
                'success' => false,
                'message' => 'Saisis la clé de liaison fournie par MAW.',
            ];
        }

        $apiKey = $this->link->ensureApiKey();
        $url = (string) apply_filters(
            'akyos_updates_maw_register_url',
            'https://mon-agence-web.io/api/akyos-updates/link'
        );

        $body = array_merge(
            SiteIdentityService::collect(),
            [
                'pairingKey' => $pairingKey,
                'apiKey' => $apiKey,
                'pluginVersion' => defined('AKYOS_UPDATES_VERSION') ? (string) constant('AKYOS_UPDATES_VERSION') : '',
            ]
        );

        $response = wp_remote_post($url, [
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Akyos-Api-Key' => $apiKey,
            ],
            'body' => wp_json_encode($body),
        ]);

        if (is_wp_error($response)) {
            $message = $response->get_error_message();
            $this->link->markLinkError($message);

            return [
                'success' => false,
                'message' => $message,
            ];
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);
        $remoteMessage = is_array($decoded) && is_string($decoded['message'] ?? null)
            ? (string) $decoded['message']
            : '';

        if ($status < 200 || $status >= 300) {
            $message = $remoteMessage !== ''
                ? $remoteMessage
                : sprintf('Échec de liaison MAW (HTTP %d).', $status);
            $this->link->markLinkError($message);

            return [
                'success' => false,
                'message' => $message,
            ];
        }

        $this->link->markLinked();

        return [
            'success' => true,
            'message' => $remoteMessage !== '' ? $remoteMessage : 'Site lié à MAW.',
        ];
    }
}
