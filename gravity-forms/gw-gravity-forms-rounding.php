<?php
/**
 * Gravity Wiz // Gravity Forms // Rounding by Increment
 *
 * Provides a variety of rounding functions for Gravity Form Number fields powered by the CSS class setting for each field. Functions include:
 *
 *  + rounding to an increment            (i.e. increment of 100 would round 1 to 100, 149 to 100, 150 to 200, etc) | class: 'gw-round-100'
 *  + rounding up by an increment         (i.e. increment of 50 would round 1 to 50, 51 to 100, 149 to 150, etc)    | class: 'gw-round-up-50'
 *  + rounding down by an increment       (i.e. increment of 25 would round 1 to 0, 26 to 25, 51 to 50, etc)        | class: 'gw-round-down-25'
 *  + rounding up to a specific minimum   (i.e. min of 50 would round 1 to 50, 51 and greater would not be rounded) | class: 'gw-round-min-50'
 *  + rounding down to a specific maximum (i.e. max of 25 would round 26 to 25, 25 and below would not be rounded)  | class: 'gw-round-max-25'
 *
 * @version 1.13
 * @author  David Smith <david@gravitywiz.com>
 * @license GPL-2.0+
 * @link    http://gravitywiz.com/rounding-increments-gravity-forms/
 *
 * Plugin Name:  Gravity Forms Rounding
 * Plugin URI:   http://gravitywiz.com/rounding-increments-gravity-forms/
 * Description:  Round your field values (including calculations) up, down, by an increment, or to a specific minimum or maximum value.
 * Author:       Gravity Wiz
 * Version:      1.13
 * Author URI:   http://gravitywiz.com
 */
class GW_Rounding {

	private static $instance = null;

	protected static $is_script_output = false;

	protected $class_regex = 'gw-round-([\w|0-9.]+)-?([\w|0-9.]+)?';

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private function __construct( $args = array() ) {

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		// time for hooks
		add_filter( 'gform_pre_render', array( $this, 'prepare_form_and_load_script' ), 10, 2 );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ) );
		add_filter( 'gform_enqueue_scripts', array( $this, 'enqueue_form_scripts' ) );

		add_action( 'gform_pre_submission', array( $this, 'override_submitted_value' ), 10, 5 );
		add_filter( 'gform_calculation_result', array( $this, 'override_submitted_calculation_value' ), 10, 5 );

	}

	public function prepare_form_and_load_script( $form, $is_ajax_enabled ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		if ( $this->is_applicable_form( $form ) && ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
			add_action( 'admin_footer', array( $this, 'output_script' ) );
		}

		foreach ( $form['fields'] as &$field ) {
			if ( preg_match( $this->get_class_regex(), $field['cssClass'] ) ) {
				$field['cssClass'] .= ' gw-rounding';
			}
		}

		return $form;
	}

	public function is_ajax_submission( $form_id, $is_ajax_enabled ) {
		return class_exists( 'GFFormDisplay' ) && isset( GFFormDisplay::$submission[ $form_id ] ) && $is_ajax_enabled;
	}

	function output_script() {
		?>

		<script type="text/javascript">

			var GWRounding;

			( function( $ ) {

				GWRounding = function( args ) {

					var self = this;

					// copy all args to current object: (list expected props)
					for( prop in args ) {
						if( args.hasOwnProperty( prop ) )
							self[prop] = args[prop];
					}

					self.init = function() {

						self.fieldElems = $( '#gform_wrapper_' + self.formId + ' .gw-rounding' );

						self.parseElemActions( self.fieldElems );

						self.bindEvents();

					};

					self.parseElemActions = function( elems ) {

						elems.each( function() {

							var cssClasses      = $( this ).attr( 'class' ),
								roundingActions = self.parseActions( cssClasses );

							$( this ).data( 'gw-rounding', roundingActions );

						} );

					};

					self.parseActions = function( str ) {

						var matches         = getMatchGroups( String( str ), new RegExp( self.classRegex.replace( /\\/g, '\\' ), 'i' ) ),
							roundingActions = [];
						for( var i = 0; i < matches.length; i++ ) {
							var action      = matches[i][1],
								actionValue = matches[i][2];

							if( typeof actionValue == 'undefined' && ! isNaN( parseFloat( action ) ) ) {
								actionValue = action;
								action = 'round';
							}

							var roundingAction = {
								'action':      action,
								'actionValue': actionValue
							};

							roundingActions.push( roundingAction );

						}

						return roundingActions;
					};

					self.bindEvents = function() {

						self.fieldElems.each( function() {

							var $targets;

							// Attempt to target only the quantity input of a Single Product field.
							if( $( this ).hasClass( 'gfield_price' ) ) {
								$targets = $( this ).find( '.ginput_quantity' );
							}

							// Otherwise, target all inputs of the given field.
							if( ! $targets || ! $targets.length ) {
								$targets = $( this ).find( 'input' );
							}

							$targets.each( function() {
								self.applyRoundingActions( $( this ) );
							} ).blur( function() {
								self.applyRoundingActions( $( this ) );
							} );

						} );

						gform.addFilter( 'gform_calculation_result', function( result, formulaField, formId, calcObj ) {

							var $input = $( '#input_' + formId + '_' + formulaField.field_id )
							$field = $input.parents( '.gfield' );

							if( $field.hasClass( 'gw-rounding' ) ) {
								result = self.getRoundedValue( $input, result );
							}

							return result;
						} );

					};

					self.applyRoundingActions = function( $input ) {
						var value = self.getRoundedValue( $input );
						if( $input.val() != value ) {
							$input.val( value ).change();
						}
					};

					self.getRoundedValue = function( $input, value ) {

						var $field  = $input.parents( '.gfield' ),
							actions = $field.data( 'gw-rounding' );

						// allows setting the 'gw-rounding' data for an element to null and it will be reparsed
						if( actions === null ) {
							self.parseElemActions( $field );
							actions = $field.data( 'gw-rounding' );
						}

						if( typeof actions == 'undefined' || actions === false || actions.length <= 0 ) {
							return;
						}

						if( typeof value == 'undefined' ) {

							value = gformToNumber( $input.val() );

							var currency = new Currency(gf_global.gf_currency_config);
							value = gformCleanNumber( value, currency.currency.symbol_right, currency.currency.symbol_left, currency.currency.decimal_separator );

						}

						if( value != '' ) {
							for( var i = 0; i < actions.length; i++ ) {
								value = GWRounding.round( value, actions[i].actionValue, actions[i].action );
							}
						}

						return isNaN( value ) ? '' : value;
					};

					GWRounding.round = function( value, actionValue, action ) {
						var interval, base, min, max;
						value = parseFloat( value );
						actionValue = parseFloat( actionValue );
						switch( action ) {
							case 'min':
								min = actionValue;
								if( value < min ) {
									value = min;
								}
								break;
							case 'max':
								max = actionValue;
								if( value > max ) {
									value = max;
								}
								break;
							case 'up':
								interval = actionValue;
								base     = Math.ceil( value / interval );
								value    = base * interval;
								break;
							case 'down':
								interval = actionValue;
								base     = Math.floor( value / interval );
								value    = base * interval;
								break;
							case 'round':
								interval = actionValue;
								base = Math.round(value / interval);
								value = base * interval;
								break;
							default:
								/**
								 * Custom rounding filter
								 *
								 * Use this filter to implement your own rounding method. The filter's name is based on
								 * the CSS class set on the field.
								 *
								 * Example:
								 * CSS Class: gw-round-mycustomroundroundingfunc-10
								 * Filter: gw_round_mycustomroundingfunc
								 *
								 * @param int value       Current input value to be rounded
								 * @param int actionValue Custom value passed in CSS class name (e.g. gw-round-custom-10, actionValue = 10)
								 */
								value = window.gform.applyFilters( 'gw_round_{0}'.format(action), value, actionValue );
								break;
						}

						return parseFloat( value );
					};

					self.init();

				}

			} )( jQuery );

		</script>

		<?php
	}

	function add_init_script( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$args   = array(
			'formId'     => $form['id'],
			'classRegex' => $this->class_regex,
		);
		$script = 'new GWRounding( ' . json_encode( $args ) . ' );';

		GFFormDisplay::add_init_script( $form['id'], 'gw_rounding', GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	function enqueue_form_scripts( $form ) {

		if ( $this->is_applicable_form( $form ) ) {
			wp_enqueue_script( 'gform_gravityforms' );
		}

	}

	function override_submitted_value( $form ) {

		foreach ( $form['fields'] as $field ) {
			if ( $this->is_applicable_field( $field ) && ! is_a( $field, 'GF_Field_Calculation' ) ) {

				$value = rgpost( "input_{$field['id']}" );

				$is_currency = $field->get_input_type() == 'number' && $field->numberFormat == 'currency';
				if ( $is_currency ) {
					$value = GFCommon::to_number( $value );
				}

				$value = $this->process_rounding_actions( $value, $this->get_rounding_actions( $field ) );

				$_POST[ "input_{$field['id']}" ] = $value;

			}
		}

	}

	function override_submitted_calculation_value( $result, $formula, $field, $form, $entry ) {

		if ( $this->is_applicable_field( $field ) ) {
			$result = $this->process_rounding_actions( $result, $this->get_rounding_actions( $field ) );
		}

		return $result;
	}

	function process_rounding_actions( $value, $actions ) {

		foreach ( $actions as $action ) {
			$value = $this->round( $value, $action['action'], $action['action_value'] );
		}

		return $value;
	}

	function round( $value, $action, $action_value ) {

		$value        = floatval( $value );
		$action_value = floatval( $action_value );

		switch ( $action ) {
			case 'min':
				$min = $action_value;
				if ( $value < $min ) {
					$value = $min;
				}
				break;
			case 'max':
				$max = $action_value;
				if ( $value > $max ) {
					$value = $max;
				}
				break;
			case 'up':
				$interval = $action_value;
				$base     = ceil( $value / $interval );
				$value    = $base * $interval;
				break;
			case 'down':
				$interval = $action_value;
				$base     = floor( $value / $interval );
				$value    = $base * $interval;
				break;
			case 'round':
				$interval = $action_value;
				$base     = round( $value / $interval );
				$value    = $base * $interval;
				break;
			default:
				/**
				 * Custom rounding filter
				 *
				 * Use this filter to implement your own rounding method. The filter's name is based on
				 * the CSS class set on the field.
				 *
				 * Example:
				 * CSS Class: gw-round-mycustomroundroundingfunc-10
				 * Filter: gw_round_mycustomroundingfunc
				 *
				 * @param int $value       Current input value to be rounded
				 * @param int $actionValue Custom value passed in CSS class name (e.g. gw-round-custom-10, actionValue = 10)
				 */
				$value = apply_filters( sprintf( 'gw_round_%s', $action ), $value, $action_value );
				break;
		}

		return floatval( $value );
	}

	// # HELPERS

	function is_applicable_form( $form ) {

		foreach ( $form['fields'] as $field ) {
			if ( $this->is_applicable_field( $field ) ) {
				return true;
			}
		}

		return false;
	}

	function is_applicable_field( $field ) {
		return preg_match( $this->get_class_regex(), rgar( $field, 'cssClass' ) ) == true;
	}

	function get_class_regex() {
		return "/{$this->class_regex}/";
	}

	function get_rounding_actions( $field ) {

		$actions = array();

		preg_match_all( $this->get_class_regex(), rgar( $field, 'cssClass' ), $matches, PREG_SET_ORDER );

		foreach ( $matches as $match ) {

			list( $full_match, $action, $action_value ) = array_pad( $match, 3, false );

			if ( $action_value === false && is_numeric( $action ) ) {
				$action_value = $action;
				$action       = 'round';
			}

			$action = array(
				'action'       => $action,
				'action_value' => $action_value,
			);

			$actions[] = $action;

		}

		return $actions;
	}

}

function gw_rounding() {
	return GW_Rounding::get_instance();
}

gw_rounding();
