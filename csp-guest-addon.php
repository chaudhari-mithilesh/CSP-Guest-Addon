<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://mithilesh.wisdmlabs.net/
 * @since             1.0.0
 * @package           Csp_Guest_Addon
 *
 * @wordpress-plugin
 * Plugin Name:       CSP Guest Addon
 * Plugin URI:        https://mithilesh.wisdmlabs.net/csp-guest-addon
 * Description:       This Plugin is an addon for Customer Specific Prices for woocommerce.
 * Version:           1.0.0
 * Author:            Mithilesh
 * Author URI:        https://mithilesh.wisdmlabs.net/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       csp-guest-addon
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('CSP_GUEST_ADDON_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-csp-guest-addon-activator.php
 */
function activate_csp_guest_addon()
{
	if (is_plugin_active('woocommerce/woocommerce.php') && is_plugin_active('customer-specific-pricing-for-woocommerce/customer-specific-pricing-for-woocommerce.php')) {
		// Require the necessary files and activate the plugin
		require_once plugin_dir_path(__FILE__) . 'includes/class-csp-guest-addon-activator.php';
		Csp_Guest_Addon_Activator::activate();
	} else {
		// Deactivate the plugin if WooCommerce and/or the CSP plugin is not active
		deactivate_plugins(plugin_basename(__FILE__));
		wp_die('Sorry, but this plugin requires WooCommerce and the CSP Guest Add-on to be installed and active.');
	}
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-csp-guest-addon-deactivator.php
 */
function deactivate_csp_guest_addon()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-csp-guest-addon-deactivator.php';
	Csp_Guest_Addon_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_csp_guest_addon');
register_deactivation_hook(__FILE__, 'deactivate_csp_guest_addon');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-csp-guest-addon.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_csp_guest_addon()
{

	$plugin = new Csp_Guest_Addon();
	$plugin->run();
}
run_csp_guest_addon();
