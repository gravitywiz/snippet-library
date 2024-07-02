<?php
/**
 * Gravity Perks // GP eCommerce Fields // Set Discount Amount by Field Value
 * http://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 */
class GPECF_Discount_Amounts_By_Field_Value {

	private $_args = array();

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'           => false,
			'discount_field_id' => false,
			'amount_field_id'   => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );

		add_filter( 'gform_pre_process', array( $this, 'set_discount_amount_by_field_value' ) );
		add_action( 'gform_product_info', array( $this, 'set_discount_amount_for_order' ), 8, 3 );

	}

	public function set_discount_amount_by_field_value( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( $field->id == $this->_args['discount_field_id'] ) {
				$field->discountAmount = rgpost( 'input_' . $this->_args['amount_field_id'] );
			}
		}

		return $form;
	}

	public function set_discount_amount_for_order( $order, $form, $entry ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $order;
		}

		$discount_field = GFAPI::get_field( $form, $this->_args['discount_field_id'] );
		$value          = rgar( $entry, $this->_args['amount_field_id'] );

		if ( strpos( $value, '|' ) !== false ) {
			$value = explode( '|', $value )[0];
		}

		$discount_field->discountAmount = $value;

		return $order;
	}

	public function load_form_script( $form, $is_ajax_enabled ) {

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

				window.GPECFSetDiscountByFieldValue = function( args ) {

					var self = this;

					// copy all args to current object: (list expected props)
					for( var prop in args ) {
						if( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[ prop ];
						}
					}

					self.init = function() {

						var $amountInput = self.$getInput( self.amountFieldId );

						$( '#gform_' + self.formId ).on( 'change', '#' + $amountInput.attr( 'id' ), function() {
							self.setDiscountAmount( $( this ).val() );
						} );

						self.setDiscountAmount( $amountInput.val() );

					};

					self.setDiscountAmount = function( value ) {
						if ( value.indexOf( '|' ) !== -1 ) {
							value = value.split( '|' )[0];
						}

						self.$getInput( self.discountFieldId )
							.data( 'amount', value )
							.val( value )
							.trigger('change');

						window.gformCalculateTotalPrice(self.formId);

						// Trigger recalculations for formulas
						$( document ).trigger( 'gform_pre_conditional_logic', [ self.formId] );
					}

					self.$getInput = function( fieldId ) {
						return $( '#input_' + self.formId + '_' + fieldId );
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
			'formId'          => $this->_args['form_id'],
			'discountFieldId' => $this->_args['discount_field_id'],
			'amountFieldId'   => $this->_args['amount_field_id'],
		);

		$script = 'new GPECFSetDiscountByFieldValue( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gpecf_set_discount_by_field_value', $this->_args['form_id'], $this->_args['discount_field_id'], $this->_args['amount_field_id'] ) );

		GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id == (int) $this->_args['form_id'];
	}

}

# Configuration

new GPECF_Discount_Amounts_By_Field_Value( array(
	'form_id'           => 123,
	'discount_field_id' => 4,
	'amount_field_id'   => 5,
) );
