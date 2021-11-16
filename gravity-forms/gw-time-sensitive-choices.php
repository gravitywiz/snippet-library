<?php
/**
 * Gravity Wiz // Gravity Forms // Time Sensitive Choices
 * https://gravitywiz.com/
 *
 * Manually specify available time slots as choices and this snippet will automatically disable choices that are before
 * the current time. Link this with a Date field to only filter by time when the current date is selected.
 *
 * Current time is based on the configured WordPress timezone.
 *
 * Works well with [GP Limit Dates](https://gravitywiz.com/documentation/gravity-forms-limit-dates/).
 *
 * ## Known Limitations
 *
 * 1. Date field integration only works with Datepicker Date fields.
 * 2. Server side validation has not been implemented. A malicious user could submit a choice that is before the current time.
 *
 * Plugin Name: Gravity Forms Time Sensitive Choices
 * Plugin URI:  https://gravitywiz.com
 * Description: Filter time-based choices based on the current time.
 * Author:      David Smith
 * Version:     1.2
 * Author URI:  https://gravitywiz.com
 */
class GW_Time_Sensitive_Choices {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'       => false,
			'field_id'      => false,
			'time_mod'      => false,
			'date_field_id' => false,
			'buffer'        => 0,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

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

				window.GWTimeSensitiveChoices = function( args ) {

					var self = this;

					for( var prop in args ) {
						if( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[ prop ];
						}
					}

					self.init = function() {

						self.$target = $( '#input_{0}_{1}'.format( self.formId, self.fieldId ) );

						self.bindEvents();
						self.initializeChoices();

						if ( self.dateFieldId ) {
							self.$date = $( '#input_{0}_{1}'.format( self.formId, self.dateFieldId ) );
							self.$date.on( 'change', function() {
								var selectedDate = self.$date.datepicker( 'getDate' );
								var currentDate  = self.getCurrentServerTime();
								// Is future date?
								if ( selectedDate > currentDate && selectedDate.getDate() > currentDate.getDate() ) {
									self.enableChoices();
								} else if ( selectedDate < currentDate && selectedDate.getDate() < currentDate.getDate() ) {
									self.disableChoices();
								} else {
									self.evaluateChoices();
								}

							} );
						}

					};

					self.bindEvents = function() {
						gform.addAction( 'gpi_field_refreshed', function( $targetField, $triggerField, initialLoad ) {
							if ( gf_get_input_id_by_html_id( self.$target.attr( 'id' ) ) == gf_get_input_id_by_html_id( $targetField.attr( 'id' ) ) ) {
								self.$target = $targetField;
								self.initializeChoices();
							}
						} );
					}

					self.evaluateChoices = function( mode ) {

						var isDisabled;
						var currentTime = self.getCurrentServerTime();

						self.$target.find( 'option' ).each( function() {
							switch ( mode ) {
								case 'enable':
									isDisabled = false;
									break;
								case 'disable':
									isDisabled = true;
									break;
								default:
									isDisabled = this.value && self.getChoiceTime( this.value ) < currentTime;
							}
							// This addresses placeholders specifically... not sure what other exceptions we'll need to make.
							if ( this.value == '' ) {
								isDisabled = false;
							}
							// If choice was loaded from PHP disabled, always honor that. For example, GPI will load the
							// choice as disabled if its inventory is exhausted.
							if ( $( this ).data( 'gwtsc-disabled' ) ) {
								isDisabled = true;
							}
							$( this ).prop( 'disabled', isDisabled );
						} );

					}

					self.enableChoices = function() {
						self.evaluateChoices( 'enable' );
					}

					self.disableChoices = function() {
						self.evaluateChoices( 'disable' );
					}

					self.initializeChoices = function() {
						self.$target.find( 'option' ).each( function() {
							if ( $( this ).prop( 'disabled' ) ) {
								$( this ).data( 'gwtsc-disabled', true );
							}
						} );
						self.evaluateChoices();
					}

					self.getChoiceTime = function( choiceTime ) {
						var date = self.parseTime( choiceTime );
						date.setMinutes( date.getMinutes() - self.buffer );
						return date;
					}

					/**
					 * @see https://stackoverflow.com/a/338439/227711
					 * @param timeString
					 * @returns {null|Date}
					 */
					self.parseTime = function( timeString ) {

						if ( timeString == '' ) {
							return null;
						}

						var date = new Date();
						var time = timeString.match( /(\d+)(:(\d\d))?\s*(p?)/i );

						date.setHours( parseInt( time[1], 10 ) + ( ( parseInt( time[1], 10 ) < 12 && time[4] ) ? 12 : 0 ) );
						date.setMinutes( parseInt( time[3], 10 ) || 0 );
						date.setSeconds( 0, 0 );

						return date;
					}

					self.getCurrentServerTime = function() {
						var date = new Date();
						return self.convertTimezone( date, self.serverTimezone );
					}

					/**
					 * @see https://stackoverflow.com/a/338439/227711
					 * @param date
					 * @param tzString
					 * @returns {Date}
					 */
					self.convertTimezone = function( date, tzString ) {
						return new Date( ( typeof date === 'string' ? new Date( date ) : date ).toLocaleString( 'en-US', { timeZone: tzString } ) );
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
			'formId'         => $this->_args['form_id'],
			'fieldId'        => $this->_args['field_id'],
			'dateFieldId'    => $this->_args['date_field_id'],
			'serverTimezone' => get_option( 'timezone_string' ),
			'buffer'         => $this->_args['buffer'],
		);

		$script = 'new GWTimeSensitiveChoices( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gw_time_sensitive_choices', $this->_args['form_id'], $this->_args['field_id'] ) );

		GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id == (int) $this->_args['form_id'];
	}

	public function is_applicable_field( $field ) {
		return in_array( $field->id, $this->_args['field_ids'] );
	}

}

new GW_Time_Sensitive_Choices( array(
	'form_id'       => 123,
	'field_id'      => 4,
	'date_field_id' => 5,
	'buffer'        => 60,
) );