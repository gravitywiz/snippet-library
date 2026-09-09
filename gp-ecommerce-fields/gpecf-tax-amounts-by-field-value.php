<?php
/**
 * Gravity Perks // eCommerce Fields // Tax Amount by Field Value
 * https://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 *
 * Instruction Video: https://www.loom.com/share/ca76b1f523f843e4b7d978a9c4877e61
 *
 * Set the tax amount of a Tax field based on the value of a field on a previous page.
 *
 * Plugin Name:  GP eCommerce Fields — Tax Amount by Field Value
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 * Description:  Set the tax amount of a Tax field based on the value of a field on a previous page.
 * Author:       Gravity Wiz
 * Version:      0.3
 * Author URI:   http://gravitywiz.com
 */
class GPECF_Tax_Amounts_By_Field_Value {

	private $_args = array();

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'             => false,
			'value_field_id'      => false,
			'tax_field_id'        => false,
			'tax_amounts'         => array(),
			'tax_amount_field_id' => false, // Optional dynamic tax source field
			'tax_amount_type'     => 'flat',
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_pre_render', array( $this, 'set_tax_amount_by_field_value' ) );
		add_filter( 'gform_pre_process', array( $this, 'set_tax_amount_by_field_value' ) );

		add_action( 'gform_product_info', array( $this, 'set_tax_amount_by_field_value_in_order' ), 8, 3 );

		add_filter( 'gform_register_init_scripts', array( $this, 'register_init_script' ) );

	}

	function set_tax_amount_by_field_value( $form ) {

		if ( ! $this->is_applicable_form( $form ) || GFCommon::is_form_editor() ) {
			return $form;
		}

		foreach ( $form['fields'] as $field ) {
			if ( $field->id == $this->_args['tax_field_id'] ) {
				$field->taxAmount     = $this->get_tax_amount_by_value( $this->get_submitted_value( $this->_args['value_field_id'] ) );
				$field->taxAmountType = $this->_args['tax_amount_type'];
			}
		}

		return $form;
	}

	function set_tax_amount_by_field_value_in_order( $order, $form, $entry ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $order;
		}

		$tax_field = GFAPI::get_field( $form, $this->_args['tax_field_id'] );

		if ( ! $tax_field ) {
			return $order;
		}

		// Pass entry so dynamic field lookup works during submission
		$tax_field->taxAmount     = $this->get_tax_amount_by_value( rgar( $entry, $this->_args['value_field_id'] ), $entry );
		$tax_field->taxAmountType = $this->_args['tax_amount_type'];

		return $order;
	}

	function get_tax_amount_by_value( $value, $entry = null ) {

		/**
		 * If a tax amount field ID is provided, use its value directly.
		 * This allows the tax amount to come from another field instead of
		 * the static tax_amounts configuration.
		 */
		if ( ! empty( $this->_args['tax_amount_field_id'] ) ) {

			// During submission we have entry data; otherwise fall back to the posted value.
			$tax_amount = $entry
				? rgar( $entry, $this->_args['tax_amount_field_id'] )
				: $this->get_submitted_value( $this->_args['tax_amount_field_id'] );

			return $this->normalize_amount( $tax_amount );
		}

		$tax_amount = rgar( $this->_args['tax_amounts'], $value, false );

		// Check for catch all amount if there is no tax amount for the given value.
		if ( $tax_amount === false ) {
			$tax_amount = rgar( $this->_args['tax_amounts'], '*', 0 );
		}

		return $tax_amount;
	}

	public function normalize_amount( $value ) {

		if ( is_array( $value ) ) {
			$value = reset( $value );
		}

		if ( is_string( $value ) && strpos( $value, '|' ) !== false ) {
			$parts = explode( '|', $value );
			$value = end( $parts );
		}

		return (float) GFCommon::to_number( $value );
	}

	public function get_submitted_value( $field_id ) {

		if ( empty( $field_id ) ) {
			return '';
		}

		return rgpost( 'input_' . str_replace( '.', '_', $field_id ) );
	}

	public function register_init_script( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		$script = sprintf(
			'( function( $ ) {
				var formId = %2$d, sourceId = "%3$s", taxInputId = "#input_%2$d_%1$d", amountType = "%4$s";
				gform.addFilter( "gform_product_total", function( total, fid ) {
					if ( parseInt( fid, 10 ) !== formId ) {
						return total;
					}
					var $tax = $( taxInputId );
					if ( ! $tax.length ) {
						return total;
					}
					$tax.data( "amounttype", amountType ).attr( "data-amounttype", amountType );
					if ( ! sourceId ) {
						return total;
					}
					var $source = $( "#input_" + formId + "_" + sourceId );
					if ( ! $source.length ) {
						$source = $( "#gform_" + formId ).find( "[name=\'input_" + sourceId + "\']" );
					}
					var amount = window.gformToNumber ? gformToNumber( $source.val() ) : parseFloat( $source.val() );
					if ( ! amount || isNaN( amount ) ) {
						amount = 0;
					}
					$tax.data( "amount", amount ).attr( "data-amount", amount );
					return total;
				}, 50 /* GPECF applies tax at 51 */ );

				if ( ! sourceId ) {
					return;
				}

				var ns = ".gpecfTaxAmount" + formId + "_%1$d", timer = null;

				$( document ).off( ns ).on( "change" + ns + " input" + ns, "#input_" + formId + "_" + sourceId + ", #gform_" + formId + " [name=\'input_" + sourceId + "\']", function() {
					clearTimeout( timer );
					timer = setTimeout( function() {
						$( document ).trigger( "gform_post_conditional_logic", [ formId, null, false ] );
					}, 250 );
				} );
			} )( jQuery );',
			$this->_args['tax_field_id'],
			$form['id'],
			$this->_args['tax_amount_field_id'] ? str_replace( '.', '_', $this->_args['tax_amount_field_id'] ) : '',
			$this->_args['tax_amount_type']
		);

		GFFormDisplay::add_init_script( $form['id'], 'gpecf_tax_amount_by_field_value_' . $this->_args['tax_field_id'], GFFormDisplay::ON_PAGE_RENDER, $script );

		return $form;
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

}

# Configuration

// Option 1 — Static mapping
new GPECF_Tax_Amounts_By_Field_Value( array(
	'form_id'        => 123,
	'value_field_id' => 4,
	'tax_field_id'   => 5,
	'tax_amounts'    => array(
		'23325' => 10,
		'23462' => 25,
		// Provide a catch-all value.
		'*'     => 50,
	),
) );

// Option 2 — Pull tax amount dynamically from another field
new GPECF_Tax_Amounts_By_Field_Value( array(
	'form_id'             => 123,
	'tax_field_id'        => 5,
	'tax_amount_field_id' => 7,
	'tax_amount_type'     => 'flat',
) );
