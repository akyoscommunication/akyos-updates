<?php

namespace AkyosUpdates\Plugin;

use AkyosUpdates\Core\Maintenance;
use AkyosUpdates\Core\Actions\ActionRegistry;
use AkyosUpdates\Core\Actions\BrandaEnableDashboardWidgetsAction;
use AkyosUpdates\Core\Actions\BrandaHideDashboardWidgetsAction;
use AkyosUpdates\Core\Actions\BrandaSaveSmtpAction;
use AkyosUpdates\Core\Actions\BrandaSendTestEmailAction;
use AkyosUpdates\Core\Actions\CreateAdminLiteUserAction;
use AkyosUpdates\Core\Actions\DisableCommentsAction;
use AkyosUpdates\Core\Actions\DisablePostByEmailAction;
use AkyosUpdates\Core\Actions\ChangeWordPressVersionAction;
use AkyosUpdates\Core\Actions\DefenderSaveMaskLoginAction;
use AkyosUpdates\Core\Actions\DefenderSaveRecaptchaKeysAction;
use AkyosUpdates\Core\Actions\DefenderApplyFirewallTranslationsAction;
use AkyosUpdates\Core\Actions\DefenderEnableGlobalIpBlockerAction;
use AkyosUpdates\Core\Actions\DefenderEnablePwnedPasswordsAction;
use AkyosUpdates\Core\Actions\DefenderEnableSecurityHeadersAction;
use AkyosUpdates\Core\Actions\GenerateComposerGuidanceAction;
use AkyosUpdates\Core\Actions\GenerateGitignorePluginExceptionsAction;
use AkyosUpdates\Core\Actions\GenerateComposerJsonAction;
use AkyosUpdates\Core\Actions\HummingbirdApplyAdvancedConfigAction;
use AkyosUpdates\Core\Actions\HummingbirdApplyPageCacheConfigAction;
use AkyosUpdates\Core\Actions\HummingbirdEnableGzipAction;
use AkyosUpdates\Core\Actions\CreateRgpdLegalPageAction;
use AkyosUpdates\Core\Actions\SeoToggleIndexingAction;
use AkyosUpdates\Core\Actions\SmushApplyImageSizingAction;
use AkyosUpdates\Core\Actions\SmushApplyNextGenFormatAction;
use AkyosUpdates\Core\Actions\SmushApplyRecommendedConfigAction;
use AkyosUpdates\Core\Actions\SmushApplyResizeLargeAction;
use AkyosUpdates\Core\Actions\SetFaviconAction;
use AkyosUpdates\Core\Checks\Plugins\AuthJsonRootCheck;
use AkyosUpdates\Core\Checks\Plugins\ComposerRootCheck;
use AkyosUpdates\Core\Checks\Plugins\ComposerPluginsCheck;
use AkyosUpdates\Core\Checks\Plugins\PluginsInventoryCheck;
use AkyosUpdates\Core\Checks\Images\SmushBulkPendingCheck;
use AkyosUpdates\Core\Checks\Images\SmushConfigurationCheck;
use AkyosUpdates\Core\Checks\Images\SmushImageSizingCheck;
use AkyosUpdates\Core\Checks\Images\SmushNextGenFormatCheck;
use AkyosUpdates\Core\Checks\Images\SmushResizeLargeImagesCheck;
use AkyosUpdates\Core\Checks\Images\SmushVersionUpgradeCheck;
use AkyosUpdates\Core\Checks\Performance\HummingbirdAdvancedCheck;
use AkyosUpdates\Core\Checks\Performance\HummingbirdGzipCheck;
use AkyosUpdates\Core\Checks\Performance\HummingbirdPageCacheCheck;
use AkyosUpdates\Core\Checks\Security\DefenderFirewallTranslationsCheck;
use AkyosUpdates\Core\Checks\Security\DefenderGlobalIpBlockerCheck;
use AkyosUpdates\Core\Checks\Security\DefenderMaskLoginCheck;
use AkyosUpdates\Core\Checks\Security\DefenderPluginCheck;
use AkyosUpdates\Core\Checks\Security\DefenderPwnedPasswordsCheck;
use AkyosUpdates\Core\Checks\Security\DefenderRecaptchaCheck;
use AkyosUpdates\Core\Checks\Security\DefenderSecurityHeadersCheck;
use AkyosUpdates\Core\Checks\Rgpd\RgpdAnalyticsTrackingCheck;
use AkyosUpdates\Core\Checks\Rgpd\RgpdLegalPagesPresenceCheck;
use AkyosUpdates\Core\Checks\Rgpd\RgpdPluginCheck;
use AkyosUpdates\Core\Checks\Seo\SeoIndexabilityCheck;
use AkyosUpdates\Core\Checks\Seo\SeoLegalPagesNoindexCheck;
use AkyosUpdates\Core\Checks\Seo\SeoPluginCheck;
use AkyosUpdates\Core\Checks\Seo\SeoSitemapCheck;
use AkyosUpdates\Core\Checks\WordPress\CommentsDisabledCheck;
use AkyosUpdates\Core\Checks\WordPress\FaviconCheck;
use AkyosUpdates\Core\Checks\WordPress\PostByEmailDisabledCheck;
use AkyosUpdates\Core\Checks\BackOffice\AdminLiteUserCheck;
use AkyosUpdates\Core\Checks\BackOffice\BrandaDashboardWidgetsCheck;
use AkyosUpdates\Core\Checks\BackOffice\BrandaSmtpCheck;
use AkyosUpdates\Core\Checks\WordPress\ActiveThemeUpdateCheck;
use AkyosUpdates\Core\Checks\WordPress\WordPressVersionCheck;
use AkyosUpdates\Core\Context\InstallationContextDetector;
use AkyosUpdates\Service\BrandaService;
use AkyosUpdates\Service\DefenderService;
use AkyosUpdates\Service\HummingbirdService;
use AkyosUpdates\Service\SmushService;
use AkyosUpdates\Controller\PluginController;
use AkyosUpdates\Controller\RouteController;
use AkyosUpdates\Controller\FixRestController;

final class Plugin
{
    public function boot(): void
    {
        $detector = new InstallationContextDetector();

        $checks = [
            new WordPressVersionCheck(),
            new ActiveThemeUpdateCheck(),
            new FaviconCheck(),
            new CommentsDisabledCheck(),
            new PostByEmailDisabledCheck(),
            new ComposerRootCheck(),
            new AuthJsonRootCheck(),
            new ComposerPluginsCheck(),
            new PluginsInventoryCheck(),
            new SeoPluginCheck(),
            new SeoSitemapCheck(),
            new SeoLegalPagesNoindexCheck(),
            new SeoIndexabilityCheck(),
            new DefenderPluginCheck(),
            new AdminLiteUserCheck(),
            new RgpdPluginCheck(),
            new RgpdLegalPagesPresenceCheck(),
            new RgpdAnalyticsTrackingCheck(),
        ];

        if (BrandaService::isPluginActive()) {
            $checks[] = new BrandaSmtpCheck();
            $checks[] = new BrandaDashboardWidgetsCheck();
        }

        if (DefenderService::isPluginActive()) {
            $checks[] = new DefenderMaskLoginCheck();
            $checks[] = new DefenderSecurityHeadersCheck();
            $checks[] = new DefenderPwnedPasswordsCheck();
            $checks[] = new DefenderRecaptchaCheck();
            $checks[] = new DefenderGlobalIpBlockerCheck();
            $checks[] = new DefenderFirewallTranslationsCheck();
        }

        $checks[] = new SmushVersionUpgradeCheck();

        if (SmushService::isSmushV4AnalysisSupported()) {
            $checks[] = new SmushBulkPendingCheck();
            $checks[] = new SmushConfigurationCheck();
            $checks[] = new SmushNextGenFormatCheck();
            $checks[] = new SmushImageSizingCheck();
            $checks[] = new SmushResizeLargeImagesCheck();
        }

        if (HummingbirdService::isPluginActive()) {
            $checks[] = new HummingbirdPageCacheCheck();
            $checks[] = new HummingbirdGzipCheck();
            $checks[] = new HummingbirdAdvancedCheck();
        }

        $analyzer = new Maintenance($detector, $checks);

        $actionsList = [
            new ChangeWordPressVersionAction(),
            new SetFaviconAction(),
            new DisableCommentsAction(),
            new DisablePostByEmailAction(),
            new GenerateComposerJsonAction(),
            new GenerateComposerGuidanceAction(),
            new GenerateGitignorePluginExceptionsAction(),
            new SeoToggleIndexingAction(),
            new CreateRgpdLegalPageAction(),
            new DefenderSaveMaskLoginAction(),
            new DefenderSaveRecaptchaKeysAction(),
            new DefenderEnableSecurityHeadersAction(),
            new DefenderEnablePwnedPasswordsAction(),
            new DefenderEnableGlobalIpBlockerAction(),
            new DefenderApplyFirewallTranslationsAction(),
            new CreateAdminLiteUserAction(),
            new BrandaSaveSmtpAction(),
            new BrandaSendTestEmailAction(),
            new BrandaEnableDashboardWidgetsAction(),
            new BrandaHideDashboardWidgetsAction(),
            new HummingbirdApplyPageCacheConfigAction(),
            new HummingbirdEnableGzipAction(),
            new HummingbirdApplyAdvancedConfigAction(),
        ];

        if (SmushService::isSmushV4AnalysisSupported()) {
            $actionsList[] = new SmushApplyRecommendedConfigAction();
            $actionsList[] = new SmushApplyNextGenFormatAction();
            $actionsList[] = new SmushApplyImageSizingAction();
            $actionsList[] = new SmushApplyResizeLargeAction();
        }
        $actions = new ActionRegistry($actionsList);

        $adminPage = new AdminPage($analyzer);
        $routeController = new RouteController($analyzer);
        $fixController = new FixRestController($actions, $detector, $checks);
        $pluginController = new PluginController();

        add_action('admin_menu', [$adminPage, 'register']);
        add_action('admin_enqueue_scripts', [$adminPage, 'enqueue']);
        add_action('rest_api_init', [$routeController, 'register']);
        add_action('rest_api_init', [$fixController, 'register']);
        add_action('rest_api_init', [$pluginController, 'register']);
    }

}
