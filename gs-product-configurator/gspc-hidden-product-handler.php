<?php
/**
 * Gravity Wiz // Remove URL and Edit Button for Hidden Products in Cart.
 * https://gravitywiz.com/
 *
 * Plugin Name:  Remove URL & Edit Button for Hidden Products in Cart
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Remove the product link and edit button for hidden WooCommerce products in the cart.
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   https://gravitywiz.com/
 */

class GW_Hidden_Product_Handler {

	private $_args = array();

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
		) );

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {
		add_filter( 'woocommerce_cart_item_name', array( $this, 'remove_url_for_hidden_product' ), 10, 3 );
		add_filter( 'gspc_cart_item_edit_link', array( $this, 'maybe_remove_edit_button' ), 10, 2 );
	}

	/**
	 * Helper function to check if the product is hidden.
	 *
	 * @param array $cart_item The cart item data.
	 *
	 * @return bool True if the product is hidden, false otherwise.
	 */
	public function is_hidden_product( $cart_item ) {
		$product = $cart_item['data'];

		if ( ! ( $product instanceof WC_Product ) ) {
			return false;
		}

		return ( $product->get_catalog_visibility() === 'hidden' );
	}

	/**
	 * Remove the URL for hidden products in the cart.
	 *
	 * @param string $product_name  The product name.
	 * @param array  $cart_item     The cart item data.
	 * @param string $cart_item_key The cart item key.
	 *
	 * @return string Modified product name without link for hidden products.
	 */
	public function remove_url_for_hidden_product( $product_name, $cart_item, $cart_item_key ) {
		if ( $this->is_hidden_product( $cart_item ) ) {
			// Remove the link for hidden products
			$product_name = strip_tags( $product_name );
		}

		return $product_name;
	}

	/**
	 * Remove the edit button for hidden products in the cart.
	 *
	 * @param string $edit_link The edit link HTML.
	 * @param array  $cart_item The cart item data.
	 *
	 * @return string Modified edit link (empty) if the product is hidden.
	 */
	public function maybe_remove_edit_button( $edit_link, $cart_item ) {
		if ( $this->is_hidden_product( $cart_item ) ) {
			// Remove the edit link for hidden products
			return '';
		}

		return $edit_link;
	}
}

// Initialize the class with no specific arguments
new GW_Hidden_Product_Handler();
