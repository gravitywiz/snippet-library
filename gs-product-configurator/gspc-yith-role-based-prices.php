<?php
/**
 * Gravity Shop // Product Configurator // Apply YITH Role Based Prices to Configured Products
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 *
 * YITH WooCommerce Role Based Prices replaces a cart item's price with the role price of the product alone,
 * so the price of the options chosen in the Product Configurator form is dropped from the cart.
 *
 * This snippet charges the product's role-based price plus the form's options.
 *
 * Instructions:
 *
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
class GSPC_YITH_Role_Based_Prices {

	private $priced_products;

	public function __construct() {
		$this->priced_products = new SplObjectStorage();

		// Run after GSPC adds the form's options to the cart item's price.
		add_filter( 'woocommerce_add_cart_item', array( $this, 'apply_role_based_price' ), 20 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'apply_role_based_price' ), 30 );

		// Stop YITH from replacing the price set here.
		add_filter( 'yith_wcrbp_return_original_price', array( $this, 'keep_cart_item_price' ), 10, 3 );
	}

	public function apply_role_based_price( $cart_item ) {
		if ( empty( $cart_item['gspc_gf_entry_ids'] ) || ! function_exists( 'YITH_Role_Based_Prices_Product' ) ) {
			return $cart_item;
		}

		$product        = $cart_item['data'];
		$stored_product = wc_get_product( $product->get_id() );

		$base_price    = (float) $stored_product->get_price( 'edit' );
		$options_price = (float) $product->get_price( 'edit' ) - $base_price;
		$role_price    = (float) YITH_Role_Based_Prices_Product()->get_price( $base_price, $stored_product );

		$product->set_price( $role_price + $options_price );
		$this->priced_products->attach( $product );

		return $cart_item;
	}

	public function keep_cart_item_price( $return_original, $price, $product ) {
		if ( $product instanceof WC_Product && $this->priced_products->contains( $product ) ) {
			return true;
		}

		return $return_original;
	}
}

new GSPC_YITH_Role_Based_Prices();
