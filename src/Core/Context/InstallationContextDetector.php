<?php

namespace AkyosUpdates\Core\Context;

final class InstallationContextDetector
{
    public function detect(): SiteContext
    {
        $abspath = untrailingslashit(ABSPATH);
        $bedrockWpRoot = str_ends_with($abspath, '/web/wp') || str_ends_with($abspath, '\\web\\wp');
        $bedrockConfigPresent = file_exists(dirname($abspath, 2) . '/config/application.php');

        if ($bedrockWpRoot || $bedrockConfigPresent) {
            $installationType = 'bedrock';
            $projectRootPath = dirname($abspath, 2);
            $wordpressRootPath = $abspath;
        } else {
            $installationType = 'vanilla';
            $projectRootPath = $abspath;
            $wordpressRootPath = $abspath;
        }

        $theme = wp_get_theme();

        return new SiteContext(
            $installationType,
            $projectRootPath,
            $wordpressRootPath,
            WP_PLUGIN_DIR,
            get_bloginfo('version'),
            PHP_VERSION,
            $theme->get('Name')
        );
    }
}
