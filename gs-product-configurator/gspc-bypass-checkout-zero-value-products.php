<?php
/**
 * Gravity Shop // Product Configurator // Bypass Checkout for Zero-Value Products
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 *
 * When a configured product is added to the cart and the cart needs neither payment nor shipping,
 * create the WooCommerce order immediately and send the customer to a confirmation page instead of
 * the cart/checkout.
 *
 * Instructions:
 *
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 *   2. Configure the snippet based on the inline instructions at the bottom of the file.
 *
 * Note: billing/shipping details come from whatever WooCommerce already knows about the customer
 * (their account, or values provided via the `woocommerce_checkout_get_value` filter). Guests will
 * produce orders with empty billing details.
 */
class GSPC_Bypass_Checkout_For_Zero_Value_Products {
	private $_args;

	public function __construct( $args = array() ) {
		$this->_args = wp_parse_args( $args, array(
			'form_id'              => false,
			'confirmation_page_id' => false,
		) );

		add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'maybe_bypass_checkout' ), 10, 2 );
	}

	public function maybe_bypass_checkout( $url, $product ) {
		if ( ! function_exists( 'gspc_get_product_form' ) || ! $product || ! $this->is_applicable_form( $product ) ) {
			return $url;
		}

		$cart = WC()->cart;

		// needs_payment() is filterable, so extensions (e.g. WooCommerce Subscriptions) get their say.
		if ( $cart->needs_payment() || $cart->needs_shipping() ) {
			return $url;
		}

		// Same validation WooCommerce runs before checkout (stock, coupons, GSPC entry checks).
		do_action( 'woocommerce_check_cart_items' );

		if ( $cart->is_empty() || wc_notice_count( 'error' ) ) {
			return $url;
		}

		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		$checkout = WC()->checkout();
		$data     = array( 'payment_method' => '' );

		foreach ( $checkout->get_checkout_fields() as $fields ) {
			foreach ( array_keys( $fields ) as $key ) {
				$data[ $key ] = (string) $checkout->get_value( $key );
			}
		}

		$order_id = $checkout->create_order( $data );

		if ( is_wp_error( $order_id ) ) {
			wc_add_notice( $order_id->get_error_message(), 'error' );
			return $url;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return $url;
		}

		// Mirror WC_Checkout::process_checkout() / process_order_without_payment().
		do_action( 'woocommerce_checkout_order_processed', $order_id, $data, $order );
		$order->payment_complete();
		wc_empty_cart();
		wc_clear_notices(); // Drop the "added to your cart" notice; it would otherwise show on the next page.

		return $this->get_confirmation_url( $order );
	}

	public function is_applicable_form( $product ) {
		$form = gspc_get_product_form( $product );

		if ( ! $form ) {
			return false;
		}

		if ( empty( $this->_args['form_id'] ) ) {
			return true;
		}

		return in_array( (int) $form['id'], array_map( 'intval', (array) $this->_args['form_id'] ), true );
	}

	public function get_confirmation_url( $order ) {
		$page_url = $this->_args['confirmation_page_id'] ? get_permalink( (int) $this->_args['confirmation_page_id'] ) : false;

		if ( $page_url ) {
			return $page_url;
		}

		return apply_filters( 'woocommerce_checkout_no_payment_needed_redirect', $order->get_checkout_order_received_url(), $order );
	}
}

new GSPC_Bypass_Checkout_For_Zero_Value_Products( array(
	'form_id'              => 123, // A form ID (or array of form IDs) to limit the bypass to. Set to false for all forms.
	'confirmation_page_id' => 456, // Page ID to send the customer to after the order is created. Set to false for WooCommerce's order-received page.
) );
