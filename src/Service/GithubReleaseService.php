<?php

namespace AkyosUpdates\Service;

final class GithubReleaseService
{
    private const CACHE_KEY = 'akyos_updates_github_release';
    private const CACHE_TTL_OK = 12 * HOUR_IN_SECONDS;
    private const CACHE_TTL_KO = 30 * MINUTE_IN_SECONDS;
    private const DEFAULT_REPO = 'akyoscommunication/akyos-updates';

    /**
     * @return array{
     *     updateAvailable: bool,
     *     current: string,
     *     latest: string|null,
     *     detailsUrl: string|null,
     *     checkFailed: bool,
     *     bedrock: bool
     * }
     */
    public static function getUpdateStatus(): array
    {
        $current = defined('AKYOS_UPDATES_VERSION') ? (string) constant('AKYOS_UPDATES_VERSION') : '';
        $release = self::getLatestRelease();
        $bedrock = self::isBedrockInstall();

        if ($release === null) {
            return [
                'updateAvailable' => false,
                'current' => $current,
                'latest' => null,
                'detailsUrl' => null,
                'checkFailed' => true,
                'bedrock' => $bedrock,
            ];
        }

        $latest = $release['version'];

        return [
            'updateAvailable' => $current !== '' && version_compare($current, $latest, '<'),
            'current' => $current,
            'latest' => $latest,
            'detailsUrl' => $release['detailsUrl'],
            'checkFailed' => false,
            'bedrock' => $bedrock,
        ];
    }

    public static function resolveToken(): string
    {
        if (defined('AKYOS_UPDATES_GITHUB_TOKEN')) {
            $token = trim((string) constant('AKYOS_UPDATES_GITHUB_TOKEN'));
            if ($token !== '') {
                return $token;
            }
        }

        return trim((string) apply_filters('akyos_updates_github_token', ''));
    }

    public static function resolveRepo(): string
    {
        $repo = trim((string) apply_filters('akyos_updates_github_repo', self::DEFAULT_REPO));
        if ($repo === '' || ! preg_match('#^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$#', $repo)) {
            return self::DEFAULT_REPO;
        }

        return $repo;
    }

    /** @return array{version: string, detailsUrl: string}|null */
    private static function getLatestRelease(): ?array
    {
        $cached = get_transient(self::CACHE_KEY);
        if (is_array($cached)) {
            return $cached !== [] ? $cached : null;
        }

        $repo = self::resolveRepo();
        $url = 'https://api.github.com/repos/' . rawurlencode($repo) . '/releases/latest';
        $response = self::githubRequest($url);
        if (is_wp_error($response)) {
            set_transient(self::CACHE_KEY, [], self::CACHE_TTL_KO);

            return null;
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        if ($status !== 200) {
            set_transient(self::CACHE_KEY, [], self::CACHE_TTL_KO);

            return null;
        }

        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);
        if (! is_array($decoded)) {
            set_transient(self::CACHE_KEY, [], self::CACHE_TTL_KO);

            return null;
        }

        $version = self::parseTagVersion((string) ($decoded['tag_name'] ?? ''));
        if ($version === null) {
            set_transient(self::CACHE_KEY, [], self::CACHE_TTL_KO);

            return null;
        }

        $release = [
            'version' => $version,
            'detailsUrl' => trim((string) ($decoded['html_url'] ?? '')),
        ];
        if ($release['detailsUrl'] === '') {
            $release['detailsUrl'] = 'https://github.com/' . $repo . '/releases/latest';
        }

        set_transient(self::CACHE_KEY, $release, self::CACHE_TTL_OK);

        return $release;
    }

    private static function parseTagVersion(string $tag): ?string
    {
        $tag = trim($tag);
        if ($tag === '') {
            return null;
        }
        if ($tag[0] === 'v' || $tag[0] === 'V') {
            $tag = substr($tag, 1);
        }
        if ($tag === '' || ! preg_match('/^\d+\.\d+\.\d+(?:[-.+][0-9A-Za-z.-]+)?$/', $tag)) {
            return null;
        }

        return $tag;
    }

    /** @return array<string, mixed>|\WP_Error */
    private static function githubRequest(string $url)
    {
        $headers = [
            'Accept' => 'application/vnd.github+json',
            'User-Agent' => 'Akyos-Updates-WordPress-Plugin',
        ];
        $token = self::resolveToken();
        if ($token !== '') {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        return wp_remote_get($url, [
            'timeout' => 8,
            'redirection' => 2,
            'headers' => $headers,
        ]);
    }

    private static function isBedrockInstall(): bool
    {
        $abspath = untrailingslashit(ABSPATH);
        $bedrockWpRoot = str_ends_with($abspath, '/web/wp') || str_ends_with($abspath, '\\web\\wp');
        $bedrockConfigPresent = file_exists(dirname($abspath, 2) . '/config/application.php');

        return $bedrockWpRoot || $bedrockConfigPresent;
    }
}
