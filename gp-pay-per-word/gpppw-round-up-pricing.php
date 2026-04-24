<?php
/**
 * Gravity Wiz // Pay Per Word // Round Up Price
 * https://gravitywiz.com/documentation/gravity-forms-pay-per-word/
 *
 * Rounds up the Pay Per Word calculated price to the nearest whole dollar amount.
 *
 * Plugin Name:  GP Pay Per Word - Round Up Price
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-pay-per-word/
 * Description:  Rounds up the Pay Per Word calculated price to the nearest whole dollar amount.
 * Version:      0.1
 * Author URI:   http://gravitywiz.com/
 */
class GW_Pay_Per_Word_Round_Up_Price {

	private $_args = array();

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
		) );

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// Hook into the Pay Per Word price filter
		add_filter( 'gwppw_price', array( $this, 'round_up_price' ), 10, 4 );

		// Add JavaScript for frontend price rounding
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_action( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );

	}

	/**
	 * Round up the calculated price to the nearest whole dollar
	 *
	 * @param float $price The calculated price
	 * @param object $price_field The price field object
	 * @param object $word_field The word field object
	 * @param int $word_count The calculated word count
	 * @return float The rounded up price
	 */
	public function round_up_price( $price, $price_field, $word_field, $word_count ) {

		if ( ! $this->is_applicable_form( $price_field->formId ) ) {
			return $price;
		}

		if ( ! $this->is_applicable_field( $price_field ) ) {
			return $price;
		}

		return ceil( $price );
	}

	public function load_form_script( $form, $is_ajax_enabled ) {

		if ( $this->is_applicable_form( $form ) && $this->has_pay_per_word_field( $form ) && ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	public function output_script() {
		?>
		<script type="text/javascript">

			( function( $ ) {

				window.GWPayPerWordRoundUpPrice = function( args ) {

					var self = this;

					for( var prop in args ) {
						if( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[ prop ];
						}
					}

					self.isApplicableField = function( fieldId ) {
						if ( typeof fieldId !== 'number' ) {
							fieldId = parseInt( fieldId );
						}

						if ( ! self.fieldId ) {
							return true;
						}

						if ( typeof self.fieldId !== 'object' ) {
							self.fieldId = [ self.fieldId ];
						}

						self.fieldId = self.fieldId.map( function( fieldId ) {
							if ( typeof fieldId === 'string' ) {
								fieldId = parseInt( fieldId );
							}
							return fieldId;
						} );

						return self.fieldId.indexOf( fieldId ) !== -1;
					};

					self.init = function() {

						gform.addFilter( 'gwppw_price', function( price, wordCount, pricePerWord, ppwField, formId ) {
							
							if ( self.formId && parseInt( formId ) !== parseInt( self.formId ) ) {
								return price;
							}

							if ( ! self.isApplicableField( ppwField.price_field ) ) {
								return price;
							}

							return Math.ceil( price );
						} );

						gform.addFilter( 'gpppw_price', function( price, wordCount, pricePerWord, ppwField, formId ) {
							
							if ( self.formId && parseInt( formId ) !== parseInt( self.formId ) ) {
								return price;
							}

							if ( ! self.isApplicableField( ppwField.price_field ) ) {
								return price;
							}

							return Math.ceil( price );
						} );

					};

					self.init();

				};

			} )( jQuery );

		</script>
		<?php
	}

	public function add_init_script( $form ) {

		if ( ! $this->is_applicable_form( $form ) || ! $this->has_pay_per_word_field( $form ) ) {
			return;
		}

		$args = array(
			'formId'  => $this->_args['form_id'],
			'fieldId' => $this->_args['field_id'],
		);

		$script = 'new GWPayPerWordRoundUpPrice( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gw_pay_per_word_round_up_price', $this->_args['form_id'], $this->_args['field_id'] ) );

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

	public function is_applicable_field( $field ) {

		$field_id = isset( $field['id'] ) ? $field['id'] : ( isset( $field->id ) ? $field->id : $field );

		return empty( $this->_args['field_id'] ) || (int) $field_id === (int) $this->_args['field_id'];
	}

	public function has_pay_per_word_field( $form ) {

		foreach ( $form['fields'] as $field ) {
			if ( rgar( $field, 'gwpayperword_enable' ) ) {
				return true;
			}
		}

		return false;
	}

}

# Configuration

// Apply to all forms and fields
new GW_Pay_Per_Word_Round_Up_Price();

// Apply to specific form
// new GW_Pay_Per_Word_Round_Up_Price( array(
//     'form_id' => 1,
// ) );

// Apply to specific field on specific form
// new GW_Pay_Per_Word_Round_Up_Price( array(
//     'form_id'  => 1,
//     'field_id' => 2,
// ) );
