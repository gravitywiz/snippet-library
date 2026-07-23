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
 * Version:      0.2
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
			'tax_amount_type'     => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_pre_render', array( $this, 'set_tax_amount_by_field_value' ) );
		add_filter( 'gform_pre_process', array( $this, 'set_tax_amount_by_field_value' ) );

		add_action( 'gform_product_info', array( $this, 'set_tax_amount_by_field_value_in_order' ), 8, 3 );

		// Single-page support: on multi-page forms, gform_pre_render re-fires on the Tax field's page with the
		// source field's value in POST, so the rendered tax amount is already correct. On a single-page form there
		// is no such re-render, so we recalculate the displayed tax client-side as the source field changes.
		add_filter( 'gform_register_init_scripts', array( $this, 'register_init_script' ) );

	}

	function set_tax_amount_by_field_value( $form ) {

		if ( ! $this->is_applicable_form( $form ) || $form['fields'][0]->is_form_editor() ) {
			return $form;
		}

		foreach ( $form['fields'] as $field ) {
			if ( $field->id == $this->_args['tax_field_id'] ) {

				$value = rgpost( sprintf(
					'input_%s',
					implode( '_', explode( '.', $this->_args['value_field_id'] ) )
				) );

				$field->taxAmount     = $this->get_tax_amount_by_value( $value );
				$field->taxAmountType = $this->get_amount_type( $field );
			}
		}

		return $form;
	}

	function set_tax_amount_by_field_value_in_order( $order, $form, $entry ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $order;
		}

		$tax_field = GFAPI::get_field( $form, $this->_args['tax_field_id'] );
		$value     = rgar( $entry, $this->_args['value_field_id'] );

		// Pass entry so dynamic field lookup works during submission
		$tax_field->taxAmount     = $this->get_tax_amount_by_value( $value, $entry );
		$tax_field->taxAmountType = $this->get_amount_type( $tax_field );

		return $order;
	}

	function get_tax_amount_by_value( $value, $entry = null ) {

		/**
		 * If a tax amount field ID is provided, use its value directly.
		 * This allows the tax amount to come from another field instead of
		 * the static tax_amounts configuration.
		 */
		if ( ! empty( $this->_args['tax_amount_field_id'] ) ) {

			// During submission we have entry data
			if ( $entry ) {
				$tax_amount = rgar( $entry, $this->_args['tax_amount_field_id'] );
			} else {
				$tax_amount = rgpost( sprintf(
					'input_%s',
					implode( '_', explode( '.', $this->_args['tax_amount_field_id'] ) )
				) );
			}

			return floatval( $tax_amount );
		}

		$tax_amount = rgar( $this->_args['tax_amounts'], $value, false );

		// Check for catch all amount if there is no tax amount for the given value.
		if ( $tax_amount === false ) {
			$tax_amount = rgar( $this->_args['tax_amounts'], '*', 0 );
		}

		return $tax_amount;
	}

	/**
	 * Resolve the amount type ('flat' or 'percent') to apply to the Tax field.
	 *
	 * GPECF leaves unconfigured Tax fields with an empty amount type, causing PHP and JS
	 * calculations to disagree. Default empty types to 'flat' to keep both sides in sync.
	 */
	private function get_amount_type( $field ) {

		if ( ! empty( $this->_args['tax_amount_type'] ) ) {
			return $this->_args['tax_amount_type'];
		}

		$type = is_object( $field ) ? $field->taxAmountType : '';

		return $type ? $type : 'flat';
	}

	/**
	 * Register a per-form init script that keeps the displayed tax amount in sync with the source field on
	 * single-page forms (where PHP only renders the Tax field once, before the source field has a value).
	 */
	public function register_init_script( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		// The field that drives the tax amount: the dynamic tax amount field if provided, otherwise the value
		// field that the tax_amounts map is keyed by.
		$source_field_id = ! empty( $this->_args['tax_amount_field_id'] )
			? $this->_args['tax_amount_field_id']
			: $this->_args['value_field_id'];

		if ( ! $source_field_id || ! $this->_args['tax_field_id'] ) {
			return $form;
		}

		$config = array(
			'formId'           => (int) $form['id'],
			'taxFieldId'       => (string) $this->_args['tax_field_id'],
			'sourceFieldId'    => (string) $source_field_id,
			'isDynamicAmount'  => ! empty( $this->_args['tax_amount_field_id'] ),
			'taxAmounts'       => (object) $this->_args['tax_amounts'],
			'amountType'       => $this->get_amount_type( GFAPI::get_field( $form, $this->_args['tax_field_id'] ) ),
		);

		$script = 'gform.gpecfTaxByFieldValue( ' . wp_json_encode( $config ) . ' );';

		// Define the helper once, regardless of how many instances of this snippet are running.
		$helper = <<<'JS'
		if ( typeof gform.gpecfTaxByFieldValue === 'undefined' ) {
			gform.gpecfTaxByFieldValue = function( config ) {
				( function( $ ) {

					var formId  = config.formId,
						$form   = $( '#gform_' + formId );

					if ( ! $form.length ) {
						return;
					}

					function normalizeId( id ) {
						return String( id ).replace( /\./g, '_' );
					}

					// Read the current value of a field, handling text/select/hidden inputs as well as radio/checkbox groups.
					function getFieldValue( fieldId ) {

						var normId = normalizeId( fieldId ),
							$byId  = $( '#input_' + formId + '_' + normId );

						if ( $byId.length && ! $byId.is( ':radio, :checkbox' ) ) {
							return $byId.val();
						}

						var $byName = $form.find( '[name="input_' + fieldId + '"], [name="input_' + normId + '"]' );

						if ( $byName.filter( ':radio, :checkbox' ).length ) {
							return $byName.filter( ':checked' ).val();
						}

						return $byName.length ? $byName.val() : $byId.val();
					}

					// True only when both the Tax field and its source field are present on the current page. On multi-page
					// forms where the source field lives on an earlier page, PHP has already set the correct amount, so we
					// leave it alone rather than clobbering it with a catch-all value.
					function canCalculate() {
						var $tax = $( '#input_' + formId + '_' + normalizeId( config.taxFieldId ) ),
							$src = $( '#input_' + formId + '_' + normalizeId( config.sourceFieldId ) );

						if ( ! $src.length ) {
							$src = $form.find( '[name="input_' + config.sourceFieldId + '"], [name="input_' + normalizeId( config.sourceFieldId ) + '"]' );
						}

						return $tax.length && $src.length;
					}

					function computeTaxAmount() {

						var value = getFieldValue( config.sourceFieldId );

						if ( config.isDynamicAmount ) {
							return parseFloat( value ) || 0;
						}

						var amounts = config.taxAmounts || {};

						if ( value != null && amounts.hasOwnProperty( value ) ) {
							return parseFloat( amounts[ value ] ) || 0;
						}

						if ( amounts.hasOwnProperty( '*' ) ) {
							return parseFloat( amounts['*'] ) || 0;
						}

						return 0;
					}

					function updateTaxAmount() {

						if ( ! canCalculate() ) {
							return;
						}

						var $taxInput = $( '#input_' + formId + '_' + normalizeId( config.taxFieldId ) ),
							amount    = computeTaxAmount();

						if ( String( $taxInput.data( 'amount' ) ) === String( amount ) && String( $taxInput.data( 'amounttype' ) ) === String( config.amountType ) ) {
							return;
						}

						// The GPECF calculation reads these via jQuery .data(), so update both the cached data values and the
						// attributes. amounttype must match what the server uses (see get_amount_type) or the displayed total
						// and the server-validated total will disagree. Then recalculate to refresh the displayed tax.
						$taxInput
							.data( 'amount', amount ).attr( 'data-amount', amount )
							.data( 'amounttype', config.amountType ).attr( 'data-amounttype', config.amountType );

						if ( typeof gformCalculateTotalPrice === 'function' ) {
							gformCalculateTotalPrice( formId );
						}
					}

					var ns = '.gpecfTax' + normalizeId( config.taxFieldId );

					// Rebind on every render (AJAX page changes re-run init scripts). Namespaced + .off() prevents stacking.
					$form
						.off( 'change' + ns + ' input' + ns )
						.on( 'change' + ns + ' input' + ns, '[name="input_' + config.sourceFieldId + '"], [name="input_' + normalizeId( config.sourceFieldId ) + '"], #input_' + formId + '_' + normalizeId( config.sourceFieldId ), function() {
							updateTaxAmount();
						} );

					updateTaxAmount();

				} )( jQuery );
			};
		}
		JS;

		GFFormDisplay::add_init_script(
			$form['id'],
			'gpecf_tax_helper',
			GFFormDisplay::ON_PAGE_RENDER,
			$helper
		);

		GFFormDisplay::add_init_script(
			$form['id'],
			'gpecf_tax_by_field_value_' . $this->_args['tax_field_id'],
			GFFormDisplay::ON_PAGE_RENDER,
			$script
		);

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
) );
