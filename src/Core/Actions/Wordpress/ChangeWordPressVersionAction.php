<?php

namespace AkyosUpdates\Core\Actions;

use Core_Upgrader;

final class ChangeWordPressVersionAction implements ActionInterface
{
    public function getId(): string
    {
        return 'wordpress.change_version';
    }

    public function run(array $payload = []): ActionResult
    {
        $targetVersion = trim((string) ($payload['targetVersion'] ?? ''));
        if ($targetVersion === '') {
            return ActionResult::failure('Version cible manquante.');
        }

        if (!function_exists('wp_version_check')) {
            require_once ABSPATH . 'wp-admin/includes/update.php';
        }
        if (!function_exists('get_core_checksums')) {
            require_once ABSPATH . 'wp-admin/includes/update.php';
        }
        if (!function_exists('show_message')) {
            require_once ABSPATH . 'wp-admin/includes/misc.php';
        }
        if (!class_exists(\WP_Upgrader::class)) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }
        if (!class_exists(Core_Upgrader::class)) {
            require_once ABSPATH . 'wp-admin/includes/class-core-upgrader.php';
        }
        \WP_Upgrader::release_lock('core_updater');
        \WP_Upgrader::release_lock('auto_updater');
        wp_version_check();
        $updateCore = get_site_transient('update_core');
        $offers = is_array($updateCore->updates ?? null) ? $updateCore->updates : [];
        $offers = $this->mergeWithApiOffers($offers);

        $selectedOffer = null;
        $latestVersion = trim((string) get_bloginfo('version'));
        foreach ($offers as $offer) {
            $offerVersion = trim((string) ($offer->current ?? ''));
            if ($offerVersion === '') {
                continue;
            }
            if (version_compare($offerVersion, $latestVersion, '>')) {
                $latestVersion = $offerVersion;
            }
            if ($offerVersion === $targetVersion) {
                $selectedOffer = $offer;
            }
        }

        if ($selectedOffer === null) {
            return ActionResult::failure(sprintf('Version %s indisponible.', $targetVersion));
        }

        $cliResult = $this->runWpCliUpdate($targetVersion);
        if (($cliResult['ran'] ?? false) === true && ($cliResult['success'] ?? false) === true) {
            wp_version_check();
            $newCurrentVersion = trim((string) get_bloginfo('version'));

            return ActionResult::success(sprintf('WordPress est maintenant en version %s.', $newCurrentVersion), [
                'currentVersion' => $newCurrentVersion,
                'latestVersion' => $latestVersion,
                'isLatest' => version_compare($newCurrentVersion, $latestVersion, '>='),
                'availableVersions' => $this->extractLatestVersions($offers, $newCurrentVersion),
                'isBedrock' => false,
            ]);
        }

        $startedBuffering = false;
        if (!ob_get_level()) {
            ob_start();
            $startedBuffering = true;
        }

        try {
            $upgrader = new Core_Upgrader();
            $result = $upgrader->upgrade($selectedOffer);
        } catch (\Throwable $throwable) {
            return ActionResult::failure($throwable->getMessage());
        } finally {
            if ($startedBuffering) {
                ob_end_clean();
            }
        }

        if (is_wp_error($result)) {
            return ActionResult::failure($result->get_error_message());
        }

        wp_version_check();
        $newCurrentVersion = trim((string) get_bloginfo('version'));

        return ActionResult::success(sprintf('WordPress est maintenant en version %s.', $newCurrentVersion), [
            'currentVersion' => $newCurrentVersion,
            'latestVersion' => $latestVersion,
            'isLatest' => version_compare($newCurrentVersion, $latestVersion, '>='),
            'availableVersions' => $this->extractLatestVersions($offers, $newCurrentVersion),
            'isBedrock' => false,
        ]);
    }

    private function runWpCliUpdate(string $targetVersion): array
    {
        if (!function_exists('exec')) {
            return ['ran' => false, 'success' => false];
        }

        $disabledFunctions = (string) ini_get('disable_functions');
        if ($disabledFunctions !== '') {
            $disabledList = array_map('trim', explode(',', $disabledFunctions));
            if (in_array('exec', $disabledList, true)) {
                return ['ran' => false, 'success' => false];
            }
        }

        $candidates = ['wp', 'wp-cli', '/usr/local/bin/wp', 'php wp-cli.phar'];
        $path = rtrim((string) ABSPATH, '/');
        $versionArg = escapeshellarg($targetVersion);
        $pathArg = escapeshellarg($path);

        foreach ($candidates as $wpBinary) {
            $binaryArg = escapeshellarg($wpBinary);
            $command = $binaryArg . ' --path=' . $pathArg . ' core update --version=' . $versionArg . ' --quiet --skip-plugins --skip-themes 2>&1';
            $output = [];
            $exitCode = 1;
            exec($command, $output, $exitCode);
            if ($exitCode === 0) {
                return ['ran' => true, 'success' => true];
            }

            $joinedOutput = trim(implode("\n", $output));
            if ($joinedOutput !== '' && stripos($joinedOutput, 'not found') === false) {
                return ['ran' => true, 'success' => false];
            }
        }

        return ['ran' => false, 'success' => false];
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

        $apiUrl = sprintf('https://api.wordpress.org/core/version-check/1.7/?locale=%s', rawurlencode(get_locale()));
        $decoded = null;
        $response = wp_remote_get($apiUrl, ['timeout' => 10]);
        if (!is_wp_error($response)) {
            $decoded = json_decode((string) wp_remote_retrieve_body($response), true);
        }
        if (!is_array($decoded)) {
            $raw = @file_get_contents($apiUrl);
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
            }
        }
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
