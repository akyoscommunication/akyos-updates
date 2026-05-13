<?php

namespace AkyosUpdates\Core\Checks\WordPress;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use WP_Theme;

final class ActiveThemeUpdateCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'wordpress.active_theme';
    }

    public function getCategory(): string
    {
        return 'WordPress';
    }

    public function getTitle(): string
    {
        return 'Thème actif';
    }
    public function getSuccessMessage(): string
    {
        return 'Point validé après action corrective.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $theme = wp_get_theme();
        $stylesheet = $theme->get_stylesheet();
        $themeName = (string) $theme->get('Name');
        $currentVersion = trim((string) $theme->get('Version'));

        $isSage = $this->isSageTheme($theme);

        if (! function_exists('wp_update_themes')) {
            require_once ABSPATH . 'wp-admin/includes/update.php';
        }

        wp_update_themes();

        /** @var object|false|null $themesUpdate */
        $themesUpdate = get_site_transient('update_themes');
        $response = [];
        if (is_object($themesUpdate) && isset($themesUpdate->response) && is_array($themesUpdate->response)) {
            $response = $themesUpdate->response[$stylesheet] ?? [];
        }

        $updateAvailable = [] !== $response && isset($response['new_version']);
        $latestAvailableVersion = $updateAvailable ? trim((string) $response['new_version']) : null;

        $sageNote = '';
        if ($isSage) {
            $sageNote = $currentVersion !== ''
                ? sprintf(' Sage détecté — version du thème : %s.', $currentVersion)
                : ' Sage détecté.';
        }

        if ($updateAvailable) {
            $message = sprintf(
                'Le thème actif « %s » peut être mis à jour (version disponible : %s).',
                $themeName !== '' ? $themeName : $stylesheet,
                $latestAvailableVersion ?? ''
            ) . $sageNote;
        } elseif ($themeName !== '') {
            $message = sprintf(
                'Thème actif : %s. Aucune mise à jour signalée depuis WordPress.org (ou thème hors dépôt).',
                $themeName
            ) . $sageNote;
        } else {
            $message = sprintf(
                'Thème actif (slug : %s). Aucune mise à jour signalée depuis WordPress.org (ou thème hors dépôt).',
                $stylesheet
            ) . $sageNote;
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            $updateAvailable ? 'warn' : 'ok',
            $updateAvailable ? 'warning' : 'success',
            $message,
            false,
            null,
            [
                'themeName' => $themeName ?: $stylesheet,
                'stylesheet' => $stylesheet,
                'template' => $theme->get_template(),
                'isChildTheme' => $theme->parent() instanceof WP_Theme,
                'currentVersion' => $currentVersion,
                'updateAvailable' => $updateAvailable,
                'latestAvailableVersion' => $latestAvailableVersion,
                'isSageTheme' => $isSage,
                'updateNoticeUrl' => isset($response['url']) ? (string) $response['url'] : null,
            ]
        );
    }

    private function isSageTheme(WP_Theme $theme): bool
    {
        if ($this->themeHeadersIndicateSage($theme)) {
            return true;
        }

        $parent = $theme->parent();
        if ($parent instanceof WP_Theme) {
            return $this->themeHeadersIndicateSage($parent);
        }

        return false;
    }

    private function themeHeadersIndicateSage(WP_Theme $theme): bool
    {
        $textDomain = strtolower(trim((string) $theme->get('TextDomain')));
        if ($textDomain === 'sage') {
            return true;
        }

        $uri = strtolower((string) $theme->get('ThemeURI'));

        return str_contains($uri, 'roots.io/sage');
    }
}
