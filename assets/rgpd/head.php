<?php
/**
 * Sortie <head> du module RGPD : init tarteaucitron OU scripts SirData.
 *
 * @var array<string, mixed> $settings
 * @var string $privacyUrl
 * @var string $forceLanguage
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
$hideBanner = ! empty($settings['hide_banner']);

if ($hideBanner) :
    ?>
    <style>#tarteaucitronRoot { display: none !important; }</style>
    <?php
endif;

if ($serviceType === RgpdSettingsService::SERVICE_SIRDATA) :
    $sirdataUser = rawurlencode((string) ($settings['sirdata_user'] ?? ''));
    $sirdataSite = rawurlencode((string) ($settings['sirdata_site'] ?? ''));
    $renderer = new CmpTagRenderer(new TarteaucitronTagService(new TarteaucitronCatalogService()), new CmpCatalogService(new TarteaucitronCatalogService()));
    $tags = $renderer->resolveTags($settings);
    ?>
    <script type="text/javascript" src="//cache.consentframework.com/js/pa/<?php echo esc_attr($sirdataUser); ?>/c/<?php echo esc_attr($sirdataSite); ?>/stub" referrerpolicy="unsafe-url" charset="utf-8"></script>
    <script type="text/javascript" src="//choices.consentframework.com/js/pa/<?php echo esc_attr($sirdataUser); ?>/c/<?php echo esc_attr($sirdataSite); ?>/cmp" referrerpolicy="unsafe-url" charset="utf-8" async></script>
    <?php foreach ($renderer->renderHeadScripts($tags) as $script) : ?>
        <script type="text/javascript"><?php echo $script; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
    <?php endforeach; ?>
    <?php
endif;
