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
        $url = $this->resolveRegisterUrl();

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

    /**
     * URL d'enregistrement MAW, dérivée de l'URL de base configurable.
     */
    private function resolveRegisterUrl(): string
    {
        $url = rtrim($this->resolveMawBaseUrl(), '/') . '/api/akyos-updates/link';

        // Compat : le filtre historique peut surcharger l'URL complète.
        return (string) apply_filters('akyos_updates_maw_register_url', $url);
    }

    /**
     * URL de base de l'outil MAW. Priorité :
     *  1. constante AKYOS_UPDATES_MAW_URL (wp-config.php) ;
     *  2. filtre akyos_updates_maw_url ;
     *  3. valeur par défaut (dev local).
     */
    private function resolveMawBaseUrl(): string
    {
        if (defined('AKYOS_UPDATES_MAW_URL')) {
            $constant = trim((string) constant('AKYOS_UPDATES_MAW_URL'));
            if ($constant !== '') {
                return $constant;
            }
        }

        return (string) apply_filters('akyos_updates_maw_url', 'https://maw.akyos.com');
    }
}
