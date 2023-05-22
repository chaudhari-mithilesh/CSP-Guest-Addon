<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://mithilesh.wisdmlabs.net/
 * @since      1.0.0
 *
 * @package    Csp_Guest_Addon
 * @subpackage Csp_Guest_Addon/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Csp_Guest_Addon
 * @subpackage Csp_Guest_Addon/includes
 * @author     Mithilesh <mithilesh.chaudhaudhari@wisdmlabs.com>
 */
class Csp_Guest_Addon
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Csp_Guest_Addon_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('CSP_GUEST_ADDON_VERSION')) {
			$this->version = CSP_GUEST_ADDON_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'csp-guest-addon';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Csp_Guest_Addon_Loader. Orchestrates the hooks of the plugin.
	 * - Csp_Guest_Addon_i18n. Defines internationalization functionality.
	 * - Csp_Guest_Addon_Admin. Defines all hooks for the admin area.
	 * - Csp_Guest_Addon_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-csp-guest-addon-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-csp-guest-addon-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-csp-guest-addon-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-csp-guest-addon-public.php';

		$this->loader = new Csp_Guest_Addon_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Csp_Guest_Addon_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Csp_Guest_Addon_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new Csp_Guest_Addon_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

		$this->loader->add_action('admin_menu', $plugin_admin, 'csp_guest_addon_menu_page');
		$this->loader->add_action('init', $plugin_admin, 'filters_cb');
		$this->loader->add_action('woocommerce_before_calculate_totals', $plugin_admin, 'calculate_dynamic_price');
		// $this->loader->add_action('woocommerce_before_calculate_totals', $plugin_admin, 'calculate_dynamic_price_var_products');
		// $this->loader->add_action('woocommerce_before_variations_form', $plugin_admin, 'qty_var_price_frontend', 10);
		// $this->loader->add_action('woocommerce_before_variations_form', $plugin_admin, 'get_var_product_qty_html', 10);
		// $this->loader->add_action('woocommerce_before_variations_form', $plugin_admin, 'var_product_data', 10);
		// $this->loader->add_action('wp_ajax_get_var_product_qty_html', $plugin_admin, 'get_var_product_qty_html');
		// $this->loader->add_action('wp_ajax_nopriv_get_var_product_qty_html', $plugin_admin, 'get_var_product_qty_html');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Csp_Guest_Addon_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		$this->loader->add_action('wp_ajax_csp_update_quantity', $plugin_public, 'csp_update_quantity');
		$this->loader->add_action('wp_ajax_nopriv_csp_update_quantity', $plugin_public, 'csp_update_quantity');
		// $this->loader->add_action('wp_ajax_csp_update_var_quantity', $plugin_public, 'csp_update_var_quantity');
		// $this->loader->add_action('wp_ajax_nopriv_csp_update_var_quantity', $plugin_public, 'csp_update_var_quantity');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Csp_Guest_Addon_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
