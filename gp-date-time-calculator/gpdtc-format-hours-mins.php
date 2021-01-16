<?php
/**
 * Gravity Perks // Date Time Calculator // Display Time Difference in Hours and Minutes
 *
 * Plugin Name:  GPDTC Format Results in Hours & Minutes
 * Plugin URI:   http://gravitywiz.com/documentation/gravity-forms-date-time-calculator/
 * Description:  Display calculated time differences in hours & minutes (e.g. 3 hours, 45 minutes).
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com
 */
class GPDTC_Format_Hours_Mins {

	public function __construct() {

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// time for hooks
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );

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

				window.GPDTCFormatHoursMins = function( args ) {

					var self = this;

					// copy all args to current object: (list expected props)
					for( var prop in args ) {
						if( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[ prop ];
						}
					}

					self.init = function() {

						if ( window.gpdtcFormatHoursMinsInitialized ) {
							return;
						}

						gform.addFilter( 'gform_calculation_result', function( result, formulaField, formId, calcObj ) {
							if( self.isApplicableField( formId, formulaField.field_id ) ) {
								var hours = Math.floor( result );
								var diff = hours - result;
								var mins = Math.round( Math.abs( diff * 60 ) );
								result = '{0} hours, {1} minutes'.format( hours, mins );
							}
							return result;
						} );

						gform.addFilter( 'gform_calculation_format_result', function( formattedResult, result, formulaField, formId, calcObj ) {
							if( self.isApplicableField( formId, formulaField.field_id ) ) {
								formattedResult = result;
							}
							return formattedResult;
						} );

						window.gpdtcFormatHoursMinsInitialized = true;

					};

					self.getField = function( formId, fieldId ) {
						return $( '#input_' + formId + '_' + fieldId ).parents( '.gfield' );
					}

					self.isApplicableField = function( formId, fieldId ) {
						var $field = self.getField( formId, fieldId )
						return $field.hasClass( 'gpdtc-format-hours-mins' );
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

		foreach ( $form['fields'] as $field ) {
			if ( $this->is_applicable_field( $field ) ) {
				$script = 'new GPDTCFormatHoursMins();';
				$slug   = implode( '_', array( 'gpdtc_format_hours_mins', $form['id'] ) );
				GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );
				break;
			}
		}

	}

	public function format_result( $result, $formula, $field, $form, $entry ) {

		if ( ! $this->is_applicable_field( $field ) ) {
			return $result;
		}

		$hours = intval( $result );
		$diff  = $result - $hours;
		$mins  = round( $diff * 60 );

		return sprintf( '%d hours, %d minutes', $hours, $mins );
	}

	public function is_applicable_form( $form ) {
		foreach ( $form['fields'] as $field ) {
			if ( $this->is_applicable_field( $field ) ) {
				return true;
			}
		}
		return false;
	}

	public function is_applicable_field( $field ) {
		return strpos( $field->cssClass, 'gpdtc-format-hours-mins' ) !== false;
	}

}

# Configuration

new GPDTC_Format_Hours_Mins();
