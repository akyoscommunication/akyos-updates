<?php

namespace AkyosUpdates\Service;

use AkyosUpdates\Plugin\AdminPage;

final class SiteIdentityService
{
    /** @return array<string, mixed> */
    public static function collect(): array
    {
        $defender = DefenderService::getMaskLoginState();
        $loginMasked = ($defender['active'] ?? false) === true && trim((string) ($defender['loginUrl'] ?? '')) !== '';
        $loginUrl = $loginMasked
            ? (string) $defender['loginUrl']
            : wp_login_url();

        return [
            'homeUrl' => home_url('/'),
            'siteUrl' => site_url('/'),
            'siteName' => (string) get_bloginfo('name'),
            'adminUrl' => admin_url(),
            'loginUrl' => $loginUrl,
            'loginMasked' => $loginMasked,
            'reportUrl' => admin_url('admin.php?page=' . AdminPage::PAGE_MAINTENANCE),
            'panels' => [
                'maintenance' => admin_url('admin.php?page=' . AdminPage::PAGE_MAINTENANCE),
                'rgpd' => admin_url('admin.php?page=' . AdminPage::PAGE_RGPD),
            ],
        ];
    }
}
