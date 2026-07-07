<?php

namespace AkyosUpdates\Plugin;

use AkyosUpdates\Service\GithubReleaseService;

final class GithubUpdater
{
    public function register(): void
    {
        add_action('admin_notices', [$this, 'renderAdminNotice']);
    }

    public function renderAdminNotice(): void
    {
        if (! current_user_can('update_plugins')) {
            return;
        }

        $status = GithubReleaseService::getUpdateStatus();
        if (! $status['updateAvailable'] || ! is_string($status['latest']) || $status['latest'] === '') {
            return;
        }

        $releaseUrl = esc_url(
            $status['detailsUrl'] ?? 'https://github.com/' . GithubReleaseService::resolveRepo() . '/releases/latest'
        );

        if ($status['bedrock']) {
            printf(
                '<div class="notice notice-warning is-dismissible"><p><strong>Akyos Updates</strong> : '
                . 'version <strong>%1$s</strong> disponible (installée : %2$s). '
                . 'Mettre à jour via <code>composer update akyos/akyos-updates</code>. '
                . '<a href="%3$s" target="_blank" rel="noopener noreferrer">Voir la release</a></p></div>',
                esc_html($status['latest']),
                esc_html($status['current']),
                $releaseUrl
            );

            return;
        }

        printf(
            '<div class="notice notice-warning is-dismissible"><p><strong>Akyos Updates</strong> : '
            . 'version <strong>%1$s</strong> disponible (installée : %2$s). '
            . '<a href="%3$s" target="_blank" rel="noopener noreferrer">Voir la release GitHub</a></p></div>',
            esc_html($status['latest']),
            esc_html($status['current']),
            $releaseUrl
        );
    }
}
