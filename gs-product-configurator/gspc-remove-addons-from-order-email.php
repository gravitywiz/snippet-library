<?php
/**
 * Gravity Shop // Product Configurator // Remove GSPC Product Add-ons in WC Order Email
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 */
class GSPC_Remove_Addons_In_Order_Email {

	private $_args;

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id' => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_action( 'woocommerce_before_template_part', function( $template_name ) {
			if ( $template_name === 'emails/email-order-items.php' ) {
				add_filter( 'gspc_addons', array( $this, 'remove_gspc_product_addons' ), 10, 4 );
			}
		} );

		add_action( 'woocommerce_after_template_part', function( $template_name ) {
			if ( $template_name === 'emails/email-order-items.php' ) {
				remove_filter( 'gspc_addons', array( $this, 'remove_gspc_product_addons' ), 10, 4 );
			}
		} );

	}

	function remove_gspc_product_addons( $addons, $object, $form, $entry ) {
		if ( $this->is_applicable_form( $form ) ) {
			$addons = array();
		}
		return $addons;
	}

	public function is_applicable_form( $form ) {
		return empty( $this->_args['form_id'] ) || (int) ( $form['id'] ?? $form ) === (int) $this->_args['form_id'];
	}

}

# Configuration

new GSPC_Remove_Addons_In_Order_Email( array(
	'form_id' => 123,
) );
