<?php
/**
 * Gravity Wiz // Gravity Forms // Populate Date
 *
 * Provides the ability to populate a Date field with a modified date based on the current date or a user-submitted date.
 *
 * @version   2.2
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/populate-dates-gravity-form-fields/
 */
class GW_Populate_Date {

	protected static $is_script_output = false;

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'                => false,
			'target_field_id'        => false,
			'source_field_id'        => false,
			'format'                 => '',
			'modifier'               => false,
			'min_date'               => false,
			'enable_i18n'            => false,
			'override_on_submission' => false,
		) );

		$this->_field_values = array();

		if ( ! $this->_args['form_id'] || ! $this->_args['target_field_id'] ) {
			return;
		}

		// time for hooks
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		if ( $this->_args['source_field_id'] ) {
			add_action( 'gform_pre_submission', array( $this, 'populate_date_on_pre_submission' ) );
			add_filter( 'gform_pre_render', array( $this, 'load_form_script' ) );
			add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ) );
			add_filter( 'gform_enqueue_scripts', array( $this, 'enqueue_form_scripts' ) );
		} else {
			add_filter( 'gform_pre_render', array( $this, 'populate_date_on_pre_render' ) );
		}

		if ( $this->_args['override_on_submission'] ) {
			add_action( 'gform_pre_submission', array( $this, 'override_on_submission' ) );
        }

	}

	public function enqueue_form_scripts( $form ) {
		if ( $this->is_applicable_form( $form ) ) {
			wp_enqueue_script( 'moment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment.min.js' );
			add_filter( 'gform_noconflict_scripts', function( $scripts ) {
				$scripts[] = 'moment';
				return $scripts;
			} );
		}
	}

	public function populate_date_on_pre_render( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( $field['id'] == $this->_args['target_field_id'] ) {

				$key   = sprintf( 'gwpd_%d_%d', $form['id'], $field['id'] );
				$value = $this->get_modified_date( $field );

				$field['allowsPrepopulate'] = true;
				$field['inputName']         = $key;

				$this->_field_values[ $key ] = $value;

				add_filter( "gform_field_value_{$key}", array( $this, 'set_field_value' ), 10, 3 );

			}
		}

		return $form;
	}

	public function set_field_value( $value, $field, $name ) {
		if ( isset( $this->_field_values[ $name ] ) ) {
			$value = $this->_field_values[ $name ];
		}
		return $value;
	}

	public function populate_date_on_pre_submission( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( $field['id'] == $this->_args['target_field_id'] ) {

				$timestamp = $this->get_source_timestamp( GFFormsModel::get_field( $form, $this->_args['source_field_id'] ) );
				$value     = $this->get_modified_date( $field, $timestamp );

				if ( $value ) {
					$_POST[ "input_{$field['id']}" ] = $value;
				}
			}
		}

	}

	public function override_on_submission( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		foreach ( $form['fields'] as $field ) {
			if ( $field['id'] == $this->_args['target_field_id'] ) {
				$_POST[ "input_{$field['id']}" ] = $this->get_modified_date( $field );
			}
		}

    }

	public function get_source_timestamp( $field ) {

		$raw = rgpost( 'input_' . $field['id'] );
		if ( is_array( $raw ) ) {
			$raw = array_filter( $raw );
		}

		list( $format, $divider ) = $field['dateFormat'] ? array_pad( explode( '_', $field['dateFormat'] ), 2, 'slash' ) : array( 'mdy', 'slash' );
		$dividers                 = array(
			'slash' => '/',
			'dot'   => '.',
			'dash'  => '-',
		);

		if ( empty( $raw ) ) {
			$raw = date( implode( $dividers[ $divider ], str_split( $format ) ) );
		}

		$date = ! is_array( $raw ) ? explode( $dividers[ $divider ], $raw ) : $raw;

		$month = $date[ strpos( $format, 'm' ) ];
		$day   = $date[ strpos( $format, 'd' ) ];
		$year  = $date[ strpos( $format, 'y' ) ];

		$timestamp = mktime( 0, 0, 0, $month, $day, $year );

		return $timestamp;
	}

	public function get_modified_date( $field, $timestamp = false ) {

		if ( ! $timestamp ) {
			$timestamp = current_time( 'timestamp' );
		}

		$target_is_date_field = GFFormsModel::get_input_type( $field ) === 'date';
		// Always respect the passed format; it may conflict with the Date format but this is used at the user's discretion.
		if ( $this->_args['format'] || ! $target_is_date_field ) {
			$format = $this->get_format();
		} else {

			list( $format, $divider ) = $field['dateFormat'] ? array_pad( explode( '_', $field['dateFormat'] ), 2, 'slash' ) : array( 'mdy', 'slash' );
			$dividers                 = array(
				'slash' => '/',
				'dot'   => '.',
				'dash'  => '-',
			);

			$format  = str_replace( 'y', 'Y', $format );
			$divider = $dividers[ $divider ];
			$format  = implode( $divider, str_split( $format ) );

		}

		if ( $this->_args['modifier'] ) {

			$modifier = $this->_args['modifier'];

			if ( is_array( $modifier ) ) {
				$key            = sprintf( 'input_%s', str_replace( '.', '_', $modifier['inputId'] ) );
				$modifier_field = GFAPI::get_field( $field->formId, (int) $modifier['inputId'] );
				$number_format  = $modifier_field->numberFormat ? $modifier_field->numberFormat : 'currency';
				// Default number format to whatever is set in the currency. This might cause issues if a Number field
				// is configured to a different number format; however, this matches how the JS works for now.
				$value    = gf_apply_filters( array( 'gwpd_get_field_value', $this->_args['form_id'], $modifier['inputId'] ), GFCommon::clean_number( rgpost( $key ), $number_format ), $modifier['inputId'] );
				$modifier = $value > 0 ? sprintf( str_replace( '{0}', '%d', $modifier['modifier'] ), $value ) : false;
			}

			if ( $modifier ) {
				$timestamp = strtotime( $modifier, $timestamp );
			}
		}

		if ( $this->_args['min_date'] ) {
			$min_timestamp = strtotime( $this->_args['min_date'] ) ? strtotime( $this->_args['min_date'] ) : $this->_args['min_date'];
			if ( $min_timestamp > $timestamp ) {
				$timestamp = $min_timestamp;
			}
		}

		if ( $this->_args['enable_i18n'] ) {
			$date = strftime( $format, $timestamp );
		} else {
			$date = date( $format, $timestamp );
		}

		return $date;
	}

	public function get_format( $should_convert = null ) {

		if ( $should_convert === null ) {
			$should_convert = $this->_args['enable_i18n'];
		}

		$format = ! $this->_args['format'] ? 'Y-m-d' : $this->_args['format'];
		if ( $should_convert ) {
			$format = $this->date_format_to( $format, 'strf' );
		}

		return $format;
	}

	public function load_form_script( $form ) {

		if ( $this->is_applicable_form( $form ) && ! has_action( 'wp_footer', array( __CLASS__, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( __CLASS__, 'output_script' ), 21 );
			add_action( 'gform_preview_footer', array( __CLASS__, 'output_script' ), 21 );
		}

		return $form;
	}

	public static function output_script() {
		?>

		<script type="text/javascript">

			( function( $ ) {

				window.GWPopulateDate = function( args ) {

					var self = this;

					// copy all args to current object: (list expected props)
					for( prop in args ) {
						if( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[ prop ];
						}
					}

					self.init = function() {

						self.$sourceInputs = GWDates.getFieldInputs( self.sourceFieldId, self.formId );

						self.$sourceInputs.change( function() {
							self.populateDate( self.sourceFieldId, self.targetFieldId, self.getModifier(), self.format );
						} );

						if( typeof self.modifier == 'object' ) {
							self.$modifierInputs = self.getInputs( self.modifier.inputId );
							self.$modifierInputs.change( function() {
								self.populateDate( self.sourceFieldId, self.targetFieldId, self.getModifier(), self.format );
							} );
						}

						$( document ).bind( 'gform_post_conditional_logic', function( event, formId, fields ) {
							if( formId == self.formId ) {
								self.populateDate( self.sourceFieldId, self.targetFieldId, self.getModifier(), self.format );
							}
						} );

						self.populateDate( self.sourceFieldId, self.targetFieldId, self.getModifier(), self.format );

					};

					self.populateDate = function( sourceFieldId, targetFieldId, modifier, format ) {

						var timestamp = GWDates.getFieldTimestamp( sourceFieldId, self.formId );
						if( timestamp === 0 ) {
							return;
						}

						var date    = new Date( timestamp ),
							newDate = modifier ? new Date( GWPopulateDate.strtotime( modifier, date.getTime() / 1000 ) * 1000 ) : date;

						GWDates.populateDate( targetFieldId, self.formId, newDate.getTime(), format );

					};

					self.getModifier = function() {

						if( typeof self.modifier != 'object' ) {
							return self.modifier;
						}

						switch( self.modifier.type ) {
							case 'field':
								var inputId  = self.modifier.inputId,
									value    = self.getFieldValue( inputId ),
									modifier = value > 0 ? self.modifier.modifier.format( value ) : false;
								break;
						}

						return modifier;
					};

					self.getFieldValue = function( inputId ) {

						var $input = self.getInputs( inputId ),
							value  = self.getCleanNumber( $input.val(), gformExtractFieldId( inputId ), self.formId );

						value = gform.applyFilters( 'gwpd_get_field_value', value, $input, inputId );

						return value;
					};

					/**
					 * Gravity Forms gformToNumber() and other number-conversion methods do a great job converting
					 * currency text to raw numbers but they are still in numeric format of the specified currency
					 */
					self.getCleanNumber = function( value, fieldId, formId ) {
						var numberFormat = gf_get_field_number_format( fieldId, formId );
						var decimalSep   = gformGetDecimalSeparator( numberFormat ? numberFormat : 'currency' );
						if ( decimalSep === ',' ) {
							value = value.replace( '.', '' ).replace( ',', '.' );
						} else {
							value = value.replace( ',', '' );
						}
						return parseFloat( value );
					}

					self.getInputs = function( inputId ) {

						var fieldId    = gformExtractFieldId( inputId ),
							inputIndex = gformExtractInputIndex( inputId ),
							id         = inputIndex !== fieldId ? '#input_{0}_{1}' : '#input_{0}_{1}_{2}',
							$input     = $( id.format( self.formId, fieldId, inputIndex ) );

						return $input;
					};

					GWPopulateDate.strtotime = function( text, now ) {
						/*discuss at: http://phpjs.org/functions/strtotime/
							 version: 1109.2016
						original by: Caio Ariede (http://caioariede.com)
						improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
						improved by: Caio Ariede (http://caioariede.com)
						improved by: A. Matías Quezada (http://amatiasq.com)
						improved by: preuter
						improved by: Brett Zamir (http://brett-zamir.me)
						improved by: Mirko Faber
							input by: David
						bugfixed by: Wagner B. Soares
						bugfixed by: Artur Tchernychev
						bugfixed by: Stephan Bösch-Plepelits (http://github.com/plepe)*/
						var parsed, match, today, year, date, days, ranges, len, times, regex, i, fail = false;

						if (!text) {
							return now;
						}

						// Unecessary spaces
						text = text.replace(/^\s+|\s+$/g, '')
							.replace(/\s{2,}/g, ' ')
							.replace(/[\t\r\n]/g, '')
							.toLowerCase();

						// in contrast to php, js Date.parse function interprets:
						// dates given as yyyy-mm-dd as in timezone: UTC,
						// dates with "." or "-" as MDY instead of DMY
						// dates with two-digit years differently
						// etc...etc...
						// ...therefore we manually parse lots of common date formats
						match = text.match(
							/^(\d{1,4})([\-\.\/\:])(\d{1,2})([\-\.\/\:])(\d{1,4})(?:\s(\d{1,2}):(\d{2})?:?(\d{2})?)?(?:\s([A-Z]+)?)?$/);

						if (match && match[2] === match[4]) {
							if (match[1] > 1901) {
								switch (match[2]) {
									case '-':
									{
										// YYYY-M-D
										if (match[3] > 12 || match[5] > 31) {
											return fail;
										}

										return new Date(match[1], parseInt(match[3], 10) - 1, match[5],
												match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
									}
									case '.':
									{
										// YYYY.M.D is not parsed by strtotime()
										return fail;
									}
									case '/':
									{
										// YYYY/M/D
										if (match[3] > 12 || match[5] > 31) {
											return fail;
										}

										return new Date(match[1], parseInt(match[3], 10) - 1, match[5],
												match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
									}
								}
							} else if (match[5] > 1901) {
								switch (match[2]) {
									case '-':
									{
										// D-M-YYYY
										if (match[3] > 12 || match[1] > 31) {
											return fail;
										}

										return new Date(match[5], parseInt(match[3], 10) - 1, match[1],
												match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
									}
									case '.':
									{
										// D.M.YYYY
										if (match[3] > 12 || match[1] > 31) {
											return fail;
										}

										return new Date(match[5], parseInt(match[3], 10) - 1, match[1],
												match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
									}
									case '/':
									{
										// M/D/YYYY
										if (match[1] > 12 || match[3] > 31) {
											return fail;
										}

										return new Date(match[5], parseInt(match[1], 10) - 1, match[3],
												match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
									}
								}
							} else {
								switch (match[2]) {
									case '-':
									{
										// YY-M-D
										if (match[3] > 12 || match[5] > 31 || (match[1] < 70 && match[1] > 38)) {
											return fail;
										}

										year = match[1] >= 0 && match[1] <= 38 ? +match[1] + 2000 : match[1];
										return new Date(year, parseInt(match[3], 10) - 1, match[5],
												match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
									}
									case '.':
									{
										// D.M.YY or H.MM.SS
										if (match[5] >= 70) {
											// D.M.YY
											if (match[3] > 12 || match[1] > 31) {
												return fail;
											}

											return new Date(match[5], parseInt(match[3], 10) - 1, match[1],
													match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
										}
										if (match[5] < 60 && !match[6]) {
											// H.MM.SS
											if (match[1] > 23 || match[3] > 59) {
												return fail;
											}

											today = new Date();
											return new Date(today.getFullYear(), today.getMonth(), today.getDate(),
													match[1] || 0, match[3] || 0, match[5] || 0, match[9] || 0) / 1000;
										}

										// invalid format, cannot be parsed
										return fail;
									}
									case '/':
									{
										// M/D/YY
										if (match[1] > 12 || match[3] > 31 || (match[5] < 70 && match[5] > 38)) {
											return fail;
										}

										year = match[5] >= 0 && match[5] <= 38 ? +match[5] + 2000 : match[5];
										return new Date(year, parseInt(match[1], 10) - 1, match[3],
												match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
									}
									case ':':
									{
										// HH:MM:SS
										if (match[1] > 23 || match[3] > 59 || match[5] > 59) {
											return fail;
										}

										today = new Date();
										return new Date(today.getFullYear(), today.getMonth(), today.getDate(),
												match[1] || 0, match[3] || 0, match[5] || 0) / 1000;
									}
								}
							}
						}

						// other formats and "now" should be parsed by Date.parse()
						if (text === 'now') {
							return now === null || isNaN(now) ? new Date()
								.getTime() / 1000 | 0 : now | 0;
						}
						if (!isNaN(parsed = Date.parse(text))) {
							return parsed / 1000 | 0;
						}
						// Browsers != Chrome have problems parsing ISO 8601 date strings, as they do
						// not accept lower case characters, space, or shortened time zones.
						// Therefore, fix these problems and try again.
						// Examples:
						//   2015-04-15 20:33:59+02
						//   2015-04-15 20:33:59z
						//   2015-04-15t20:33:59+02:00
						if (match = text.match(/^([0-9]{4}-[0-9]{2}-[0-9]{2})[ t]([0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?)([\+-][0-9]{2}(:[0-9]{2})?|z)/)) {
							// fix time zone information
							if (match[4] == 'z') {
								match[4] = 'Z';
							}
							else if (match[4].match(/^([\+-][0-9]{2})$/)) {
								match[4] = match[4] + ':00';
							}

							if (!isNaN(parsed = Date.parse(match[1] + 'T' + match[2] + match[4]))) {
								return parsed / 1000 | 0;
							}
						}

						date = now ? new Date(now * 1000) : new Date();
						days = {
							'sun': 0,
							'mon': 1,
							'tue': 2,
							'wed': 3,
							'thu': 4,
							'fri': 5,
							'sat': 6
						};
						ranges = {
							'yea': 'FullYear',
							'mon': 'Month',
							'day': 'Date',
							'hou': 'Hours',
							'min': 'Minutes',
							'sec': 'Seconds'
						};

						function lastNext(type, range, modifier) {
							var diff, day = days[range];

							if (typeof day !== 'undefined') {
								diff = day - date.getDay();

								if (diff === 0) {
									diff = 7 * modifier;
								} else if (diff > 0 && type === 'last') {
									diff -= 7;
								} else if (diff < 0 && type === 'next') {
									diff += 7;
								}

								date.setDate(date.getDate() + diff);
							}
						}

						function process(val) {
							var splt = val.split(' '), // Todo: Reconcile this with regex using \s, taking into account browser issues with split and regexes
								type = splt[0],
								range = splt[1].substring(0, 3),
								typeIsNumber = /\d+/.test(type),
								ago = splt[2] === 'ago',
								num = (type === 'last' ? -1 : 1) * (ago ? -1 : 1);

							if (typeIsNumber) {
								num *= parseInt(type, 10);
							}

							if (ranges.hasOwnProperty(range) && !splt[1].match(/^mon(day|\.)?$/i)) {
								return date['set' + ranges[range]](date['get' + ranges[range]]() + num);
							}

							if (range === 'wee') {
								return date.setDate(date.getDate() + (num * 7));
							}

							if (type === 'next' || type === 'last') {
								lastNext(type, range, num);
							} else if (!typeIsNumber) {
								return false;
							}

							return true;
						}

						times = '(years?|months?|weeks?|days?|hours?|minutes?|min|seconds?|sec' +
						'|sunday|sun\\.?|monday|mon\\.?|tuesday|tue\\.?|wednesday|wed\\.?' +
						'|thursday|thu\\.?|friday|fri\\.?|saturday|sat\\.?)';
						regex = '([+-]?\\d+\\s' + times + '|' + '(last|next)\\s' + times + ')(\\sago)?';

						match = text.match(new RegExp(regex, 'gi'));
						if (!match) {
							return fail;
						}

						for (i = 0, len = match.length; i < len; i++) {
							if (!process(match[i])) {
								return fail;
							}
						}

						// ECMAScript 5 only
						// if (!match.every(process))
						//    return false;

						return (date.getTime() / 1000);
					};

					self.init();

				};

				window.GWDates = {

					getFieldTimestamp: function( dateTimeFieldId, formId, $inputs ) {

						var field;

						if( typeof dateTimeFieldId == 'object' ) {
							field = dateTimeFieldId;
						} else {
							field = GWDates.getFieldPropsByCSS( dateTimeFieldId, formId );
						}

						var isVisible = window['gf_check_field_rule'] ? gf_check_field_rule( formId, field.id, true, '' ) == 'show' : true,
							$inputs   = typeof $inputs == 'undefined' ? GWDates.getFieldInputs( field.id, formId ) : $inputs,
							value     = 0;

						if( $inputs.length <= 0 || ! isVisible ) {
							return value;
						}

						var allInputsFilled = true;
						$inputs.each( function( i, input ) {
							if( ! $( input ).val() ) {
								allInputsFilled = false;
							}
						} );

						if( ! allInputsFilled ) {
							return value;
						}

						switch( field.type ) {

							case 'date':

								var formatBits = field.dateFormat.split( '_' ),
									mdy        = formatBits[0],
									separator  = formatBits[1] ? formatBits[1] : 'slash',
									sepChars   = { slash: '/', dot: '.', dash: '-' },
									sepChar    = sepChars[ separator ];

								switch( field.dateType ) {
									case 'datefield':
									case 'datedropdown':
										var month       = $inputs.eq( mdy.indexOf( 'm' ) ).val(),
											day         = $inputs.eq( mdy.indexOf( 'd' ) ).val(),
											year        = $inputs.eq( mdy.indexOf( 'y' ) ).val(),
											missingData = ! month || ! day || ! year,
											datetime    = missingData ? false : new Date( year, month - 1, day, 0, 0, 0, 0 );
										break;
									case 'datepicker':
										var dateArr     = $inputs.eq( 0 ).val().split( sepChar ),
											month       = dateArr[ mdy.indexOf( 'm' )],
											day         = dateArr[ mdy.indexOf( 'd' ) ],
											year        = dateArr[ mdy.indexOf( 'y' ) ],
											missingData = ! month || ! day || ! year,
											datetime    = missingData ? false : new Date( year, month - 1, day, 0, 0, 0, 0 );
											datetime    = new Date( year, month - 1, day, 0, 0, 0, 0 );
										break;
									default:
										break;
								}

								var timestamp = datetime === false ? false : datetime.getTime();

								break;

							/* Coming soon...
							case 'time':

								var timestamp   = moment().format( 'X' ),
									hour        = $inputs.eq( 0 ).val(),
									min         = $inputs.eq( 1 ).val(),
									ampm        = $inputs.eq( 2 ).val(),
									missingData = ! hour || ! min;

								if( ! missingData ) {

									var timeStr   = ampm ? hour + ':' + min + ' ' + ampm : hour + ':' + min,
										format    = ampm ? 'h:m a' : 'HH:m',
										datetime  = moment( timeStr, format );

									timestamp = datetime.format( 'X' );

								}

								break;*/

						}

						return timestamp;
					},

					getFieldPropsByCSS: function( fieldId, formId ) {

						var $field  = $( '#field_' + formId + '_' + fieldId ),
							classes = $field.attr( 'class' ),
							field   = { id: fieldId, type: null };

						if( ! classes ) {
							return field;
						}

						var cssClasses = $field.attr( 'class' ).split( /\s+/ );

						for( var i = 0; i < cssClasses.length; i++ ) {
							if( cssClasses[i].indexOf( 'gw-field-' ) != -1 ) {

								var classBits = cssClasses[i].split( '-' ),
									prop      = classBits[2],
									value     = classBits[3];

								field[prop] = value;

							}
						}

						// hacky way of determining whether any property is set
						//return typeof prop != 'undefined' ? field : false;
						return field;
					},

					populateDate: function( dateTimeFieldId, formId, timestamp, format ) {

						var field     = GWDates.getFieldPropsByCSS( dateTimeFieldId, formId ),
							date      = timestamp instanceof Date ? timestamp: new Date( timestamp ),
							isVisible = window['gf_check_field_rule'] ? gf_check_field_rule( formId, field.id, true, '' ) == 'show' : true,
							$inputs   = typeof $inputs == 'undefined' ? $( '#field_' + formId + '_' + field.id ).find( 'input, select' ) : $inputs,
							oldValues = $inputs.val();

						if( $inputs.length <= 0 || ! isVisible ) {
							return false;
						}

						switch( field.type ) {
							case 'date':

								var formatBits = field.dateFormat.split( '_' ),
									mdy        = formatBits[0],
									separator  = formatBits[1] ? formatBits[1] : 'slash',
									sepChars   = { slash: '/', dot: '.', dash: '-' },
									dateParts  = {
										month: isNaN( date.getMonth() ) ? '' : date.getMonth() + 1,
										day:   isNaN( date.getDate() )  ? '' : date.getDate(),
										year:  isNaN( date.getFullYear() )  ? '' : date.getFullYear()
									};

								switch( field.dateType ) {
									case 'datefield':
									case 'datedropdown':
										$inputs.eq( mdy.indexOf( 'm' ) ).val( dateParts.month );
										$inputs.eq( mdy.indexOf( 'd' ) ).val( dateParts.day );
										$inputs.eq( mdy.indexOf( 'y' ) ).val( dateParts.year );
										break;
									case 'datepicker':
										var dateStr = ! dateParts.month ? '' : mdy.split( '' ).join( sepChars[ separator ] )
											.replace( 'm', GWDates.padDateOrMonth( dateParts.month ) )
											.replace( 'd', GWDates.padDateOrMonth( dateParts.day ) )
											.replace( 'y', dateParts.year );
										$inputs.val( dateStr );
										break;
									default:
										break;
								}

								// @todo update to work with multi-input date types
								if( oldValues !== $inputs.val() ) {
									$inputs.change();
								}

								break;
							default:

								var mo    = moment( timestamp ),
									value = mo.formatPHP( format );

								if( $inputs.val() != value ) {
									$inputs.val( value ).change();
								}

								break;
						}

					},

					getFieldInputs: function( fieldId, formId ) {
						return $( '#field_' + formId + '_' + fieldId ).find( 'input, select' ).not( '[type="hidden"]');
					},

					padDateOrMonth: function( num ) {
						return ( '0' + num ).slice( -2 );
					}

				}

			} )( jQuery );

			( function ( m ) {
				/*
				 * PHP => moment.js
				 * Will take a php date format and convert it into a JS format for moment
				 * http://www.php.net/manual/en/function.date.php
				 * http://momentjs.com/docs/#/displaying/format/
				 */
				var formatMap = {
						d: 'DD',
						D: 'ddd',
						j: 'D',
						l: 'dddd',
						N: 'E',
						S: function () {
							return '[' + this.format('Do').replace(/\d*/g, '') + ']';
						},
						w: 'd',
						z: function () {
							return this.format('DDD') - 1;
						},
						W: 'W',
						F: 'MMMM',
						m: 'MM',
						M: 'MMM',
						n: 'M',
						t: function () {
							return this.daysInMonth();
						},
						L: function () {
							return this.isLeapYear() ? 1 : 0;
						},
						o: 'GGGG',
						Y: 'YYYY',
						y: 'YY',
						a: 'a',
						A: 'A',
						B: function () {
							var thisUTC = this.clone().utc(),
								// Shamelessly stolen from http://javascript.about.com/library/blswatch.htm
								swatch = ((thisUTC.hours() + 1) % 24) + (thisUTC.minutes() / 60) + (thisUTC.seconds() / 3600);
							return Math.floor(swatch * 1000 / 24);
						},
						g: 'h',
						G: 'H',
						h: 'hh',
						H: 'HH',
						i: 'mm',
						s: 'ss',
						u: '[u]', // not sure if moment has this
						e: '[e]', // moment does not have this
						I: function () {
							return this.isDST() ? 1 : 0;
						},
						O: 'ZZ',
						P: 'Z',
						T: '[T]', // deprecated in moment
						Z: function () {
							return parseInt(this.format('ZZ'), 10) * 36;
						},
						c: 'YYYY-MM-DD[T]HH:mm:ssZ',
						r: 'ddd, DD MMM YYYY HH:mm:ss ZZ',
						U: 'X'
					},
					formatEx = /[dDjlNSwzWFmMntLoYyaABgGhHisueIOPTZcrU]/g;

				moment.fn.formatPHP = function (format) {
					var that = this;

					return this.format(format.replace(formatEx, function (phpStr) {
						return typeof formatMap[phpStr] === 'function' ? formatMap[phpStr].call(that) : formatMap[phpStr];
					}));
				};

			}( moment ) );

		</script>

		<?php

		self::$is_script_output = true;

	}

	public function add_init_script( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$args = array(
			'formId'        => $this->_args['form_id'],
			'targetFieldId' => $this->_args['target_field_id'],
			'sourceFieldId' => $this->_args['source_field_id'],
			'modifier'      => $this->_args['modifier'],
			// Keep the format in the `date()` format for JS.
			'format'        => $this->get_format( false ),
		);

		$script = 'new GWPopulateDate( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gw_populate_date', $this->_args['form_id'], $this->_args['target_field_id'] ) );

		GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return $form_id == $this->_args['form_id'];
	}

	/**
	 * Convert date/time format between `date()` and `strftime()`
	 *
	 * Timezone conversion is done for Unix. Windows users must exchange %z and %Z.
	 *
	 * Unsupported date formats : S, n, t, L, B, G, u, e, I, P, Z, c, r
	 * Unsupported strftime formats : %U, %W, %C, %g, %r, %R, %T, %X, %c, %D, %F, %x
	 *
	 * @param string $format The format to parse.
	 * @param string $syntax The format's syntax. Either 'strf' for `strtime()` or 'date' for `date()`.
	 *
	 * @return bool|string Returns a string formatted according $syntax using the given $format or `false`.
	 * @link http://php.net/manual/en/function.strftime.php#96424
	 * @link https://gist.github.com/mcaskill/02636e5970be1bb22270
	 *
	 * @example Convert `%A, %B %e, %Y, %l:%M %P` to `l, F j, Y, g:i a`, and vice versa for "Saturday, March 10, 2001, 5:16 pm"
	 */
	function date_format_to( $format, $syntax ) {
		// http://php.net/manual/en/function.strftime.php
		$strf_syntax = array(
			// Day - no strf eq : S (created one called %O)
			'%O',
			'%d',
			'%a',
			'%e',
			'%A',
			'%u',
			'%w',
			'%j',
			// Week - no date eq : %U, %W
			'%V',
			// Month - no strf eq : n, t
			'%B',
			'%m',
			'%b',
			'%-m',
			// Year - no strf eq : L; no date eq : %C, %g
			'%G',
			'%Y',
			'%y',
			// Time - no strf eq : B, G, u; no date eq : %r, %R, %T, %X
			'%P',
			'%p',
			'%l',
			'%I',
			'%H',
			'%M',
			'%S',
			// Timezone - no strf eq : e, I, P, Z
			'%z',
			'%Z',
			// Full Date / Time - no strf eq : c, r; no date eq : %c, %D, %F, %x
			'%s',
		);

		// http://php.net/manual/en/function.date.php
		$date_syntax = array(
			'S',
			'd',
			'D',
			'j',
			'l',
			'N',
			'w',
			'z',
			'W',
			'F',
			'm',
			'M',
			'n',
			'o',
			'Y',
			'y',
			'a',
			'A',
			'g',
			'h',
			'H',
			'i',
			's',
			'O',
			'T',
			'U',
		);

		switch ( $syntax ) {
			case 'date':
				$from = $strf_syntax;
				$to   = $date_syntax;
				break;

			case 'strf':
				$from = $date_syntax;
				$to   = $strf_syntax;
				break;

			default:
				return false;
		}

		$pattern = array_map(
			function ( $s ) {
				return '/(?<!\\\\|\%)' . $s . '/';
			},
			$from
		);

		return preg_replace( $pattern, $to, $format );
	}

}

// add date props as CSS classes
add_filter( 'gform_pre_render', function( $form ) {

	$prefix = 'gw-field-';

	foreach ( $form['fields'] as &$field ) {

		$classes = false;

		switch ( GFFormsModel::get_input_type( $field ) ) {
			case 'date':
				$classes = array(
					'type'        => sprintf( '%stype-%s', $prefix, $field->type ),
					'date_type'   => sprintf( '%sdateType-%s', $prefix, $field->dateType ),
					'date_format' => sprintf( '%sdateFormat-%s', $prefix, $field->dateFormat ? $field->dateFormat : 'mdy' ),
				);
				break;
			case 'time':
				$classes = array(
					'type'        => sprintf( '%stype-%s', $prefix, $field->type ),
					'time_format' => sprintf( '%stimeFormat-%s', $prefix, $field->timeFormat ? $field->timeFormat : 'mdy' ),
				);
				break;
		}

		if ( $classes ) {
			$current_classes  = explode( ' ', $field->cssClass );
			$classes          = array_unique( array_merge( $current_classes, $classes ), SORT_REGULAR );
			$field->cssClass .= ' ' . implode( ' ', $classes );
		}
	}

	return $form;
} );

# Configuration

new GW_Populate_Date( array(
	'form_id'         => 123,
	'source_field_id' => 4,
	'target_field_id' => 5,
	'modifier'        => '+7 days',
) );

