<?php
/**
 * Gravity Wiz // Gravity Forms // Dynamic Range
 * http://gravitywiz.com/
 *
 * Set a Number field's minimum and/or maximum range by the value entered into another field.
 *
 * @todo
 *  - Prevent min field's value from exceeding max's.
 *  - Honor default range values if field value is empty.
 *  - Honor default range values in range instructions.
 *
 * Plugin Name:  Gravity Forms Dynamic Range
 * Plugin URI:   http://gravitywiz.com/
 * Description:  Set a Number field's minimum and maximum range by the values entered in to other fields.
 * Author:       Gravity Wiz
 * Version:      0.2
 * Author URI:   http://gravitywiz.com
 */
class GW_Dynamic_Range {

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'      => false,
			'field_id'     => false,
			'min_field_id' => false,
			'max_field_id' => false,
			'enforce_live' => true,
		) );

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_pre_render', array( $this, 'set_dynamic_range' ), 10, 3 );
		add_filter( 'gform_pre_process', array( $this, 'set_dynamic_range' ), 10, 3 );

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );

	}

	public function set_dynamic_range( $form, $ajax, $field_values ) {

		foreach ( $form['fields'] as &$field ) {

			if ( $field->id != $this->_args['field_id'] ) {
				continue;
			}

			if ( $this->_args['min_field_id'] ) {
				$min = $this->get_range_value( $this->_args['min_field_id'], $form, $field_values );
				if ( ! rgblank( $min ) ) {
					$field->rangeMin = $this->get_range_value( $this->_args['min_field_id'], $form, $field_values );
				}
			}

			if ( $this->_args['max_field_id'] ) {
				$max = $this->get_range_value( $this->_args['max_field_id'], $form, $field_values );
				if ( ! rgblank( $max ) ) {
					$field->rangeMax = $this->get_range_value( $this->_args['max_field_id'], $form, $field_values );
				}
			}
		}

		return $form;
	}

	public function get_range_value( $source_field_id, $form, $field_values ) {

		$min_field = GFAPI::get_field( $form, $source_field_id );
		$value     = GFFormsModel::get_field_value( $min_field, $field_values );

		return $value;
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

				window.GWDynamicRange = function( args ) {

					var self = this;

					// copy all args to current object: formId, fieldId, minFieldId, maxFieldId
					for( var prop in args ) {
						if( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[ prop ];
						}
					}

					self.init = function() {

						self.$target = $( '#input_{0}_{1}'.format( self.formId, self.fieldId ) );

						if ( self.minFieldId ) {
							self.$min = $( '#input_{0}_{1}'.format( self.formId, self.minFieldId ) );
							self.$min.on( 'change', function() {
								self.setDynamicRange();
							} );
						}

						if ( self.maxFieldId ) {
							self.$max = $( '#input_{0}_{1}'.format( self.formId, self.maxFieldId ) );
							self.$max.on( 'change', function() {
								self.setDynamicRange();
							} );
						}

						self.$target.on( 'change', function() {
							self.setDynamicRange();
						} );

						self.setDynamicRange();

					};

					self.setMin = function() {
						let min = self.getMinValue();
						self.$target.attr( 'min', min );
						return min;
					}

					self.setMax = function() {
						let max = self.getMaxValue();
						self.$target.attr( 'max', max );
						return max;
					}

					self.getMinValue = function() {
						if ( ! self.minFieldId ) {
							return null;
						}
						let min = parseInt( self.$min.val() );
						return isNaN( min ) ? 0 : min;
					}

					self.getMaxValue = function() {
						if ( ! self.maxFieldId ) {
							return null;
						}
						let max = parseInt( self.$max.val() );
						return isNaN( max ) ? 0 : max;
					}

					self.setDynamicRange = function() {

						let min;
						let max;

						if ( self.minFieldId ) {
							min = self.setMin();
						}

						if ( self.maxFieldId ) {
							max = self.setMax();
						}

						self.updateRangeInstructions( min, max );

						if ( self.enforceLive ) {
							self.enforceRange( min, max );
						}

					}

					self.updateRangeInstructions = function( min, max ) {

						let message;

						if ( min && max ) {
							message = self.messages.both
								.replace( '%1$s', '{0}' )
								.replace( '%2$s', '{1}' )
								.format( '<strong>' + min + '</strong>', '<strong>' + max + '</strong>' );
						} else if ( min ) {
							message = self.messages.min
								.replace( '%s', '{0}' )
								.format( '<strong>' + min + '</strong>' );
						} else if ( max ) {
							message = self.messages.max
								.replace( '%s', '{0}' )
								.format( '<strong>' + max + '</strong>' );
						}

						let $instruct = $( '#gfield_instruction_{0}_{1}'.format( self.formId, self.fieldId ) );
						if ( ! $instruct.length ) {
							$instruct = $( '#validation_message_{0}_{1}'.format( self.formId, self.fieldId ) );
						}
						if ( ! $instruct.length && message ) {
							$instruct = $( '<div class="gfield_description instruction" id="gfield_instruction_{0}_{1}"></div>'.format( self.formId, self.fieldId ) );
							self.$target.after( $instruct );
						}

						$instruct.html( message );

					}

					self.enforceRange = function( min, max ) {

						let currentValue = self.$target.val();
						if ( currentValue === '' ) {
							return;
						}

						currentValue = parseInt( currentValue );

						let value = currentValue;

						if ( min !== undefined ) {
							value = Math.max( value, min );
						}

						if ( max !== undefined ) {
							value = Math.min( value, max );
						}

						if ( value !== currentValue ) {
							self.$target.val( value ).change();
						}

					}

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
			'formId'      => $this->_args['form_id'],
			'fieldId'     => $this->_args['field_id'],
			'minFieldId'  => $this->_args['min_field_id'],
			'maxFieldId'  => $this->_args['max_field_id'],
			'enforceLive' => $this->_args['enforce_live'],
			'messages'    => array(
				// translators: placeholders are numbers
				'both' => esc_html__( 'Please enter a number from %1$s to %2$s.', 'gravityforms' ),
				// translators: placeholder is a number
				'min'  => esc_html__( 'Please enter a number greater than or equal to %s.', 'gravityforms' ),
				// translators: placeholder is a number
				'max'  => esc_html__( 'Please enter a number less than or equal to %s.', 'gravityforms' ),
			),
		);

		$script = 'new GWDynamicRange( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gw_dynamic_range', $this->_args['form_id'], $this->_args['field_id'] ) );

		GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id == (int) $this->_args['form_id'];
	}

}

# Configuration

new GW_Dynamic_Range( array(
	'form_id'      => 123,
	'field_id'     => 4,
	'min_field_id' => 5,
	'max_field_id' => 6,
) );
