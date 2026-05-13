<?php
/**
 * Plugin Name: Akyos Updates
 * Plugin URI: https://akyos.com
 * Description: Maintenance analyzer et actions correctives WordPress pour le parc Akyos.
 * Version: 1.0.0
 * Author: Akyos Communication
 * Author URI: https://akyos.com
 * Text Domain: akyos-updates
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AKYOS_UPDATES_VERSION', '1.0.0');
define('AKYOS_UPDATES_PLUGIN_FILE', __FILE__);
define('AKYOS_UPDATES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AKYOS_UPDATES_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once AKYOS_UPDATES_PLUGIN_DIR . 'src/Autoloader.php';

\AkyosUpdates\Autoloader::register();

add_action('plugins_loaded', static function (): void {
    $plugin = new \AkyosUpdates\Plugin\Plugin();
    $plugin->boot();
});
