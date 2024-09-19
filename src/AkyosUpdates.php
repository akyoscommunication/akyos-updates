<?php

namespace AkyosUpdates;

use AkyosUpdates\Class\Hook;
use AkyosUpdates\Service\AdminService;
use AkyosUpdates\Service\I18nService;
use AkyosUpdates\Service\LoaderService;

readonly class AkyosUpdates
{
    public function __construct(
        private AdminService  $adminService,
        private I18nService   $i18nService,
        private LoaderService $loaderService,
    )
    {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        $this->loaderService->add(new Hook(LoaderService::TYPE_ACTION, 'init', $this,'init'));
    }

    public function activate()
    {
        // Add any activation code here
    }

    public function deactivate()
    {
        // Add any deactivation code here
    }

    public function init(): void
    {
        // If this file is called directly, abort.
        if ( ! defined( 'WPINC' ) ) {
            die;
        }

        define( 'AKYOS_UPDATES_VERSION', '0.0.1' );
        define( 'AKYOS_UPDATES_NAME', 'akyos-updates' );

        $this->setLocaleHook();
        $this->defineAdminHooks();
    }

    private function setLocaleHook(): void
    {
        $this->loaderService->add(new Hook(LoaderService::TYPE_ACTION, 'plugins_loaded', $this->i18nService, 'loadPluginTextdomain'));
    }

    private function defineAdminHooks(): void
    {
        $plugin_basename = plugin_basename(plugin_dir_path(__DIR__) . AKYOS_UPDATES_NAME . '.php');
        
        $hooks = [
            new Hook(LoaderService::TYPE_ACTION, 'admin_enqueue_scripts', $this->adminService, 'enqueueStyles'),
            new Hook(LoaderService::TYPE_ACTION, 'admin_enqueue_scripts', $this->adminService, 'enqueueScripts'),
            new Hook(LoaderService::TYPE_ACTION, 'admin_menu', $this->adminService, 'addPluginAdminMenu'),
            new Hook(LoaderService::TYPE_FILTER, 'plugin_action_links_' . $plugin_basename, $this->adminService, 'addActionLinks'),
            new Hook(LoaderService::TYPE_ACTION, 'admin_init', $this->adminService, 'optionsUpdate'),
        ];
        
        $this->loaderService->bulkAdd($hooks);
    }
}