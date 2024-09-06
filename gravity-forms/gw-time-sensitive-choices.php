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
 * Version:     1.4
 * Author URI:  https://gravitywiz.com
 */
class GW_Time_Sensitive_Choices {

	private $_args = array();

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'                  => false,
			'field_id'                 => false,
			'date_field_id'            => false,
			'buffer'                   => 0,
			'remove_choices'           => false,
			'no_times_available_label' => '&ndash; No times available &ndash;', // Only used if remove_choices is set to true
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

						self.$target = $( '#input_{0}_{1}'.gformFormat( self.formId, self.fieldId ) );
						if ( self.dateFieldId ) {
							self.$date = $( '#input_{0}_{1}'.gformFormat( self.formId, self.dateFieldId ) );
							self.bindEvents();
							setTimeout( function() {
								self.initializeChoices();
							} );
						} else {
							self.bindEvents();
							self.initializeChoices();
						}

					};

					self.bindEvents = function() {

						gform.addAction( 'gpi_field_refreshed', function( $targetField, $triggerField, initialLoad ) {
							if ( gf_get_input_id_by_html_id( self.$target.attr( 'id' ) ) == gf_get_input_id_by_html_id( $targetField.attr( 'id' ) ) ) {
								self.$target = $targetField.find( '#input_{0}_{1}'.gformFormat( self.formId, self.fieldId ) );
								self.initializeChoices();
							}
						} );

						$( document ).on( 'gppa_updated_batch_fields', function ( event, formId, updatedFieldIds ) {
							if ( updatedFieldIds.indexOf( gf_get_input_id_by_html_id( self.$target.attr( 'id' ) ) ) !== - 1 ) {
								self.$target = $( '#input_{0}_{1}'.gformFormat( self.formId, self.fieldId ) );
								self.initializeChoices();
							}
						} );

						if ( self.$date.length ) {
							self.$date.on( 'change', function() {
								self.evaluateChoices();
							} );
						}

					}

					self.evaluateChoices = function() {

						var isDisabled;
						var mode;
						var currentTime = self.getCurrentServerTime();

						if ( self.dateFieldId ) {
							var selectedDate = self.$date.datepicker( 'getDate' );
							if ( selectedDate !== null ) {
								var currentDate = self.getCurrentServerTime();
								currentDate.setHours(0, 0, 0, 0);

								selectedDate = new Date(selectedDate.getTime());
								selectedDate.setHours(0, 0, 0, 0);

								// Is future date?
								if ( selectedDate > currentDate ) {
									mode = 'enable';
								}
								// Is past date?
								else if ( selectedDate < currentDate ) {
									mode = 'disable';
								}
							}
						}

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
							$( this ).attr( 'hidden', false );

							if ( self.removeChoices && isDisabled ) {
								$( this ).attr( 'hidden', true );
							}
						} );

						if ( self.$target.find( 'option:not([hidden])' ).length === 0 ) {
							self.$target.append( '<option value="" disabled selected class="gwtsc-no-times-available"><?php echo $this->_args['no_times_available_label']; ?></option>' );
						} else {
							self.$target.find( '.gwtsc-no-times-available' ).remove();
						}

						/* Force selection of first time available */
						self.$target.val( self.$target.find( 'option:not([hidden])' ).first().val() );
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
						// Ensure that times are always checked for the selected date. Without this, times will be based
						// on the user's current date. This is only relevant when the user is in a different timezone
						// than the server.
						if ( self.dateFieldId ) {
							var selectedDate = self.getSelectedDate();
							if ( selectedDate ) {
								var isMidnight = date.getDate() === selectedDate.getDate() + 1 && date.getHours() === 0;
								// We're making an assumption here that if people will want midnight to be a future time
								// and not midnight from the morning of the current date.
								if ( ! isMidnight ) {
									date.setDate( selectedDate.getDate() );
								}
							}
						}
						date.setMinutes( date.getMinutes() - self.buffer );
						return date;
					}

					self.getSelectedDate = function() {
						return self.$date.datepicker( 'getDate' );
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

					/**
					 * @returns Date
					 */
					self.getCurrentServerTime = function() {
						var date = new Date();
						return self.convertTimezone( date );
					}

					self.convertTimezone = function( date ) {
						if ( $.isNumeric( self.serverTimezone ) ) {
							// Get the difference between the WP timezone and the user's local time in minutes.
							var localDiff = date.getTimezoneOffset() + ( self.serverTimezone * 60 );
							if ( localDiff ) {
								date.setMinutes( localDiff );
							}
						} else {
							date = new Date( ( typeof date === 'string' ? new Date( date ) : date ).toLocaleString( 'en-US', { timeZone: self.serverTimezone } ) );
						}
						return date;
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
			'serverTimezone' => get_option( 'timezone_string' ) ? get_option( 'timezone_string' ) : get_option( 'gmt_offset' ),
			'buffer'         => $this->_args['buffer'],
			'removeChoices'  => $this->_args['remove_choices'],
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
	'form_id'        => 123,
	'field_id'       => 4,
	'date_field_id'  => 5,
	'buffer'         => 60,
	'remove_choices' => false,
) );
