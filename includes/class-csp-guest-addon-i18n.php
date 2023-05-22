<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://mithilesh.wisdmlabs.net/
 * @since      1.0.0
 *
 * @package    Csp_Guest_Addon
 * @subpackage Csp_Guest_Addon/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Csp_Guest_Addon
 * @subpackage Csp_Guest_Addon/includes
 * @author     Mithilesh <mithilesh.chaudhaudhari@wisdmlabs.com>
 */
class Csp_Guest_Addon_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'csp-guest-addon',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
