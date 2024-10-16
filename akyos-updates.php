<?php

/**
 * The plugin Akyos Updates
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://akyos.com
 * @since             0.0.1
 * @package           AkyosUpdates
 *
 * @wordpress-plugin
 * Plugin Name:       Mise à jour Akyos
 * Plugin URI:        https://github.com/akyos-communication/akyos-updates
 * Description:       Plugin Akyos Communication pour les mises à jour WP
 * Version:           0.0.1
 * Author:            Akyos Communication <developpement@akyos.com>
 * Author URI:        https://akyos.com
 * Text Domain:       akyos-updates
 * Domain Path:       /languages
 */

use AkyosUpdates\AkyosUpdates;
use AkyosUpdates\Attribute\AdminRoute;
use AkyosUpdates\Kernel;

require_once __DIR__.'/vendor/autoload.php';

const AKYOS_UPDATES_VERSION = '0.0.1';
const AKYOS_UPDATES_NAME = 'akyos-updates';


$kernel = new Kernel('dev', true);
$container = $kernel->boot();
$akyosUpdates = $container->get(AkyosUpdates::class);

