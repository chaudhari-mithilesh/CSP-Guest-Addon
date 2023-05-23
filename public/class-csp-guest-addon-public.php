<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://mithilesh.wisdmlabs.net/
 * @since      1.0.0
 *
 * @package    Csp_Guest_Addon
 * @subpackage Csp_Guest_Addon/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Csp_Guest_Addon
 * @subpackage Csp_Guest_Addon/public
 * @author     Mithilesh <mithilesh.chaudhaudhari@wisdmlabs.com>
 */
class Csp_Guest_Addon_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		include_once plugin_dir_path(__FILE__) . '/../admin/class-csp-guest-addon-admin.php';
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Csp_Guest_Addon_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Csp_Guest_Addon_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/csp-guest-addon-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Csp_Guest_Addon_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Csp_Guest_Addon_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/csp-guest-addon-public.js', array('jquery'), $this->version, false);

		wp_localize_script($this->plugin_name, "csp_guest_ajax", array(
			"name"			=>	"CSP Guest Addon",
			"author"		=>	"Mithilesh Chaudhari",
			"ajaxurl"		=>	admin_url("admin-ajax.php"),
		));
	}

	public function csp_update_quantity()
	{
		$product_id = (int) $_POST['product_id'];
		$var_id = (int) $_POST['variationId'];
		$new_quantity = (int) $_POST['new_quantity'];

		$plugin_data = get_plugin_data(__FILE__);
		$plugin_version = $plugin_data['Version'];
		$admin = new Csp_Guest_Addon_Admin(plugin_basename(__FILE__), $plugin_version);
		if ($var_id == 0) {
			$price = $admin->calculate_price_qty($product_id, $new_quantity);
		} else {
			$price = $admin->calculate_price_qty($var_id, $new_quantity);
			$table = $admin->get_var_product_qty_dynamic_html($var_id);;
			$total = $price * $new_quantity;
			$total = (float) number_format($total, 2, '.', '');
			echo json_encode(array(
				'price'	=>	$total,
				'table'	=>	$table,
			));
			wp_die();
		}
		$total = $price * $new_quantity;
		$total = (float) number_format($total, 2, '.', '');
		echo json_encode(array(
			'price'	=>	$total,
		));
		wp_die();
	}
}
