<?php

namespace AkyosUpdates\Service;

final class WordpressService
{
    private const TRANSIENT_PREFIX = 'akyos_updates_wporg_plugin_lookup_';
    private const TTL_FOUND = 24 * HOUR_IN_SECONDS;
    private const TTL_NOT_FOUND = 6 * HOUR_IN_SECONDS;

    public static function isListedOnWordPressOrg(string $slug): bool
    {
        if ($slug === '') {
            return false;
        }

        $cacheKey = self::TRANSIENT_PREFIX . md5($slug);
        $cached = get_transient($cacheKey);
        if ($cached === '1') {
            return true;
        }
        if ($cached === '0') {
            return false;
        }

        $url = 'https://api.wordpress.org/plugins/info/1.0/' . rawurlencode($slug) . '.json';
        $response = wp_remote_get($url, [
            'timeout' => 6,
            'redirection' => 2,
            'sslverify' => true,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            set_transient($cacheKey, '0', self::TTL_NOT_FOUND);
            return false;
        }

        if ((int) wp_remote_retrieve_response_code($response) !== 200) {
            set_transient($cacheKey, '0', self::TTL_NOT_FOUND);
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        if ($body === '' || $body === 'null') {
            set_transient($cacheKey, '0', self::TTL_NOT_FOUND);
            return false;
        }

        $data = json_decode($body, true);
        $listed = is_array($data) && ! empty($data['name']);
        set_transient($cacheKey, $listed ? '1' : '0', $listed ? self::TTL_FOUND : self::TTL_NOT_FOUND);

        return $listed;
    }
}
