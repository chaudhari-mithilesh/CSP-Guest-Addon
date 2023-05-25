<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://mithilesh.wisdmlabs.net/
 * @since      1.0.0
 *
 * @package    Csp_Guest_Addon
 * @subpackage Csp_Guest_Addon/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Csp_Guest_Addon
 * @subpackage Csp_Guest_Addon/admin
 * @author     Mithilesh <mithilesh.chaudhaudhari@wisdmlabs.com>
 */
class Csp_Guest_Addon_Admin
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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/csp-guest-addon-admin.css', array(), $this->version, 'all');
		// if (wp_style_is('wdm_csp_product_frontend_css', 'registered')) {
		// 	$css_file = wp_styles()->registered['wdm_csp_product_frontend_css']->src;
		// 	wp_enqueue_style('wdm_csp_product_frontend_css', $css_file);
		// }
	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/csp-guest-addon-admin.js', array('jquery'), $this->version, false);

		// wp_localize_script($this->plugin_name, "csp_guest_ajax", array(
		// 	"name"			=>	"CSP Guest Addon",
		// 	"author"		=>	"Mithilesh Chaudhari",
		// 	"ajaxurl"		=>	admin_url("admin-ajax.php"),
		// ));
	}
	/**
	 * Render the CSP Guest Addon menu page.
	 *
	 * @return void
	 */
	public function csp_guest_addon_menu_page()
	{
		add_menu_page(
			'CSP Guest Addon',
			'CSP Guest Addon',
			'manage_options',
			'guest-addon-menu',
			array($this, 'get_category_rules'),
			'dashicons-buddicons-buddypress-logo',
			50
		);

		add_submenu_page(
			'guest-addon-menu',
			'Get Price Data',
			'Calculate Prices',
			'manage_options',
			'csp-guest-prices',
			array($this, 'get_category_price_data'),
		);
	}

	/**
	 * Fetch active guest rules from the database.
	 *
	 * @return array The fetched guest rules.
	 */

	public function fetch_active_guest_rules()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . "wusp_subrules";
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT product_id, price, flat_or_discount_price, min_qty FROM {$table_name} WHERE rule_type = 'Role' and associated_entity = 'guest' and active = 1")
		);
		$output = highlight_string(print_r($results, true), true);
		// echo $output;
		// echo "<br><br>";
		return $results;
	}

	/**
	 * Get price data for active guest rules.
	 *
	 * @return array The price data array.
	 */

	public function get_price_data()
	{
		$data = $this->fetch_active_guest_rules();
		$price_data = array();
		foreach ($data as $args) {
			$price_data[] = $this->calc_price_data($args->product_id, $args->price, $args->flat_or_discount_price, $args->min_qty);
		}
		$output = highlight_string(print_r($price_data, true), true);
		// echo $output;
		return $price_data;
	}

	/**
	 * Calculate the price data for a specific product.
	 *
	 * @param int    $product_id             The ID of the product.
	 * @param float  $price                  The base price of the product.
	 * @param int    $flat_or_discount_price The type of price calculation to apply.
	 * @param int    $min_qty                The minimum quantity for price calculation.
	 * @return array The calculated price data.
	 */

	public function calc_price_data($product_id, $price, $flat_or_discount_price, $min_qty)
	{
		$product = wc_get_product($product_id);
		$regular_price = $product->get_regular_price();

		if ($flat_or_discount_price == 1);
		elseif ($flat_or_discount_price == 2) {
			$price = $regular_price - ($regular_price * $price * 0.01);
		}

		// $price = round($price, 2);
		// echo $price . "\n";
		return array(
			'product_id' => $product_id,
			'price' => $price,
			'min_qty' => $min_qty
		);
	}

	/**
	 * Set the guest prices for a product on filter hook.
	 *
	 * @param string  $price_html  The original price HTML.
	 * @param WC_Product  $product  The product object.
	 * @return string The updated price HTML.
	 */

	public function set_guest_prices($price_html, $product)
	{
		if (!is_user_logged_in()) {


			$price_data = $this->get_price_data();
			$product_id = $product->get_id();
			$key = array_search($product_id, array_column($price_data, 'product_id'));
			if ($key !== false) {
				$price = $price_data[$key]['price'];
				$min_qty = $price_data[$key]['min_qty'];
				// if (is_product() && $product->is_type('simple')) {
				// 	$product_html = $this->get_product_qty_html($product_id, $price, $min_qty);
				// 	// echo $product_html;
				$price_html = '<span class="price">' . wc_price($price)  . '</span>';
				// return $price_html;
				// }
				// $price_html = '<span class="price">' . wc_price($price)  . '</span>';
			}

			return $price_html;
		}
	}

	/**
	 * To display Product Total on the product page.
	 *
	 * @param string     $price_html The original price HTML.
	 * @param WC_Product $product    The product object.
	 * @return string The updated price HTML.
	 */

	public function qty_price_frontend($description)
	{
		global $product;

		if ($product && $product->is_type('simple')) {
			// Get the product ID
			$product_id = $product->get_id();
			$price = $this->calculate_price_qty($product_id, 1);
			$price = number_format($price, 2);
			// $price = number_format($price, 2);
			$price_html = "<br>";
			$price_html .= '<div class = "dynamic-price-text"><span>Product Total: <span>';
			$price_html .= '<span>' . get_woocommerce_currency_symbol() . '<span>';
			$price_html .= '<span class = "dynamic-price">' . $price .  '</span>';
			$price_html .= '</div>';
			return $price_html .= '<p>' . $description . '</p>';
		}

		return $description;
	}

	/**
	 * Get the HTML for product quantity and price table.
	 *
	 * @param int    $product_id The ID of the product.
	 * @param float  $price      The base price of the product.
	 * @param int    $min_qty    The minimum quantity for price calculation.
	 * @return string The HTML table containing quantity and price data.
	 */

	public function get_product_qty_html($description)
	{
		// $price_data = $this->fetch_active_guest_rules();
		global $product;

		if ($product && $product->is_type('simple')) {
			// return "Hello";
			// $price_data = $this->fetch_active_guest_rules();
			// Get the product ID
			$product_id = $product->get_id();
			// echo $product_id;
			$regular_price = $product->get_regular_price();
			// if ($product->is_on_sale()) {
			// 	// Product has a sale price
			// 	$regular_price = $product->get_sale_price();
			// } else {
			// 	$regular_price = $product->get_regular_price();
			// }
			// echo $regular_price;

			$price_data = $this->get_price_data();
			// $cat_price_data = $this->get_category_price_data();
			$product_data = array();
			foreach ($price_data as $rec) {
				if ($rec['product_id'] == $product_id) {
					$product_data[] = $rec;
				}
			}

			// if (empty($product_data)) {
			// 	foreach ($cat_price_data as $rec) {
			// 		foreach ($rec as $r) {
			// 			if ($r['product_id'] == $product_id) {
			// 				$product_data[] = $r;
			// 			}
			// 		}
			// 	}
			// }

			// if ($rec['product_id'] == $product_id) {
			// if (count($product_data) === 1 && $product_data[0]['min_qty'] === 1) {
			// 	return $description;
			// }

			$output = highlight_string(print_r($product_data, true), true);
			// echo $output;

			usort($product_data, function ($a, $b) {
				return $a['min_qty'] <=> $b['min_qty'];
			});

			if (count($product_data) == 1 && end($product_data)['min_qty'] == 1) {
				return $description;
			}
			$table = '<div class = "qty-fieldset"><h1 class="qty-legend"><span>' . __('Quantity Discount', 'customer-specific-pricing-for-woocommerce') . '</span></h1><div class="qty_table_container"><table class = "qty_table">';
			$moreText = __('and more :', 'customer-specific-pricing-for-woocommerce');
			$table .= '<tr><td class = "qty-num">1' . ' ' . $moreText . '</td><td class = "qty-price">' . wc_price($regular_price) . '</td></tr>';

			foreach ($product_data as $data) {
				if (!($data['min_qty'] == 1)) {
					$table .= '<tr><td class = "qty-num">' . $data['min_qty'] . ' ' . $moreText . '</td><td class = "qty-price">' . wc_price($this->calculate_price_qty($data['product_id'], $data['min_qty'])) . '</td></tr>';
				}
			}


			$table .= '</table></div></div>';
			return $table .= "<p>" . $description . "</p>";
			// }
			return $description;
		} else {
			return $description;
		}
	}

	public function custom_product_description($description)
	{
		global $product;

		// Get the product ID
		$product_id = $product->get_id();

		// Get the regular price
		$regular_price = $product->get_regular_price();

		// Modify the product description based on the product ID and regular price
		$modified_description = '<p><strong>Product ID:</strong> ' . $product_id . '</p>';
		$modified_description .= '<p><strong>Regular Price:</strong> ' . $regular_price . '</p>';
		$modified_description .= '<p>' . $description . '</p>';

		return $modified_description;
	}

	/**
	 * Calculate dynamic prices for cart items.
	 *
	 * @param WC_Cart $cart_object The cart object.
	 * @return void
	 */

	function calculate_dynamic_price($cart_object)
	{
		// die('Hello');
		if (is_admin() && !defined('DOING_AJAX')) {
			return;
		}

		// foreach ($cart_object->get_cart() as $cart_item) {
		// 	$cart_item['data']->set_price(15);
		// }\

		if (!is_user_logged_in()) {

			// var_dump($cart_object->get_cart());
			foreach ($cart_object->get_cart() as $cart_item_key => $cart_item) {

				$product_id = $cart_item['product_id'];
				// echo $product_id;
				$product_quantity = $cart_item['quantity'];
				if ($cart_item['variation_id']) {
					// Get the variation ID
					$variation_id = $cart_item['variation_id'];
					$dynamic_price = $this->calculate_price_qty($variation_id, $product_quantity);
				} else {
					$dynamic_price = $this->calculate_price_qty($product_id, $product_quantity);
				}
				// echo $product_id;

				// echo $product_quantity;
				// $product_id = 13;
				// $product_quantity = 7;

				// echo 'This is Product_id' . $product_id;
				// echo $product_quantity;

				// Calculate the dynamic price using a separate function
				// $dynamic_price = $this->calculate_price_qty($product_id, $product_quantity);
				// echo $dynamic_price;

				// $dynamic_price = 20;
				$cart_item['data']->set_price($dynamic_price);

				// Recalculate cart totals to reflect the new price
				// WC()->cart->calculate_totals();
			}
		}
	}

	/**
	 * Calculate the price based on the quantity for a specific product.
	 *
	 * @param int   $product_id       The ID of the product.
	 * @param int   $product_quantity The quantity of the product.
	 * @return float The calculated price.
	 */

	public function calculate_price_qty($product_id, $product_quantity)
	{
		// return $product_id;
		$price_data = $this->get_price_data();
		// $cat_price_data = $this->get_category_price_data();
		$product = wc_get_product($product_id);
		// $cat_price_data = $this->get_category_price_data();
		$product_data = array();
		foreach ($price_data as $rec) {
			if ($rec['product_id'] == $product_id) {
				// return $rec['product_id'];
				// return $rec['product_id'];
				// echo $product_id . "\n";
				// $product_id = 1;
				// return $product_id;
				$product_data[] = $rec;
			}
		}

		// if (empty($product_data)) {
		// 	if ($product && $product->is_type('simple')) {
		// 		// return 100;
		// 		foreach ($cat_price_data as $rec) {
		// 			foreach ($rec as $r) {
		// 				// return $r['product_id'];
		// 				if ($r['product_id'] == $product_id) {
		// 					// echo $product_id;
		// 					// $product_id = 1;
		// 					// return $product_id;
		// 					$product_data[] = $r;
		// 				}
		// 			}
		// 		}
		// 	} elseif ($product && $product->is_type('variable')) {
		// 		// return $product_id;
		// 		// $parent_id = $product->get_parent_id();
		// 		// return 100;
		// 	// 	foreach ($cat_price_data as $rec) {
		// 	// 		foreach ($rec as $r) {
		// 	// 			// return $r['product_id'];
		// 	// 			if ($r['product_id'] == $product_id) {
		// 	// 				// echo $product_id;
		// 	// 				// $product_id = 1;
		// 	// 				// return $product_id;
		// 	// 				$product_data[] = $r;
		// 	// 			}
		// 	// 		}
		// 	// 	}
		// 	// }
		// }

		// var_dump($product_data);
		usort($product_data, function ($a, $b) {
			return $b['min_qty'] <=> $a['min_qty'];
		});

		$output = highlight_string(print_r($product_data, true), true);
		// echo $output;
		$product = wc_get_product($product_id);
		if ($product->is_on_sale()) {
			// Product has a sale price
			$calc_price = $product->get_sale_price();
		} else {
			$calc_price = $product->get_regular_price();
		}
		foreach ($product_data as $rec) {
			if ($product_quantity >= $rec['min_qty']) {
				$calc_price = $rec['price'];
				break;
			}
		}
		// echo $calc_price;
		return $calc_price;
	}

	/**
	 * Get the price for the mini cart display.
	 *
	 * @param int   $product_id       The ID of the product.
	 * @param int   $product_quantity The quantity of the product.
	 * @return float The price for the mini cart display.
	 */

	public function price_mini_cart($product_id, $product_quantity)
	{
		$price_data = $this->get_price_data();
		$product_data = array();
		foreach ($price_data as $rec) {
			if ($rec['product_id'] == $product_id) {
				$product_data[] = $rec;
			}
		}

		$output = highlight_string(print_r($product_data, true), true);
		// echo $output;

		usort($product_data, function ($a, $b) {
			return $b['min_qty'] <=> $a['min_qty'];
		});

		$output = highlight_string(print_r($product_data, true), true);
		// echo $output;
		$calc_price = $product_data[-1]['price'];
		foreach ($product_data as $rec) {
			if ($product_quantity >= $rec['min_qty']) {
				$calc_price = $rec['price'];
				break;
			}
		}
		// echo $calc_price;
		return $calc_price;
	}

	/**
	 * Update the display of the mini cart item.
	 *
	 * @param string $product_name    The original product name.
	 * @param array  $cart_item       The cart item data.
	 * @param string $cart_item_key   The cart item key.
	 * @return string The updated display of the mini cart item.
	 */

	public function update_mini_cart_display($product_quantity, $cart_item, $cart_item_key)
	{
		$product_price = 10;
		$formatted_price = wc_price($product_price);

		return $product_quantity . ' X ' . $formatted_price;

		// return $formatted_price;

		// return $product_name . '<br><span class="mini-cart-quantity">' . $quantity = 10 . ' X ' . $price = 10 . '</span>';
		// return $product_name . '<br><span class="mini-cart-quantity">10X10</span>';
	}

	public function custom_variation_price_html($price, $variation, $product)
	{
		// Modify the variation price as needed
		echo gettype($price);
		$modified_price = wp_price(19); // Replace with your desired modified price format

		return $modified_price;
	}

	// public function get_var_price_data()
	// {
	// 	$data = $this->fetch_active_guest_rules();
	// 	$var_price_data = array();
	// 	foreach ($data as $args) {
	// 		$var_price_data[] = $this->calc_var_price_data($args->product_id, $args->price, $args->flat_or_discount_price, $args->min_qty);
	// 	}
	// 	$output = highlight_string(print_r($price_data, true), true);
	// 	// echo $output;
	// 	return $var_price_data;
	// }

	// public function calc_var_price_data($product_id, $price, $flat_or_discount_price, $min_qty)
	// {
	// 	global $product;
	// 	if ($product && $product->is_type('variable')) {
	// 		$variation_ids = array();
	// 		$variation_ids = $product->get_children();
	// 		foreach($variation_ids as $id)
	// 	}
	// }

	public function set_var_prices($price_html, $product)
	{

		if ($product && $product->is_type('variable')) {
			$price_data = $this->get_price_data();
			// $variation_ids = array();
			$variations = $product->get_available_variations();
			foreach ($variations as $variation) {
				// return $variation['variation_id'];
				$key = array_search($variation['variation_id'], array_column($price_data, 'product_id'));

				if ($key !== false) {
					$price = $price_data[$key]['price'];
					// return 100;
					// $min_qty = $price_data[$key]['min_qty'];

					return wc_price($price) . '-' . wc_price($price);
					// Use the price and min qty as needed
					// ...
				}
			}
			return $price_html;
		}
		return $price_html;
	}
	// public $global_variation_id;
	public function get_var_product_qty_html()
	{
		global $product;

		if ($product && $product->is_type('variable')) {
			$price_data = $this->get_price_data();
			// $cat_price_data = $this->get_category_price_data();
			$product_id = $product->get_id();
			$variations = $product->get_available_variations();
			$variation_data = array();

			foreach ($variations as $variation) {
				$variation_id = $variation['variation_id'];
				// Match the variation ID with product ID in $price_data array
				foreach ($price_data as $data) {
					if ($data['product_id'] == $variation_id) {
						$variation_price = $data['price'];
						$variation_min_qty = $data['min_qty'];
						// Use the variation price and min qty as needed
						// ...

						$variation_data[] = array(
							'variation_id' => $variation_id,
							'price' => $variation_price,
							'min_qty' => $variation_min_qty,
						);
						usort($variation_data, function ($a, $b) {
							return $a['min_qty'] <=> $b['min_qty'];
						});

						if (end($variation_data)['min_qty'] == 1) {
							return $table = '<div class = "dynamic-table"></div>';
						}
						// if (count($variation_data) > 0) {
						// 	foreach ($variation_data as $key => $data) {
						// 		if ($data['min_qty'] === 1) {
						// 			unset($variation_data[$key]);
						// 			// continue;
						// 		}
						// 	}
						// } else {
						// The array does not have exactly one record
						// echo "The array contains more than one record.";

						$table = '<div class = "dynamic-table"><div class = "qty-fieldset"><h1 class="qty-legend"><span>' . __('Quantity Discount', 'customer-specific-pricing-for-woocommerce') . '</span></h1><div class="qty_table_container"><table class = "qty_table">';
						$moreText = __('and more :', 'customer-specific-pricing-for-woocommerce');
						$table .= '<tr><td class = "qty-num">1' . ' ' . $moreText . '</td><td class = "qty-price">' . wc_price($this->calculate_price_qty($variation_data[0]['variation_id'], 1)) . '</td></tr>';

						foreach ($variation_data as $data) {
							if ($data['min_qty'] != 1) {
								$table .= '<tr>';
								$table .= '<td class="qty-num">' . $data['min_qty'] . ' ' . $moreText . '</td>';
								$table .= '<td class="qty-price">' . wc_price($data['price']) . '</td>';
								$table .= '</tr>';
							} // else
							// 		continue;
						}

						$table .= '</table>';
						$table .= '</div>';
						$table .= '</div>';
						$table .= '</div>';

						echo $table;
						return;
					}
				}
				// foreach ($cat_price_data as $data) {
				// 	if ($data['product_id'] == $variation_id) {
				// 		$variation_price = $data['price'];
				// 		$variation_min_qty = $data['min_qty'];
				// 		// Use the variation price and min qty as needed
				// 		// ...

				// 		$variation_data[] = array(
				// 			'variation_id' => $variation_id,
				// 			'price' => $variation_price,
				// 			'min_qty' => $variation_min_qty,
				// 		);
				// 		usort($variation_data, function ($a, $b) {
				// 			return $a['min_qty'] <=> $b['min_qty'];
				// 		});

				// 		if (end($variation_data)['min_qty'] == 1) {
				// 			return $table = '<div class = "dynamic-table"></div>';
				// 		}
				// 		// if (count($variation_data) > 0) {
				// 		// 	foreach ($variation_data as $key => $data) {
				// 		// 		if ($data['min_qty'] === 1) {
				// 		// 			unset($variation_data[$key]);
				// 		// 			// continue;
				// 		// 		}
				// 		// 	}
				// 		// } else {
				// 		// The array does not have exactly one record
				// 		// echo "The array contains more than one record.";

				// 		$table = '<div class = "dynamic-table"><div class = "qty-fieldset"><h1 class="qty-legend"><span>' . __('Quantity Discount', 'customer-specific-pricing-for-woocommerce') . '</span></h1><div class="qty_table_container"><table class = "qty_table">';
				// 		$moreText = __('and more :', 'customer-specific-pricing-for-woocommerce');
				// 		$table .= '<tr><td class = "qty-num">1' . ' ' . $moreText . '</td><td class = "qty-price">' . wc_price($this->calculate_price_qty($variation_data[0]['variation_id'], 1)) . '</td></tr>';

				// 		foreach ($variation_data as $data) {
				// 			if ($data['min_qty'] != 1) {
				// 				$table .= '<tr>';
				// 				$table .= '<td class="qty-num">' . $data['min_qty'] . ' ' . $moreText . '</td>';
				// 				$table .= '<td class="qty-price">' . wc_price($data['price']) . '</td>';
				// 				$table .= '</tr>';
				// 			} // else
				// 			// 		continue;
				// 		}

				// 		$table .= '</table>';
				// 		$table .= '</div>';
				// 		$table .= '</div>';
				// 		$table .= '</div>';

				// 		echo $table;
				// 		return;
				// 	}
				// }
			}
		}
	}

	public function get_var_product_qty_dynamic_html($variation_id)
	{

		$variation_product = wc_get_product($variation_id);
		if ($variation_product) {
			// return "Hello";
			$parent_product_id = $variation_product->get_parent_id();
			// return $parent_product_id;
			// return $parent_product_id;
			$parent_product = wc_get_product($parent_product_id);
			// return $parent_product
		}
		if ($parent_product && $parent_product->is_type('variable')) {

			// $price_data = $this->get_price_data();
			$price_data = $this->get_price_data();
			$product_data = array();
			foreach ($price_data as $rec) {
				if ($rec['product_id'] == $variation_id) {
					$product_data[] = $rec;
				}
			}
			$output = highlight_string(print_r($product_data, true), true);
			// echo $output;

			// if (empty($product_data)) {
			// foreach ($price_data as $rec) {
			// 	foreach ($rec as $r) {
			// 		if ($r['product_id'] == $parent_product_id) {
			// 			$product_data[] = $rec;
			// 		}
			// 	}
			// }
			// }

			usort($product_data, function ($a, $b) {
				return $a['min_qty'] <=> $b['min_qty'];
			});
			if (end($product_data)['min_qty'] == 1) {
				return $table = '<div class = "dynamic-table"></div>';
			}
			// var_dump($product_data);
			// return;
			// if($product_data[-1][min])
			$table = '<div class = "qty-fieldset"><h1 class="qty-legend"><span>' . __('Quantity Discount', 'customer-specific-pricing-for-woocommerce') . '</span></h1><div class="qty_table_container"><table class = "qty_table">';
			$moreText = __('and more :', 'customer-specific-pricing-for-woocommerce');
			$table .= '<tr><td class = "qty-num">1' . ' ' . $moreText . '</td><td class = "qty-price">' . wc_price($this->calculate_price_qty($variation_id, 1)) . '</td></tr>';

			foreach ($product_data as $data) {
				if ($data['min_qty'] != 1) {
					$table .= '<tr><td class = "qty-num">' . $data['min_qty'] . ' ' . $moreText . '</td><td class = "qty-price">' . wc_price($this->calculate_price_qty($data['product_id'], $data['min_qty'])) . '</td></tr>';
				}
			}


			$table .= '</table></div></div>';
			return $table;
			// return $table .= "<p>" . $description . "</p>";
		}
	}

	public function qty_var_price_frontend()
	{
		global $product;
		// Get the product ID
		if ($product && $product->is_type('variable') && !is_user_logged_in()) {
			$product_id = $product->get_id();
			if (isset($_POST['variation_id'])) {
				$variation_id = $_POST['variation_id'];

				// Output the variation ID
				// echo 'Variation ID: ' . $variation_id;
			}
			$variation_ids = $product->get_children(); // Get an array of variation IDs

			// Output the variation IDs
			foreach ($variation_ids as $variation_id) {
				// echo 'Variation ID: ' . $variation_id . '<br>';
			}
			// $variation_id = $product->get_variation_id();
			// echo $variation_id;
			// echo $product_id;
			$price = $this->calculate_price_qty($product_id, 1);
			// $price = number_format($price, 2);
			$price_html = "<br>";
			$price_html .= '<div><span>Product Total: <span>';
			$price_html .= '<span>' . get_woocommerce_currency_symbol() . '<span>';
			$price_html .= '<span class = "dynamic-price">' . $price .  '</span>';
			$price_html .= '</div>';
			// $price_html .= '<p>' . $description . '</p>';
			echo $price_html;
		}
		// return $description;
		// echo $price_html;
	}

	public function get_current_variation_id()
	{
		global $product;

		if ($product && $product->is_type('variable')) {
			// Variable product

			$variation_id = $product->get_variation_id();

			// Output the current variation ID
			// echo 'Current Variation ID: ' . $variation_id;
		}
	}

	function custom_mini_cart_fragment($fragments)
	{
		ob_start();

		// Generate the updated mini cart HTML
		// Replace this with your own logic to update the mini cart content
		wc_get_template_part('cart/mini-cart');

		$fragments['div.mini-cart'] = ob_get_clean();

		return $fragments;
	}

	/**
	 * Callback function for filters.
	 *
	 * @return void
	 */

	public function filters_cb()
	{
		if (!is_user_logged_in()) {
			add_filter('woocommerce_get_price_html', array($this, 'set_guest_prices'), 10, 2);
			// add_filter('woocommerce_get_price_html', array($this, 'set_var_prices'), 10, 2);
			add_filter('woocommerce_short_description', array($this, 'qty_price_frontend'), 10, 1);
			add_filter('woocommerce_short_description', array($this, 'get_product_qty_html'), 10, 1);
			add_action('woocommerce_before_add_to_cart_button', array($this, 'qty_var_price_frontend'), 10, 1);
			add_action('woocommerce_before_add_to_cart_button', array($this, 'get_var_product_qty_html'), 10);
			// add_filter('woocommerce_get_price_html', array($this, 'set_cat_prices'), 9, 2);
			// add_action('woocommerce_before_add_to_cart_button', array($this, 'qty_cat_price_frontend'), 10, 1);
			// add_action('woocommerce_before_add_to_cart_button', array($this, 'get_cat_product_qty_html'), 10);
			add_filter('woocommerce_sale_flash', '__return_false');
			add_filter('woocommerce_add_to_cart_fragments', 'custom_mini_cart_fragment', 10, 1);
			// add_filter('woocommerce_before_single_variation', array($this, 'qty_var_price_frontend'), 10);
			// add_filter('woocommerce_before_single_variation', array($this, 'get_var_product_qty_html'), 10);
			// add_filter('woocommerce_variation_prices_price', array($this, 'custom_variation_price_html'), 10, 3);
			// add_filter('woocommerce_cart_item_quantity', 'update_mini_cart_display', 10, 3);
			// add_filter('woocommerce_mini_cart_item_name', 'update_mini_cart_display', 10, 3);



			// add_action('woocommerce_before_add_to_cart_button', array($this, 'set_cat_prices'), 999);
			// add_action('woocommerce_before_single_variation', array($this, 'get_current_variation_id'));
		}
	}










	/**
	 * Fetch active guest rules from the database.
	 *
	 * @return array The fetched guest rules.
	 */

	// public function get_category_rules()
	// {
	// 	global $wpdb;
	// 	$table_name = $wpdb->prefix . "wcsp_role_category_pricing_mapping";
	// 	$results = $wpdb->get_results(
	// 		$wpdb->prepare("SELECT cat_slug, price, flat_or_discount_price, min_qty FROM {$table_name} WHERE role = 'guest'")
	// 	);
	// 	$data = array();
	// 	// var_dump($results);
	// 	// $results = (array) $results;
	// 	foreach ($results as $result) {
	// 		$entry = array(
	// 			'cat_slug' => $result->cat_slug,
	// 			'price' => $result->price,
	// 			'flat_or_discount_price' => $result->flat_or_discount_price,
	// 			'min_qty' => $result->min_qty,
	// 			'product_ids' => $this->get_cat_product_ids($result->cat_slug),
	// 		);

	// 		$data[] = $entry;
	// 	}
	// 	$output = highlight_string(print_r($data, true), true);
	// 	// echo $output;
	// 	// var_dump($data);
	// 	// echo "<br><br>";
	// 	return $data;
	// }

	// public function get_cat_product_ids($cat_slug)
	// {
	// 	// $cat_slug = 'hoodies'; // Specify the category slug

	// 	$category = get_term_by('slug', $cat_slug, 'product_cat'); // Get the category term object

	// 	if ($category) {
	// 		$args = array(
	// 			'post_type' => 'product',
	// 			'post_status' => 'publish',
	// 			'posts_per_page' => -1,
	// 			'tax_query' => array(
	// 				array(
	// 					'taxonomy' => 'product_cat',
	// 					'field' => 'term_id',
	// 					'terms' => $category->term_id,
	// 					'include_children' => true,
	// 				),
	// 			),
	// 		);

	// 		$products = get_posts($args);
	// 		// unset($products);
	// 		if ($products) {
	// 			$product_ids = wp_list_pluck($products, 'ID');
	// 			// Use the product IDs as needed
	// 			return $product_ids;
	// 		}
	// 	}
	// }

	// public function get_category_price_data()
	// {
	// 	$rules_data = $this->get_category_rules();
	// 	// echo "<h1>This is '$rules_data'</h1>";
	// 	// var_dump($rules_data);
	// 	$price_data = array();
	// 	foreach ($rules_data as $data) {
	// 		// var_dump($data['product_ids']);
	// 		// echo $data['price'] . "\n";
	// 		// echo $data->flat_or_discount_price . "\n";
	// 		// echo  $data->min_qty . "\n";
	// 		// $product_ids
	// 		$price_data[] = $this->calc_cat_price($data['product_ids'], $data['price'], $data['flat_or_discount_price'], $data['min_qty']);
	// 		// echo $calc_price;
	// 	}

	// 	$output = highlight_string(print_r($price_data, true), true);
	// 	// echo $output;
	// 	return $price_data;
	// }

	// public function calc_cat_price($product_ids, $price, $flat_or_discount_price, $min_qty)
	// {
	// 	// echo "<h1>" . $product_ids . "</h1>";
	// 	$price_by_id = array();
	// 	// $category_id = 'your_category_id'; // Replace with the actual category ID
	// 	if ($flat_or_discount_price == 1) {

	// 		// if (is_array($product_ids)) {
	// 		// echo "<h1>Hello</h1>";
	// 		foreach ($product_ids as $product_id) {
	// 			$product = wc_get_product($product_id);
	// 			if ($product && $product->is_type('simple')) {
	// 				$regular_price = $product->get_regular_price();
	// 				// echo "Product ID - " .  $product_id;
	// 				// echo "Regular Price - " .  $regular_price;
	// 			} elseif ($product && $product->is_type('variable')) {
	// 				$regular_price = $product->get_variation_regular_price();
	// 				// echo "Product ID - " .  $product_id;
	// 				// echo "Regular Price - " .  $regular_price;
	// 			}
	// 			if ($regular_price < $price)
	// 				$new_price = $regular_price;

	// 			$price_by_id[] = array(
	// 				'product_id'	=>	$product_id,
	// 				'regular_price'	=>	$regular_price,
	// 				'price'			=>	$new_price,
	// 				'min_qty'		=>	$min_qty
	// 			);
	// 		}
	// 		// }
	// 	} elseif ($flat_or_discount_price == 2) {
	// 		// echo "<h1>Inside calc_cat_price</h1>";
	// 		if (is_array($product_ids)) {
	// 			foreach ($product_ids as $product_id) {
	// 				$product = wc_get_product($product_id);
	// 				if ($product && $product->is_type('simple')) {
	// 					$regular_price = $product->get_regular_price();
	// 					$new_price = $regular_price - ($regular_price * $price * 0.01);
	// 					// echo "<h1>Product ID - " .  $product_id . "</h1>\n";
	// 					// echo "<h1>Regular Price - " .  $regular_price . "</h1>\n";
	// 				} elseif ($product && $product->is_type('variable')) {
	// 					$regular_price = $product->get_variation_regular_price();
	// 					$new_price = $regular_price - ($regular_price * $price * 0.01);
	// 					// echo "<h1>Product ID - " .  $product_id . "</h1>\n";
	// 					// echo "<h1>Regular Price - " .  $regular_price . "</h1>\n";
	// 				}
	// 				if ($regular_price < $new_price)
	// 					$new_price = $regular_price;

	// 				$price_by_id[] = array(
	// 					'product_id'	=>	$product_id,
	// 					'price'			=>	$new_price,
	// 					'min_qty'		=>	$min_qty
	// 				);
	// 			}
	// 		}
	// 	}

	// 	return $price_by_id;
	// }

	// public function set_cat_prices($price_html, $product)
	// {

	// 	// return 10;
	// 	$price_data = $this->get_category_price_data();
	// 	$product_id = $product->get_id();

	// 	if ($product) {

	// 		foreach ($price_data as $data) {
	// 			foreach ($data as $datapoint) {
	// 				// echo "Hello is it getting printed bro?";
	// 				// var_dump($datapoint);
	// 				// echo $datapoint['product_id'] . "\n";
	// 				// echo $datapoint['price'] . "\n";
	// 				if ($product_id == $datapoint['product_id']) {
	// 					// echo $datapoint['product_id'] . "\n";
	// 					// echo $datapoint['price'] . "\n";
	// 					$price_html = '<span class="price">' . wc_price($datapoint['price'])  . ' - ' . wc_price($datapoint['price']) . '</span>';
	// 				}
	// 			}
	// 		}
	// 	} //elseif($product && $product->is_type('variable')){

	// 	// }
	// 	return $price_html;
	// }

	// public function qty_cat_price_frontend()
	// {
	// }
	// public function get_cat_product_qty_html()
	// {
	// 	// echo 'Hello';
	// 	global $product;

	// 	if ($product && $product->is_type('variable')) {
	// 		$price_data = $this->get_category_price_data();
	// 		$product_id = $product->get_id();
	// 		echo $product_id . "\n";
	// 		$variations = $product->get_available_variations();
	// 		$variation_data = array();

	// 		foreach ($variations as $variation) {
	// 			$variation_id = $variation['variation_id'];
	// 			echo $variation_id . "\n";
	// 			// Match the variation ID with product ID in $price_data array
	// 			foreach ($price_data as $data) {
	// 				foreach ($data as $d) {
	// 					// var_dump($d);
	// 					if ($d['product_id'] == $product_id) {

	// 						$variation_price = $d['price'];
	// 						$variation_min_qty = $d['min_qty'];
	// 						// Use the variation price and min qty as needed
	// 						// ...

	// 						$variation_data[] = array(
	// 							'variation_id' => $variation_id,
	// 							'price' => $variation_price,
	// 							'min_qty' => $variation_min_qty,
	// 						);
	// 						usort($variation_data, function ($a, $b) {
	// 							return $a['min_qty'] <=> $b['min_qty'];
	// 						});
	// 						// var_dump($variation_data);
	// 						if (end($variation_data)['min_qty'] == 1) {
	// 							return $table = '<div class = "dynamic-table"></div>';
	// 						}
	// 						// if (count($variation_data) > 0) {
	// 						// 	foreach ($variation_data as $key => $d) {
	// 						// 		if ($d['min_qty'] === 1) {
	// 						// 			unset($variation_data[$key]);
	// 						// 			// continue;
	// 						// 		}
	// 						// 	}
	// 						// } else {
	// 						// The array does not have exactly one record
	// 						// echo "The array contains more than one record.";

	// 						$table = '<div class = "dynamic-table"><div class = "qty-fieldset"><h1 class="qty-legend"><span>' . __('Quantity Discount', 'customer-specific-pricing-for-woocommerce') . '</span></h1><div class="qty_table_container"><table class = "qty_table">';
	// 						$moreText = __('and more :', 'customer-specific-pricing-for-woocommerce');
	// 						$table .= '<tr><td class = "qty-num">1' . ' ' . $moreText . '</td><td class = "qty-price">' . wc_price($this->calculate_price_qty($variation_data[0]['variation_id'], 1)) . '</td></tr>';

	// 						foreach ($variation_data as $d) {
	// 							if ($d['min_qty'] != 1) {
	// 								$table .= '<tr>';
	// 								$table .= '<td class="qty-num">' . $d['min_qty'] . ' ' . $moreText . '</td>';
	// 								$table .= '<td class="qty-price">' . wc_price($d['price']) . '</td>';
	// 								$table .= '</tr>';
	// 							} // else
	// 							// 		continue;
	// 						}

	// 						$table .= '</table>';
	// 						$table .= '</div>';
	// 						$table .= '</div>';
	// 						$table .= '</div>';

	// 						echo $table;
	// 						return;
	// 					}
	// 				}
	// 			}
	// 		}
	// 	}
	// }
}
