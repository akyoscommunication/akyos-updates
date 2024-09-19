<?php

namespace AkyosUpdates\Service;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class AdminService
{
    public function enqueueStyles(): void
    {
        wp_enqueue_style(AKYOS_UPDATES_NAME, plugin_dir_path(__DIR__) . 'assets/css/akyos-updates.css', [], AKYOS_UPDATES_VERSION, 'all');
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_script(AKYOS_UPDATES_NAME, plugin_dir_path(__DIR__) . 'assets/js/akyos-updates.js', [], AKYOS_UPDATES_VERSION, false);
    }

    public function addPluginAdminMenu(): void
    {
        add_menu_page('Akyos Updates', 'Akyos Updates', 'manage_options', AKYOS_UPDATES_NAME, [$this, 'displayPluginSetupPage'], 'dashicons-shield-alt', 99);

//        add_submenu_page(AKYOS_UPDATES_NAME, 'Tracking Woocommerce', 'Tracking Woocommerce', 'manage_options', AKYOS_UPDATES_NAME."-woo_tracking", array($this, 'display_plugin_woo_tracking_page'));
    }

    public function addActionLinks($links): array
    {
        /*
        *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
        */
        $settings_link = array(
            '<a href="' . admin_url('options-general.php?page=' . AKYOS_UPDATES_NAME) . '">' . __('Settings', AKYOS_UPDATES_NAME) . '</a>',
        );

        return array_merge($settings_link, $links);
    }

    /**
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\LoaderError
     */
    public function displayPluginSetupPage(): void
    {
        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        $twig = new Environment($loader);
        echo $twig->render('akyos-updates.html.twig', []);
    }

    public function optionsUpdate(): void
    {
        register_setting(AKYOS_UPDATES_NAME, AKYOS_UPDATES_NAME, array($this, 'validate'));
    }

    public function validate(array $input): array
    {
        $validOptions = array();

//        //Cleanup
//        $valid['rgpd_custom_rgpd_page'] = $input['rgpd_custom_rgpd_page'] ?? false;

        return $validOptions;
    }
}