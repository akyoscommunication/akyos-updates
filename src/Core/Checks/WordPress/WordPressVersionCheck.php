<?php

namespace AkyosUpdates\Core\Checks\WordPress;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;

final class WordPressVersionCheck implements CheckInterface
{
    private const CORE_CHECK_REFRESH_LOCK_TRANSIENT = 'akyos_updates_wp_core_check_refresh_lock';
    private const CORE_OFFERS_API_CACHE_PREFIX = 'akyos_updates_wp_core_offers_api_';
    private const CORE_OFFERS_API_CACHE_TTL_OK = 12 * HOUR_IN_SECONDS;
    private const CORE_OFFERS_API_CACHE_TTL_KO = 30 * MINUTE_IN_SECONDS;

    public function getId(): string
    {
        return 'wordpress.version';
    }

    public function getCategory(): string
    {
        return 'WordPress';
    }

    public function getTitle(): string
    {
        return 'Version WordPress';
    }
    public function getSuccessMessage(): string
    {
        return 'Version WordPress mise à jour.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $offers = $this->readCoreOffers();
        $offers = $this->mergeWithApiOffers($offers);
        $currentVersion = trim((string) $context->getWordpressVersion());
        $latestVersion = $currentVersion;

        foreach ($offers as $offer) {
            $offerVersion = trim((string) ($offer->current ?? ''));
            if ($offerVersion === '') {
                continue;
            }
            if (version_compare($offerVersion, $latestVersion, '>')) {
                $latestVersion = $offerVersion;
            }
        }

        $isLatest = version_compare($currentVersion, $latestVersion, '>=');
        $isBedrock = $context->getInstallationType() === 'bedrock';
        $availableVersions = $this->extractLatestVersions($offers, $currentVersion);
        $message = $isLatest
            ? sprintf('WordPress est à jour (%s).', $currentVersion)
            : sprintf('Version actuelle %s, dernière version %s.', $currentVersion, $latestVersion);

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            $isLatest ? 'ok' : 'warn',
            $isLatest ? 'success' : 'warning',
            $message,
            false,
            null,
            [
                'currentVersion' => $currentVersion,
                'latestVersion' => $latestVersion,
                'isLatest' => $isLatest,
                'isBedrock' => $isBedrock,
                'availableVersions' => $availableVersions,
            ]
        );
    }

    private function extractLatestVersions(array $offers, string $currentVersion): array
    {
        $versions = [];
        foreach ($offers as $offer) {
            $version = trim((string) ($offer->current ?? ''));
            if ($version === '') {
                continue;
            }
            $response = trim((string) ($offer->response ?? ''));
            if (!in_array($response, ['upgrade', 'latest', 'autoupdate'], true)) {
                continue;
            }
            $versions[$version] = true;
        }
        if ($currentVersion !== '') {
            $versions[$currentVersion] = true;
        }

        $versionList = array_keys($versions);
        usort($versionList, static fn(string $left, string $right): int => version_compare($right, $left));

        return array_slice($versionList, 0, 3);
    }

    private function mergeWithApiOffers(array $offers): array
    {
        $byVersion = [];
        foreach ($offers as $offer) {
            if (!is_object($offer)) {
                continue;
            }
            $offer = $this->normalizeOffer($offer);
            $version = trim((string) ($offer->current ?? ''));
            if ($version === '') {
                continue;
            }
            $byVersion[$version] = $offer;
        }

        $decoded = $this->readCoreOffersApiPayload();
        if (!is_array($decoded)) {
            return array_values($byVersion);
        }
        $apiOffers = is_array($decoded['offers'] ?? null) ? $decoded['offers'] : [];
        foreach ($apiOffers as $apiOffer) {
            if (!is_array($apiOffer)) {
                continue;
            }
            $version = trim((string) ($apiOffer['current'] ?? ''));
            if ($version === '') {
                continue;
            }
            if (!isset($byVersion[$version])) {
                $byVersion[$version] = $this->normalizeOffer((object) $apiOffer);
            }
        }

        return array_values($byVersion);
    }

    private function readCoreOffers(): array
    {
        $updateCore = get_site_transient('update_core');
        $lastChecked = is_object($updateCore) ? (int) ($updateCore->last_checked ?? 0) : 0;
        $offers = is_object($updateCore) && is_array($updateCore->updates ?? null) ? $updateCore->updates : [];
        $stale = $lastChecked === 0 || (time() - $lastChecked) > 12 * HOUR_IN_SECONDS;
        if (($stale || $offers === []) && !get_transient(self::CORE_CHECK_REFRESH_LOCK_TRANSIENT)) {
            set_transient(self::CORE_CHECK_REFRESH_LOCK_TRANSIENT, '1', 5 * MINUTE_IN_SECONDS);
            wp_version_check([], true);
            $updateCore = get_site_transient('update_core');
            $offers = is_object($updateCore) && is_array($updateCore->updates ?? null) ? $updateCore->updates : [];
        }

        return $offers;
    }

    private function readCoreOffersApiPayload(): ?array
    {
        $cacheKey = self::CORE_OFFERS_API_CACHE_PREFIX . md5((string) get_locale());
        $cached = get_transient($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $apiUrl = sprintf('https://api.wordpress.org/core/version-check/1.7/?locale=%s', rawurlencode(get_locale()));
        $response = wp_remote_get($apiUrl, ['timeout' => 2.5, 'redirection' => 2]);
        if (is_wp_error($response)) {
            set_transient($cacheKey, [], self::CORE_OFFERS_API_CACHE_TTL_KO);
            return null;
        }

        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);
        if (!is_array($decoded)) {
            set_transient($cacheKey, [], self::CORE_OFFERS_API_CACHE_TTL_KO);
            return null;
        }

        set_transient($cacheKey, $decoded, self::CORE_OFFERS_API_CACHE_TTL_OK);
        return $decoded;
    }

    private function normalizeOffer(object $offer): object
    {
        $packagesRaw = $offer->packages ?? null;
        if (is_array($packagesRaw)) {
            $packagesRaw = (object) $packagesRaw;
        }
        if (!is_object($packagesRaw)) {
            $packagesRaw = (object) [];
        }

        $download = trim((string) ($offer->download ?? ''));
        $full = trim((string) ($packagesRaw->full ?? ''));
        if ($full === '' && $download !== '') {
            $packagesRaw->full = $download;
        }
        foreach (['partial', 'rollback', 'new_bundled', 'no_content', 'full'] as $key) {
            if (!property_exists($packagesRaw, $key)) {
                $packagesRaw->{$key} = false;
            }
        }
        $offer->packages = $packagesRaw;

        return $offer;
    }
}
