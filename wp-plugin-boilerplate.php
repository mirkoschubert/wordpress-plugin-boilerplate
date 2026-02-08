<?php
/**
 * Plugin Name: WP Plugin Boilerplate
 * Plugin URI:  https://mirkoschubert.de
 * Description: Feature plugin boilerplate for client projects.
 * Version:     1.0.0
 * Author:      Mirko Schubert
 * Author URI:  https://mirkoschubert.de
 * Text Domain: wp-plugin-boilerplate
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * License:     GPL-3.0
 */

if (!defined('ABSPATH')) {
  exit;
}

define('WP_PLUGIN_BOILERPLATE_VERSION', '1.0.0');
define('WP_PLUGIN_BOILERPLATE_FILE', __FILE__);
define('WP_PLUGIN_BOILERPLATE_DIR', plugin_dir_path(__FILE__));
define('WP_PLUGIN_BOILERPLATE_URL', plugin_dir_url(__FILE__));

require_once __DIR__ . '/vendor/autoload.php';

use WPPluginBoilerplate\Core\Plugin;

$plugin = new Plugin();
$plugin->init();

register_activation_hook(__FILE__, [$plugin, 'activate']);
register_deactivation_hook(__FILE__, [$plugin, 'deactivate']);
