<?php

namespace AkyosUpdates\Service;

use WP_Error;
use WP_REST_Request;

final class CrmApiAuthService
{
    public const HEADER_API_KEY = 'X-Akyos-Api-Key';

    public static function isConfigured(): bool
    {
        return self::getConfiguredKey() !== '';
    }

    public static function getConfiguredKey(): string
    {
        if (!defined('AKYOS_UPDATES_MAW_KEY')) {
            return '';
        }

        $key = constant('AKYOS_UPDATES_MAW_KEY');

        return is_string($key) ? trim($key) : '';
    }

    /** @return true|WP_Error */
    public static function validate(?WP_REST_Request $request = null): bool|WP_Error
    {
        $expected = self::getConfiguredKey();
        if ($expected === '') {
            return new WP_Error(
                'akyos_updates_unauthorized',
                'Clé API CRM non configurée (AKYOS_UPDATES_MAW_KEY dans wp-config.php).',
                ['status' => 401]
            );
        }

        $provided = self::extractKeyFromRequest($request);
        if ($provided === '') {
            return new WP_Error(
                'akyos_updates_unauthorized',
                'Clé API manquante (header X-Akyos-Api-Key ou Authorization: Bearer).',
                ['status' => 401]
            );
        }

        if (!hash_equals($expected, $provided)) {
            return new WP_Error(
                'akyos_updates_unauthorized',
                'Clé API invalide.',
                ['status' => 401]
            );
        }

        return true;
    }

    public static function permissionCallback(WP_REST_Request $request): bool|WP_Error
    {
        $result = self::validate($request);

        return $result === true ? true : $result;
    }

    private static function extractKeyFromRequest(?WP_REST_Request $request): string
    {
        if ($request instanceof WP_REST_Request) {
            $header = $request->get_header(self::HEADER_API_KEY);
            if (is_string($header) && trim($header) !== '') {
                return trim($header);
            }

            $auth = $request->get_header('authorization');
            if (is_string($auth) && preg_match('/^\s*Bearer\s+(.+)$/i', $auth, $m) === 1) {
                return trim($m[1]);
            }
        }

        if (isset($_SERVER['HTTP_X_AKYOS_API_KEY']) && is_string($_SERVER['HTTP_X_AKYOS_API_KEY'])) {
            return trim($_SERVER['HTTP_X_AKYOS_API_KEY']);
        }

        if (isset($_SERVER['HTTP_AUTHORIZATION']) && is_string($_SERVER['HTTP_AUTHORIZATION'])) {
            if (preg_match('/^\s*Bearer\s+(.+)$/i', $_SERVER['HTTP_AUTHORIZATION'], $m) === 1) {
                return trim($m[1]);
            }
        }

        return '';
    }
}
