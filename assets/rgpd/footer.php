<?php
/**
 * Sortie <footer> du module RGPD : bouton cookies + tags (tarteaucitron / Matomo direct).
 *
 * @var array<string, mixed> $settings
 */

use AkyosUpdates\Service\CmpCatalogService;
use AkyosUpdates\Service\CmpTagRenderer;
use AkyosUpdates\Service\RgpdSettingsService;
use AkyosUpdates\Service\TarteaucitronCatalogService;
use AkyosUpdates\Service\TarteaucitronTagService;

if (! defined('ABSPATH')) {
    exit;
}

$serviceType = (string) ($settings['service_type'] ?? '');
$hideCookieButton = ! empty($settings['hide_cookie_button']);
$buttonContent = (string) ($settings['cookie_button_content'] ?? '');

$onclick = '';
if ($serviceType === RgpdSettingsService::SERVICE_TARTEAUCITRON) {
    $onclick = 'tarteaucitron.userInterface.openPanel();';
} elseif ($serviceType === RgpdSettingsService::SERVICE_SIRDATA) {
    $onclick = 'window.Sddan.cmp.displayUI();';
}

$cookieLogo = AKYOS_UPDATES_PLUGIN_URL . 'assets/rgpd/cookie-icon.png';
$buttonPosition = (string) ($settings['cookie_button_position'] ?? 'bottom-left');
$positionClass = $buttonPosition === 'bottom-right' ? ' aky-position-right' : '';
$alwaysVisible = ! empty($settings['cookie_button_always_visible']);
$alwaysVisibleClass = $alwaysVisible ? ' aky-always-visible' : '';

if (! $hideCookieButton) :
    $hasCustom = $buttonContent !== '';
    ?>
    <div id="akyCookiesGestion"
         class="<?php echo esc_attr(trim($hasCustom ? 'aky-cookies-text' : 'aky-cookies-logo') . $positionClass . $alwaysVisibleClass); ?>"
         onclick="<?php echo esc_attr($onclick); ?>">
        <?php
        if ($hasCustom) {
            echo wp_kses_post($buttonContent);
        } else {
            echo '<img src="' . esc_url($cookieLogo) . '" alt="cookie">';
        }
        ?>
    </div>
    <?php
endif;

if ($serviceType === RgpdSettingsService::SERVICE_MATOMO_NO_COOKIE) :
    $renderer = new CmpTagRenderer(new TarteaucitronTagService(new TarteaucitronCatalogService()), new CmpCatalogService(new TarteaucitronCatalogService()));
    $tags = $renderer->resolveTags($settings);
    foreach ($renderer->renderFooterScripts($serviceType, $tags) as $script) :
        ?>
        <script type="text/javascript">
            <?php echo $script; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </script>
        <?php
    endforeach;
endif;
