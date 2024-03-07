<?php
/**
 * Gravity Shop // Product Configurator // Include WC Product Quantity in Total Field
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 *
 * Include the WooCommerce product quantity in the Gravity Forms Total field calculation.
 *
 * Plugin Name:  GSPC Include WC Product Quantity in Total
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 * Description:  Include the WooCommerce product quantity in the Gravity Forms Total field calculation.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com
 */
class GSPC_Include_WC_Product_Quantity_In_Total {

	public $_args;

	public function __construct( $args = array() ) {

		// Set default arguments, parse against the provided arguments, and store for use throughout the class.
		$this->_args = wp_parse_args( $args, array(
			'form_id' => false,
		) );

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ) );
		add_action( 'gform_register_init_scripts', array( $this, 'add_init_script' ) );
		add_filter( 'gform_pre_process', array( $this, 'bypass_total_validation' ) );

	}

	public function load_form_script( $form ) {

		if ( $this->is_applicable_form( $form ) && ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	public function output_script() {
		?>

		<script type="text/javascript">

			( function( $ ) {

				window.<?php echo __CLASS__; ?> = function( args ) {

					var self = this;
					self.formId = args.formId;

					self.init = function() {
						let $wc_quantity = $( 'form#gform_' + self.formId ).find( 'input[name="quantity"]' );

						$wc_quantity.on( 'change', function() {
							gformCalculateTotalPrice( self.formId );
						} );

						gform.addFilter( 'gform_product_total', function( total, formId ) {
							if ( formId == self.formId ) {
								total *= gformToNumber( $wc_quantity.val() );
							}
							return total;
						} );
					};

					self.init();

				}

			} )( jQuery );

		</script>

		<?php
	}

	public function add_init_script( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$args = array(
			'formId' => $this->_args['form_id'],
		);

		$script = 'new ' . __CLASS__ . '( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( strtolower( __CLASS__ ), $this->_args['form_id'] ) );

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	function bypass_total_validation( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( $field->type === 'total' ) {
				$field->validateTotal = false;
			}
		}

		return $form;
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

}

// Configuration
new GSPC_Include_WC_Product_Quantity_In_Total( array(
	// Update "123" to your form ID
	  'form_id' => 123,
) );
