<?php
/**
 * Gravity Wiz // Gravity Forms // Populate Date
 *
 * Provides the ability to populate a Date field with a modified date based on the current date or a user-submitted date.
 *
 * @version   2.8
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/populate-dates-gravity-form-fields/
 */
class GW_Populate_Date {

	protected static $is_script_output = false;

	private $_args         = array();
	private $_field_values = array();

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
			'utc_offset'             => get_option( 'gmt_offset' ), // Used only for time calculations on the current date.
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
			add_filter( 'gform_pre_render', array( $this, 'load_form_script' ) );
			add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ) );
			add_filter( 'gform_enqueue_scripts', array( $this, 'enqueue_form_scripts' ) );
		} else {
			// Populate dates *before* Populate Anything so GPPA can use them in live merge tags and it's own pre render population.
			add_filter( 'gform_pre_render', array( $this, 'populate_date_on_pre_render' ), 8 );
		}

		if ( $this->_args['override_on_submission'] ) {
			add_action( 'gform_pre_submission', array( $this, 'populate_date_on_pre_submission' ) );
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

				$timestamp = false;

				if ( $this->_args['source_field_id'] ) {
					$timestamp = $this->get_source_timestamp( GFFormsModel::get_field( $form, $this->_args['source_field_id'] ) );
				}

				$value = $this->get_modified_date( $field, $timestamp );

				if ( $value ) {
					$_POST[ "input_{$field['id']}" ] = $value;
				}
			}
		}

	}

	public function get_source_timestamp( $field ) {

		$raw = rgpost( 'input_' . $field['id'] );
		if ( is_array( $raw ) ) {
			$raw = array_filter( $raw );
		}

		switch ( $field->type ) {
			case 'time':
				list( $hour, $minute, $ampm ) = array_pad( $raw, 3, false );
				if ( $ampm ) {
					$ampm = strtolower( $ampm );
					if ( $ampm === 'pm' ) {
						$hour -= 12;
					} elseif ( $ampm === 'am' && (int) $hour === 12 ) {
						$hour = 0;
					}
				}
				$timestamp = mktime( $hour, $minute );
				break;
			case 'date':
			default:
				list( $format, $divider ) = $field['dateFormat'] ? array_pad( explode( '_', $field['dateFormat'] ), 2, 'slash' ) : array( 'mdy', 'slash' );
				$dividers                 = array(
					'slash' => '/',
					'dot'   => '.',
					'dash'  => '-',
				);

				if ( empty( $raw ) ) {
					// phpcs:disable WordPress.DateTime.RestrictedFunctions.date_date
					$raw = date( implode( $dividers[ $divider ], str_split( $format ) ) );
				}

				$date = ! is_array( $raw ) ? explode( $dividers[ $divider ], $raw ) : $raw;

				$month = $date[ strpos( $format, 'm' ) ];
				$day   = $date[ strpos( $format, 'd' ) ];
				$year  = $date[ strpos( $format, 'y' ) ];

				$timestamp = mktime( 0, 0, 0, $month, $day, $year );

				break;
		}

		return $timestamp;
	}

	public function get_modified_date( $field, $timestamp = false ) {

		if ( ! $timestamp ) {
            // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
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
				$modifier = ! rgblank( $value ) ? sprintf( str_replace( '{0}', '%d', $modifier['modifier'] ), $value ) : false;
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

		if ( $field->get_input_type() === 'time' ) {
			// This is really a Time value...
			$hour   = (int) date( 'G', $timestamp );
			$minute = date( 'i', $timestamp );
			$ampm   = 'AM';
			if ( $field->timeFormat === '12' ) {
				if ( $hour > 12 ) {
					$hour -= 12;
					$ampm  = 'PM';
				} elseif ( $hour === 0 ) {
					$hour = 12;
				}
			}
			// Ensure the time value is retained as a String.
			// If saved in array format, it will not reload the value after conditional viewing/hiding.
			$date = "{$hour}:{$minute} {$ampm}";
		} elseif ( $this->_args['enable_i18n'] ) {
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

						// Listen for GPPA's new `gppa_updated_batch_fields`
						$( document ).on( 'gppa_updated_batch_fields', function ( e, formId, updatedFieldIDs ) {
							for ( var i = 0, max = updatedFieldIDs.length; i < max; i ++ ) {
								if ( self.sourceFieldId === parseInt( updatedFieldIDs[i] ) ) {
									self.populateDate( self.sourceFieldId, self.targetFieldId, self.getModifier(), self.format );
								}
							}
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

						var timestamp = GWDates.getFieldTimestamp( sourceFieldId, self.formId, undefined, self.utcOffset );
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
									modifier = value !== '' ? self.modifier.modifier.gformFormat( value ) : false;
								break;
						}

						return modifier;
					};

					self.getFieldValue = function( inputId ) {

						var $input = self.getInputs( inputId ),
							value  = self.getCleanNumber( $input.val(), gformExtractFieldId( inputId ), self.formId );

						// Cannot retrieve value from `gfield_radio` directly on `$input`.
						if ( ! $input.val() && $input.hasClass( 'gfield_radio' ) ) {
							value = $input.find('input[type="radio"]:checked').val();
						}

						value = gform.applyFilters( 'gwpd_get_field_value', value, $input, inputId );

						if ( ! value || isNaN( value ) ) {
							value = 0;
						}

						return value;
					};

					/**
					 * Gravity Forms gformToNumber() and other number-conversion methods do a great job converting
					 * currency text to raw numbers but they are still in numeric format of the specified currency
					 */
					self.getCleanNumber = function( value, fieldId, formId ) {
						if (typeof value === 'undefined') {
							return value;
						}

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
							$input     = $( id.gformFormat( self.formId, fieldId, inputIndex ) );

						return $input;
					};

					GWPopulateDate.strtotime = function( str, now ) {
						var reSpace = '[ \\t]+';
						var reSpaceOpt = '[ \\t]*';
						var reMeridian = '(?:([ap])\\.?m\\.?([\\t ]|$))';
						var reHour24 = '(2[0-4]|[01]?[0-9])';
						var reHour24lz = '([01][0-9]|2[0-4])';
						var reHour12 = '(0?[1-9]|1[0-2])';
						var reMinute = '([0-5]?[0-9])';
						var reMinutelz = '([0-5][0-9])';
						var reSecond = '(60|[0-5]?[0-9])';
						var reSecondlz = '(60|[0-5][0-9])';
						var reFrac = '(?:\\.([0-9]+))';

						var reDayfull = 'sunday|monday|tuesday|wednesday|thursday|friday|saturday';
						var reDayabbr = 'sun|mon|tue|wed|thu|fri|sat';
						var reDaytext = reDayfull + '|' + reDayabbr + '|weekdays?';

						var reReltextnumber = 'first|second|third|fourth|fifth|sixth|seventh|eighth?|ninth|tenth|eleventh|twelfth';
						var reReltexttext = 'next|last|previous|this';
						var reReltextunit = '(?:second|sec|minute|min|hour|day|fortnight|forthnight|month|year)s?|weeks|' + reDaytext;

						var reYear = '([0-9]{1,4})';
						var reYear2 = '([0-9]{2})';
						var reYear4 = '([0-9]{4})';
						var reYear4withSign = '([+-]?[0-9]{4})';
						var reMonth = '(1[0-2]|0?[0-9])';
						var reMonthlz = '(0[0-9]|1[0-2])';
						var reDay = '(?:(3[01]|[0-2]?[0-9])(?:st|nd|rd|th)?)';
						var reDaylz = '(0[0-9]|[1-2][0-9]|3[01])';

						var reMonthFull = 'january|february|march|april|may|june|july|august|september|october|november|december';
						var reMonthAbbr = 'jan|feb|mar|apr|may|jun|jul|aug|sept?|oct|nov|dec';
						var reMonthroman = 'i[vx]|vi{0,3}|xi{0,2}|i{1,3}';
						var reMonthText = '(' + reMonthFull + '|' + reMonthAbbr + '|' + reMonthroman + ')';

						var reTzCorrection = '((?:GMT)?([+-])' + reHour24 + ':?' + reMinute + '?)';
						var reDayOfYear = '(00[1-9]|0[1-9][0-9]|[12][0-9][0-9]|3[0-5][0-9]|36[0-6])';
						var reWeekOfYear = '(0[1-9]|[1-4][0-9]|5[0-3])';

						var reDateNoYear = reMonthText + '[ .\\t-]*' + reDay + '[,.stndrh\\t ]*';

						function processMeridian(hour, meridian) {
							meridian = meridian && meridian.toLowerCase();

							switch (meridian) {
								case 'a':
									hour += hour === 12 ? -12 : 0;
									break;
								case 'p':
									hour += hour !== 12 ? 12 : 0;
									break;
							}

							return hour;
						}

						function processYear(yearStr) {
							var year = +yearStr;

							if (yearStr.length < 4 && year < 100) {
								year += year < 70 ? 2000 : 1900;
							}

							return year;
						}

						function lookupMonth(monthStr) {
							return {
								jan: 0,
								january: 0,
								i: 0,
								feb: 1,
								february: 1,
								ii: 1,
								mar: 2,
								march: 2,
								iii: 2,
								apr: 3,
								april: 3,
								iv: 3,
								may: 4,
								v: 4,
								jun: 5,
								june: 5,
								vi: 5,
								jul: 6,
								july: 6,
								vii: 6,
								aug: 7,
								august: 7,
								viii: 7,
								sep: 8,
								sept: 8,
								september: 8,
								ix: 8,
								oct: 9,
								october: 9,
								x: 9,
								nov: 10,
								november: 10,
								xi: 10,
								dec: 11,
								december: 11,
								xii: 11
							}[monthStr.toLowerCase()];
						}

						function lookupWeekday(dayStr) {
							var desiredSundayNumber = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;

							var dayNumbers = {
								mon: 1,
								monday: 1,
								tue: 2,
								tuesday: 2,
								wed: 3,
								wednesday: 3,
								thu: 4,
								thursday: 4,
								fri: 5,
								friday: 5,
								sat: 6,
								saturday: 6,
								sun: 0,
								sunday: 0
							};

							return dayNumbers[dayStr.toLowerCase()] || desiredSundayNumber;
						}

						function lookupRelative(relText) {
							var relativeNumbers = {
								last: -1,
								previous: -1,
								this: 0,
								first: 1,
								next: 1,
								second: 2,
								third: 3,
								fourth: 4,
								fifth: 5,
								sixth: 6,
								seventh: 7,
								eight: 8,
								eighth: 8,
								ninth: 9,
								tenth: 10,
								eleventh: 11,
								twelfth: 12
							};

							var relativeBehavior = {
								this: 1
							};

							var relTextLower = relText.toLowerCase();

							return {
								amount: relativeNumbers[relTextLower],
								behavior: relativeBehavior[relTextLower] || 0
							};
						}

						function processTzCorrection(tzOffset, oldValue) {
							var reTzCorrectionLoose = /(?:GMT)?([+-])(\d+)(:?)(\d{0,2})/i;
							tzOffset = tzOffset && tzOffset.match(reTzCorrectionLoose);

							if (!tzOffset) {
								return oldValue;
							}

							var sign = tzOffset[1] === '-' ? 1 : -1;
							var hours = +tzOffset[2];
							var minutes = +tzOffset[4];

							if (!tzOffset[4] && !tzOffset[3]) {
								minutes = Math.floor(hours % 100);
								hours = Math.floor(hours / 100);
							}

							return sign * (hours * 60 + minutes);
						}

						var formats = {
							yesterday: {
								regex: /^yesterday/i,
								name: 'yesterday',
								callback: function callback() {
									this.rd -= 1;
									return this.resetTime();
								}
							},

							now: {
								regex: /^now/i,
								name: 'now'
								// do nothing
							},

							noon: {
								regex: /^noon/i,
								name: 'noon',
								callback: function callback() {
									return this.resetTime() && this.time(12, 0, 0, 0);
								}
							},

							midnightOrToday: {
								regex: /^(midnight|today)/i,
								name: 'midnight | today',
								callback: function callback() {
									return this.resetTime();
								}
							},

							tomorrow: {
								regex: /^tomorrow/i,
								name: 'tomorrow',
								callback: function callback() {
									this.rd += 1;
									return this.resetTime();
								}
							},

							timestamp: {
								regex: /^@(-?\d+)/i,
								name: 'timestamp',
								callback: function callback(match, timestamp) {
									this.rs += +timestamp;
									this.y = 1970;
									this.m = 0;
									this.d = 1;
									this.dates = 0;

									return this.resetTime() && this.zone(0);
								}
							},

							firstOrLastDay: {
								regex: /^(first|last) day of/i,
								name: 'firstdayof | lastdayof',
								callback: function callback(match, day) {
									if (day.toLowerCase() === 'first') {
										this.firstOrLastDayOfMonth = 1;
									} else {
										this.firstOrLastDayOfMonth = -1;
									}
								}
							},

							backOrFrontOf: {
								regex: RegExp('^(back|front) of ' + reHour24 + reSpaceOpt + reMeridian + '?', 'i'),
								name: 'backof | frontof',
								callback: function callback(match, side, hours, meridian) {
									var back = side.toLowerCase() === 'back';
									var hour = +hours;
									var minute = 15;

									if (!back) {
										hour -= 1;
										minute = 45;
									}

									hour = processMeridian(hour, meridian);

									return this.resetTime() && this.time(hour, minute, 0, 0);
								}
							},

							weekdayOf: {
								regex: RegExp('^(' + reReltextnumber + '|' + reReltexttext + ')' + reSpace + '(' + reDayfull + '|' + reDayabbr + ')' + reSpace + 'of', 'i'),
								name: 'weekdayof'
								// todo
							},

							mssqltime: {
								regex: RegExp('^' + reHour12 + ':' + reMinutelz + ':' + reSecondlz + '[:.]([0-9]+)' + reMeridian, 'i'),
								name: 'mssqltime',
								callback: function callback(match, hour, minute, second, frac, meridian) {
									return this.time(processMeridian(+hour, meridian), +minute, +second, +frac.substr(0, 3));
								}
							},

							timeLong12: {
								regex: RegExp('^' + reHour12 + '[:.]' + reMinute + '[:.]' + reSecondlz + reSpaceOpt + reMeridian, 'i'),
								name: 'timelong12',
								callback: function callback(match, hour, minute, second, meridian) {
									return this.time(processMeridian(+hour, meridian), +minute, +second, 0);
								}
							},

							timeShort12: {
								regex: RegExp('^' + reHour12 + '[:.]' + reMinutelz + reSpaceOpt + reMeridian, 'i'),
								name: 'timeshort12',
								callback: function callback(match, hour, minute, meridian) {
									return this.time(processMeridian(+hour, meridian), +minute, 0, 0);
								}
							},

							timeTiny12: {
								regex: RegExp('^' + reHour12 + reSpaceOpt + reMeridian, 'i'),
								name: 'timetiny12',
								callback: function callback(match, hour, meridian) {
									return this.time(processMeridian(+hour, meridian), 0, 0, 0);
								}
							},

							soap: {
								regex: RegExp('^' + reYear4 + '-' + reMonthlz + '-' + reDaylz + 'T' + reHour24lz + ':' + reMinutelz + ':' + reSecondlz + reFrac + reTzCorrection + '?', 'i'),
								name: 'soap',
								callback: function callback(match, year, month, day, hour, minute, second, frac, tzCorrection) {
									return this.ymd(+year, month - 1, +day) && this.time(+hour, +minute, +second, +frac.substr(0, 3)) && this.zone(processTzCorrection(tzCorrection));
								}
							},

							wddx: {
								regex: RegExp('^' + reYear4 + '-' + reMonth + '-' + reDay + 'T' + reHour24 + ':' + reMinute + ':' + reSecond),
								name: 'wddx',
								callback: function callback(match, year, month, day, hour, minute, second) {
									return this.ymd(+year, month - 1, +day) && this.time(+hour, +minute, +second, 0);
								}
							},

							exif: {
								regex: RegExp('^' + reYear4 + ':' + reMonthlz + ':' + reDaylz + ' ' + reHour24lz + ':' + reMinutelz + ':' + reSecondlz, 'i'),
								name: 'exif',
								callback: function callback(match, year, month, day, hour, minute, second) {
									return this.ymd(+year, month - 1, +day) && this.time(+hour, +minute, +second, 0);
								}
							},

							xmlRpc: {
								regex: RegExp('^' + reYear4 + reMonthlz + reDaylz + 'T' + reHour24 + ':' + reMinutelz + ':' + reSecondlz),
								name: 'xmlrpc',
								callback: function callback(match, year, month, day, hour, minute, second) {
									return this.ymd(+year, month - 1, +day) && this.time(+hour, +minute, +second, 0);
								}
							},

							xmlRpcNoColon: {
								regex: RegExp('^' + reYear4 + reMonthlz + reDaylz + '[Tt]' + reHour24 + reMinutelz + reSecondlz),
								name: 'xmlrpcnocolon',
								callback: function callback(match, year, month, day, hour, minute, second) {
									return this.ymd(+year, month - 1, +day) && this.time(+hour, +minute, +second, 0);
								}
							},

							clf: {
								regex: RegExp('^' + reDay + '/(' + reMonthAbbr + ')/' + reYear4 + ':' + reHour24lz + ':' + reMinutelz + ':' + reSecondlz + reSpace + reTzCorrection, 'i'),
								name: 'clf',
								callback: function callback(match, day, month, year, hour, minute, second, tzCorrection) {
									return this.ymd(+year, lookupMonth(month), +day) && this.time(+hour, +minute, +second, 0) && this.zone(processTzCorrection(tzCorrection));
								}
							},

							iso8601long: {
								regex: RegExp('^t?' + reHour24 + '[:.]' + reMinute + '[:.]' + reSecond + reFrac, 'i'),
								name: 'iso8601long',
								callback: function callback(match, hour, minute, second, frac) {
									return this.time(+hour, +minute, +second, +frac.substr(0, 3));
								}
							},

							dateTextual: {
								regex: RegExp('^' + reMonthText + '[ .\\t-]*' + reDay + '[,.stndrh\\t ]+' + reYear, 'i'),
								name: 'datetextual',
								callback: function callback(match, month, day, year) {
									return this.ymd(processYear(year), lookupMonth(month), +day);
								}
							},

							pointedDate4: {
								regex: RegExp('^' + reDay + '[.\\t-]' + reMonth + '[.-]' + reYear4),
								name: 'pointeddate4',
								callback: function callback(match, day, month, year) {
									return this.ymd(+year, month - 1, +day);
								}
							},

							pointedDate2: {
								regex: RegExp('^' + reDay + '[.\\t]' + reMonth + '\\.' + reYear2),
								name: 'pointeddate2',
								callback: function callback(match, day, month, year) {
									return this.ymd(processYear(year), month - 1, +day);
								}
							},

							timeLong24: {
								regex: RegExp('^t?' + reHour24 + '[:.]' + reMinute + '[:.]' + reSecond),
								name: 'timelong24',
								callback: function callback(match, hour, minute, second) {
									return this.time(+hour, +minute, +second, 0);
								}
							},

							dateNoColon: {
								regex: RegExp('^' + reYear4 + reMonthlz + reDaylz),
								name: 'datenocolon',
								callback: function callback(match, year, month, day) {
									return this.ymd(+year, month - 1, +day);
								}
							},

							pgydotd: {
								regex: RegExp('^' + reYear4 + '\\.?' + reDayOfYear),
								name: 'pgydotd',
								callback: function callback(match, year, day) {
									return this.ymd(+year, 0, +day);
								}
							},

							timeShort24: {
								regex: RegExp('^t?' + reHour24 + '[:.]' + reMinute, 'i'),
								name: 'timeshort24',
								callback: function callback(match, hour, minute) {
									return this.time(+hour, +minute, 0, 0);
								}
							},

							iso8601noColon: {
								regex: RegExp('^t?' + reHour24lz + reMinutelz + reSecondlz, 'i'),
								name: 'iso8601nocolon',
								callback: function callback(match, hour, minute, second) {
									return this.time(+hour, +minute, +second, 0);
								}
							},

							iso8601dateSlash: {
								// eventhough the trailing slash is optional in PHP
								// here it's mandatory and inputs without the slash
								// are handled by dateslash
								regex: RegExp('^' + reYear4 + '/' + reMonthlz + '/' + reDaylz + '/'),
								name: 'iso8601dateslash',
								callback: function callback(match, year, month, day) {
									return this.ymd(+year, month - 1, +day);
								}
							},

							dateSlash: {
								regex: RegExp('^' + reYear4 + '/' + reMonth + '/' + reDay),
								name: 'dateslash',
								callback: function callback(match, year, month, day) {
									return this.ymd(+year, month - 1, +day);
								}
							},

							american: {
								regex: RegExp('^' + reMonth + '/' + reDay + '/' + reYear),
								name: 'american',
								callback: function callback(match, month, day, year) {
									return this.ymd(processYear(year), month - 1, +day);
								}
							},

							americanShort: {
								regex: RegExp('^' + reMonth + '/' + reDay),
								name: 'americanshort',
								callback: function callback(match, month, day) {
									return this.ymd(this.y, month - 1, +day);
								}
							},

							gnuDateShortOrIso8601date2: {
								// iso8601date2 is complete subset of gnudateshort
								regex: RegExp('^' + reYear + '-' + reMonth + '-' + reDay),
								name: 'gnudateshort | iso8601date2',
								callback: function callback(match, year, month, day) {
									return this.ymd(processYear(year), month - 1, +day);
								}
							},

							iso8601date4: {
								regex: RegExp('^' + reYear4withSign + '-' + reMonthlz + '-' + reDaylz),
								name: 'iso8601date4',
								callback: function callback(match, year, month, day) {
									return this.ymd(+year, month - 1, +day);
								}
							},

							gnuNoColon: {
								regex: RegExp('^t?' + reHour24lz + reMinutelz, 'i'),
								name: 'gnunocolon',
								callback: function callback(match, hour, minute) {
									// this rule is a special case
									// if time was already set once by any preceding rule, it sets the captured value as year
									switch (this.times) {
										case 0:
											return this.time(+hour, +minute, 0, this.f);
										case 1:
											this.y = hour * 100 + +minute;
											this.times++;

											return true;
										default:
											return false;
									}
								}
							},

							gnuDateShorter: {
								regex: RegExp('^' + reYear4 + '-' + reMonth),
								name: 'gnudateshorter',
								callback: function callback(match, year, month) {
									return this.ymd(+year, month - 1, 1);
								}
							},

							pgTextReverse: {
								// note: allowed years are from 32-9999
								// years below 32 should be treated as days in datefull
								regex: RegExp('^' + '(\\d{3,4}|[4-9]\\d|3[2-9])-(' + reMonthAbbr + ')-' + reDaylz, 'i'),
								name: 'pgtextreverse',
								callback: function callback(match, year, month, day) {
									return this.ymd(processYear(year), lookupMonth(month), +day);
								}
							},

							dateFull: {
								regex: RegExp('^' + reDay + '[ \\t.-]*' + reMonthText + '[ \\t.-]*' + reYear, 'i'),
								name: 'datefull',
								callback: function callback(match, day, month, year) {
									return this.ymd(processYear(year), lookupMonth(month), +day);
								}
							},

							dateNoDay: {
								regex: RegExp('^' + reMonthText + '[ .\\t-]*' + reYear4, 'i'),
								name: 'datenoday',
								callback: function callback(match, month, year) {
									return this.ymd(+year, lookupMonth(month), 1);
								}
							},

							dateNoDayRev: {
								regex: RegExp('^' + reYear4 + '[ .\\t-]*' + reMonthText, 'i'),
								name: 'datenodayrev',
								callback: function callback(match, year, month) {
									return this.ymd(+year, lookupMonth(month), 1);
								}
							},

							pgTextShort: {
								regex: RegExp('^(' + reMonthAbbr + ')-' + reDaylz + '-' + reYear, 'i'),
								name: 'pgtextshort',
								callback: function callback(match, month, day, year) {
									return this.ymd(processYear(year), lookupMonth(month), +day);
								}
							},

							dateNoYear: {
								regex: RegExp('^' + reDateNoYear, 'i'),
								name: 'datenoyear',
								callback: function callback(match, month, day) {
									return this.ymd(this.y, lookupMonth(month), +day);
								}
							},

							dateNoYearRev: {
								regex: RegExp('^' + reDay + '[ .\\t-]*' + reMonthText, 'i'),
								name: 'datenoyearrev',
								callback: function callback(match, day, month) {
									return this.ymd(this.y, lookupMonth(month), +day);
								}
							},

							isoWeekDay: {
								regex: RegExp('^' + reYear4 + '-?W' + reWeekOfYear + '(?:-?([0-7]))?'),
								name: 'isoweekday | isoweek',
								callback: function callback(match, year, week, day) {
									day = day ? +day : 1;

									if (!this.ymd(+year, 0, 1)) {
										return false;
									}

									// get day of week for Jan 1st
									var dayOfWeek = new Date(this.y, this.m, this.d).getDay();

									// and use the day to figure out the offset for day 1 of week 1
									dayOfWeek = 0 - (dayOfWeek > 4 ? dayOfWeek - 7 : dayOfWeek);

									this.rd += dayOfWeek + (week - 1) * 7 + day;
								}
							},

							relativeText: {
								regex: RegExp('^(' + reReltextnumber + '|' + reReltexttext + ')' + reSpace + '(' + reReltextunit + ')', 'i'),
								name: 'relativetext',
								callback: function callback(match, relValue, relUnit) {
									// todo: implement handling of 'this time-unit'
									// eslint-disable-next-line no-unused-vars
									var _lookupRelative = lookupRelative(relValue),
										amount = _lookupRelative.amount,
										behavior = _lookupRelative.behavior;

									switch (relUnit.toLowerCase()) {
										case 'sec':
										case 'secs':
										case 'second':
										case 'seconds':
											this.rs += amount;
											break;
										case 'min':
										case 'mins':
										case 'minute':
										case 'minutes':
											this.ri += amount;
											break;
										case 'hour':
										case 'hours':
											this.rh += amount;
											break;
										case 'day':
										case 'days':
											this.rd += amount;
											break;
										case 'fortnight':
										case 'fortnights':
										case 'forthnight':
										case 'forthnights':
											this.rd += amount * 14;
											break;
										case 'week':
										case 'weeks':
											this.rd += amount * 7;
											break;
										case 'month':
										case 'months':
											this.rm += amount;
											break;
										case 'year':
										case 'years':
											this.ry += amount;
											break;
										case 'mon':case 'monday':
										case 'tue':case 'tuesday':
										case 'wed':case 'wednesday':
										case 'thu':case 'thursday':
										case 'fri':case 'friday':
										case 'sat':case 'saturday':
										case 'sun':case 'sunday':
											this.resetTime();
											this.weekday = lookupWeekday(relUnit, 7);
											this.weekdayBehavior = 1;
											this.rd += (amount > 0 ? amount - 1 : amount) * 7;
											break;
										case 'weekday':
										case 'weekdays':
											// todo
											break;
									}
								}
							},

							relative: {
								regex: RegExp('^([+-]*)[ \\t]*(\\d+)' + reSpaceOpt + '(' + reReltextunit + '|week)', 'i'),
								name: 'relative',
								callback: function callback(match, signs, relValue, relUnit) {
									var minuses = signs.replace(/[^-]/g, '').length;

									var amount = +relValue * Math.pow(-1, minuses);

									switch (relUnit.toLowerCase()) {
										case 'sec':
										case 'secs':
										case 'second':
										case 'seconds':
											this.rs += amount;
											break;
										case 'min':
										case 'mins':
										case 'minute':
										case 'minutes':
											this.ri += amount;
											break;
										case 'hour':
										case 'hours':
											this.rh += amount;
											break;
										case 'day':
										case 'days':
											this.rd += amount;
											break;
										case 'fortnight':
										case 'fortnights':
										case 'forthnight':
										case 'forthnights':
											this.rd += amount * 14;
											break;
										case 'week':
										case 'weeks':
											this.rd += amount * 7;
											break;
										case 'month':
										case 'months':
											this.rm += amount;
											break;
										case 'year':
										case 'years':
											this.ry += amount;
											break;
										case 'mon':case 'monday':
										case 'tue':case 'tuesday':
										case 'wed':case 'wednesday':
										case 'thu':case 'thursday':
										case 'fri':case 'friday':
										case 'sat':case 'saturday':
										case 'sun':case 'sunday':
											this.resetTime();
											this.weekday = lookupWeekday(relUnit, 7);
											this.weekdayBehavior = 1;
											this.rd += (amount > 0 ? amount - 1 : amount) * 7;
											break;
										case 'weekday':
										case 'weekdays':
											this.rwd += amount;
											break;
									}
								}
							},

							dayText: {
								regex: RegExp('^(' + reDaytext + ')', 'i'),
								name: 'daytext',
								callback: function callback(match, dayText) {
									this.resetTime();
									this.weekday = lookupWeekday(dayText, 0);

									if (this.weekdayBehavior !== 2) {
										this.weekdayBehavior = 1;
									}
								}
							},

							relativeTextWeek: {
								regex: RegExp('^(' + reReltexttext + ')' + reSpace + 'week', 'i'),
								name: 'relativetextweek',
								callback: function callback(match, relText) {
									this.weekdayBehavior = 2;

									switch (relText.toLowerCase()) {
										case 'this':
											this.rd += 0;
											break;
										case 'next':
											this.rd += 7;
											break;
										case 'last':
										case 'previous':
											this.rd -= 7;
											break;
									}

									if (isNaN(this.weekday)) {
										this.weekday = 1;
									}
								}
							},

							monthFullOrMonthAbbr: {
								regex: RegExp('^(' + reMonthFull + '|' + reMonthAbbr + ')', 'i'),
								name: 'monthfull | monthabbr',
								callback: function callback(match, month) {
									return this.ymd(this.y, lookupMonth(month), this.d);
								}
							},

							tzCorrection: {
								regex: RegExp('^' + reTzCorrection, 'i'),
								name: 'tzcorrection',
								callback: function callback(tzCorrection) {
									return this.zone(processTzCorrection(tzCorrection));
								}
							},

							ago: {
								regex: /^ago/i,
								name: 'ago',
								callback: function callback() {
									this.ry  = -this.ry;
									this.rm  = -this.rm;
									this.rd  = -this.rd;
									this.rwd = -this.rwd;
									this.rh  = -this.rh;
									this.ri  = -this.ri;
									this.rs  = -this.rs;
									this.rf  = -this.rf;
								}
							},

							year4: {
								regex: RegExp('^' + reYear4),
								name: 'year4',
								callback: function callback(match, year) {
									this.y = +year;
									return true;
								}
							},

							whitespace: {
								regex: /^[ .,\t]+/,
								name: 'whitespace'
								// do nothing
							},

							dateShortWithTimeLong: {
								regex: RegExp('^' + reDateNoYear + 't?' + reHour24 + '[:.]' + reMinute + '[:.]' + reSecond, 'i'),
								name: 'dateshortwithtimelong',
								callback: function callback(match, month, day, hour, minute, second) {
									return this.ymd(this.y, lookupMonth(month), +day) && this.time(+hour, +minute, +second, 0);
								}
							},

							dateShortWithTimeLong12: {
								regex: RegExp('^' + reDateNoYear + reHour12 + '[:.]' + reMinute + '[:.]' + reSecondlz + reSpaceOpt + reMeridian, 'i'),
								name: 'dateshortwithtimelong12',
								callback: function callback(match, month, day, hour, minute, second, meridian) {
									return this.ymd(this.y, lookupMonth(month), +day) && this.time(processMeridian(+hour, meridian), +minute, +second, 0);
								}
							},

							dateShortWithTimeShort: {
								regex: RegExp('^' + reDateNoYear + 't?' + reHour24 + '[:.]' + reMinute, 'i'),
								name: 'dateshortwithtimeshort',
								callback: function callback(match, month, day, hour, minute) {
									return this.ymd(this.y, lookupMonth(month), +day) && this.time(+hour, +minute, 0, 0);
								}
							},

							dateShortWithTimeShort12: {
								regex: RegExp('^' + reDateNoYear + reHour12 + '[:.]' + reMinutelz + reSpaceOpt + reMeridian, 'i'),
								name: 'dateshortwithtimeshort12',
								callback: function callback(match, month, day, hour, minute, meridian) {
									return this.ymd(this.y, lookupMonth(month), +day) && this.time(processMeridian(+hour, meridian), +minute, 0, 0);
								}
							}
						};

						var resultProto = {
							// date
							y: NaN,
							m: NaN,
							d: NaN,
							// time
							h: NaN,
							i: NaN,
							s: NaN,
							f: NaN,

							// relative shifts
							ry: 0,
							rm: 0,
							rd: 0,
							rwd: 0,
							rh: 0,
							ri: 0,
							rs: 0,
							rf: 0,

							// weekday related shifts
							weekday: NaN,
							weekdayBehavior: 0,

							// first or last day of month
							// 0 none, 1 first, -1 last
							firstOrLastDayOfMonth: 0,

							// timezone correction in minutes
							z: NaN,

							// counters
							dates: 0,
							times: 0,
							zones: 0,

							// helper functions
							ymd: function ymd(y, m, d) {
								if (this.dates > 0) {
									return false;
								}

								this.dates++;
								this.y = y;
								this.m = m;
								this.d = d;
								return true;
							},
							time: function time(h, i, s, f) {
								if (this.times > 0) {
									return false;
								}

								this.times++;
								this.h = h;
								this.i = i;
								this.s = s;
								this.f = f;

								return true;
							},
							resetTime: function resetTime() {
								this.h = 0;
								this.i = 0;
								this.s = 0;
								this.f = 0;
								this.times = 0;

								return true;
							},
							zone: function zone(minutes) {
								if (this.zones <= 1) {
									this.zones++;
									this.z = minutes;
									return true;
								}

								return false;
							},
							toDate: function toDate(relativeTo) {
								if (this.dates && !this.times) {
									this.h = this.i = this.s = this.f = 0;
								}

								// fill holes
								if (isNaN(this.y)) {
									this.y = relativeTo.getFullYear();
								}

								if (isNaN(this.m)) {
									this.m = relativeTo.getMonth();
								}

								if (isNaN(this.d)) {
									this.d = relativeTo.getDate();
								}

								if (isNaN(this.h)) {
									this.h = relativeTo.getHours();
								}

								if (isNaN(this.i)) {
									this.i = relativeTo.getMinutes();
								}

								if (isNaN(this.s)) {
									this.s = relativeTo.getSeconds();
								}

								if (isNaN(this.f)) {
									this.f = relativeTo.getMilliseconds();
								}

								// adjust special early
								switch (this.firstOrLastDayOfMonth) {
									case 1:
										this.d = 1;
										break;
									case -1:
										this.d = 0;
										this.m += 1;
										break;
								}

								if (!isNaN(this.weekday)) {
									var date = new Date(relativeTo.getTime());
									date.setFullYear(this.y, this.m, this.d);
									date.setHours(this.h, this.i, this.s, this.f);

									var dow = date.getDay();

									if (this.weekdayBehavior === 2) {
										// To make "this week" work, where the current day of week is a "sunday"
										if (dow === 0 && this.weekday !== 0) {
											this.weekday = -6;
										}

										// To make "sunday this week" work, where the current day of week is not a "sunday"
										if (this.weekday === 0 && dow !== 0) {
											this.weekday = 7;
										}

										this.d -= dow;
										this.d += this.weekday;
									} else {
										var diff = this.weekday - dow;

										// some PHP magic
										if (this.rd < 0 && diff < 0 || this.rd >= 0 && diff <= -this.weekdayBehavior) {
											diff += 7;
										}

										if (this.weekday >= 0) {
											this.d += diff;
										} else {
											this.d -= 7 - (Math.abs(this.weekday) - dow);
										}

										this.weekday = NaN;
									}
								}

								// adjust relative
								this.y += this.ry;
								this.m += this.rm;
								this.d += this.rd;

								this.h += this.rh;
								this.i += this.ri;
								this.s += this.rs;
								this.f += this.rf;

								this.ry = this.rm = this.rd = 0;
								this.rh = this.ri = this.rs = this.rf = 0;

								var result = new Date(relativeTo.getTime());
								// since Date constructor treats years <= 99 as 1900+
								// it can't be used, thus this weird way
								result.setFullYear(this.y, this.m, this.d);
								result.setHours(this.h, this.i, this.s, this.f);

								if ( this.rwd ) {
									/**
									 * Let's add a day to the current date result until our weekday allowance is exhausted.
									 * If our weekday allowance is a negative number, we will subtract a day instead.
									 */
									var mod = this.rwd < 0 ? -1 : 1;
									var i   = Math.abs( this.rwd );
									while ( i > 0 ) {
										result.setDate( result.getDate() + mod );
										var currentDay = result.getDay();
										if ( currentDay !== 6 && currentDay !== 0 ) {
											i--;
										}
									}
								}

								// note: this is done twice in PHP
								// early when processing special relatives
								// and late
								// todo: check if the logic can be reduced
								// to just one time action
								switch (this.firstOrLastDayOfMonth) {
									case 1:
										result.setDate(1);
										break;
									case -1:
										result.setMonth(result.getMonth() + 1, 0);
										break;
								}

								// adjust timezone
								if (!isNaN(this.z) && result.getTimezoneOffset() !== this.z) {
									result.setUTCFullYear(result.getFullYear(), result.getMonth(), result.getDate());

									result.setUTCHours(result.getHours(), result.getMinutes() + this.z, result.getSeconds(), result.getMilliseconds());
								}

								return result;
							}
						};

						//       discuss at: https://locutus.io/php/strtotime/
						//      original by: Caio Ariede (https://caioariede.com)
						//      improved by: Kevin van Zonneveld (https://kvz.io)
						//      improved by: Caio Ariede (https://caioariede.com)
						//      improved by: A. Matías Quezada (https://amatiasq.com)
						//      improved by: preuter
						//      improved by: Brett Zamir (https://brett-zamir.me)
						//      improved by: Mirko Faber
						//         input by: David
						//      bugfixed by: Wagner B. Soares
						//      bugfixed by: Artur Tchernychev
						//      bugfixed by: Stephan Bösch-Plepelits (https://github.com/plepe)
						// reimplemented by: Rafał Kukawski
						//           note 1: Examples all have a fixed timestamp to prevent
						//           note 1: tests to fail because of variable time(zones)
						//        example 1: strtotime('+1 day', 1129633200)
						//        returns 1: 1129719600
						//        example 2: strtotime('+1 week 2 days 4 hours 2 seconds', 1129633200)
						//        returns 2: 1130425202
						//        example 3: strtotime('last month', 1129633200)
						//        returns 3: 1127041200
						//        example 4: strtotime('2009-05-04 08:30:00+00')
						//        returns 4: 1241425800
						//        example 5: strtotime('2009-05-04 08:30:00+02:00')
						//        returns 5: 1241418600

						if (now == null) {
							now = Math.floor(Date.now() / 1000);
						}

						// the rule order is important
						// if multiple rules match, the longest match wins
						// if multiple rules match the same string, the first match wins
						var rules = [formats.yesterday, formats.now, formats.noon, formats.midnightOrToday, formats.tomorrow, formats.timestamp, formats.firstOrLastDay, formats.backOrFrontOf,
							// formats.weekdayOf, // not yet implemented
							formats.timeTiny12, formats.timeShort12, formats.timeLong12, formats.mssqltime, formats.timeShort24, formats.timeLong24, formats.iso8601long, formats.gnuNoColon, formats.iso8601noColon, formats.americanShort, formats.american, formats.iso8601date4, formats.iso8601dateSlash, formats.dateSlash, formats.gnuDateShortOrIso8601date2, formats.gnuDateShorter, formats.dateFull, formats.pointedDate4, formats.pointedDate2, formats.dateNoDay, formats.dateNoDayRev, formats.dateTextual, formats.dateNoYear, formats.dateNoYearRev, formats.dateNoColon, formats.xmlRpc, formats.xmlRpcNoColon, formats.soap, formats.wddx, formats.exif, formats.pgydotd, formats.isoWeekDay, formats.pgTextShort, formats.pgTextReverse, formats.clf, formats.year4, formats.ago, formats.dayText, formats.relativeTextWeek, formats.relativeText, formats.monthFullOrMonthAbbr, formats.tzCorrection, formats.dateShortWithTimeShort12, formats.dateShortWithTimeLong12, formats.dateShortWithTimeShort, formats.dateShortWithTimeLong, formats.relative, formats.whitespace];

						var result = Object.create(resultProto);

						while (str.length) {
							var longestMatch = null;
							var finalRule = null;

							for (var i = 0, l = rules.length; i < l; i++) {
								var format = rules[i];

								var match = str.match(format.regex);

								if (match) {
									if (!longestMatch || match[0].length > longestMatch[0].length) {
										longestMatch = match;
										finalRule = format;
									}
								}
							}

							if (!finalRule || finalRule.callback && finalRule.callback.apply(result, longestMatch) === false) {
								return false;
							}

							str = str.substr(longestMatch[0].length);
							finalRule = null;
							longestMatch = null;
						}

						return Math.floor(result.toDate(new Date(now * 1000)) / 1000);
					};

					self.init();

				};

				window.GWDates = {

					getFieldTimestamp: function( dateTimeFieldId, formId, $inputs, utcOffset ) {

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

								/*
								 * If the date matches the current date, attach the time to it as well so time calculations
								 * work as expected rather than off of midnight.
								 */
								var now = new Date();

								// Convert now to use the UTC offset from the server.
								now = new Date( now.getTime() + ( now.getTimezoneOffset() * 60000 ) + ( utcOffset * 60 * 60000 ) );

								if (datetime.getDate() == now.getDate() && datetime.getMonth() == now.getMonth() && datetime.getFullYear() == now.getFullYear()) {
									datetime.setHours(now.getHours());
									datetime.setMinutes(now.getMinutes());
									datetime.setSeconds(now.getSeconds());
								}

								var timestamp = datetime === false ? false : datetime.getTime();

								break;

							case 'time':

								var hour        = parseInt( $inputs.eq( 0 ).val() ),
									min         = parseInt( $inputs.eq( 1 ).val() ),
									ampm        = $inputs.eq( 2 ).val(),
									missingData = isNaN( hour ) || isNaN( min ),
									datetime    = missingData ? false : new Date();

								if ( $inputs.eq( 2 ).length ) {
									hours = hour;

									if (ampm.toLowerCase() === 'am') {
										hours += hour === 12 ? -12 : 0;
									} else {
										hours += hour !== 12 ? 12 : 0;
									}

									datetime.setHours( hours );
								} else {
									datetime.setHours( hour );
								}

								datetime.setMinutes( min );

								timestamp = datetime.getTime();

								break;

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
							case 'time':
								var hours   = isNaN( date.getHours() ) ? '' : date.getHours(),
									minutes = isNaN( date.getMinutes() )  ? '' : date.getMinutes(),
									hasAMPM = $inputs.filter( 'select' ).length === 1,
									isPM    = false;

								if ( hasAMPM ) {
									if ( hours === 0 ) {
										hours = 12;
									} else if ( hours > 12 ) {
										hours -= 12;
										isPM   = true;
									} else if ( hours == 12 ) {
										// for 12 PM, the PM display should update
										isPM = true;
									}
								}

								$inputs.eq( 0 ).val( hours );
								$inputs.eq( 1 ).val( GWDates.padDateOrMonth( minutes ) );

								if ( hasAMPM ) {
									if ( isPM ) {
										$inputs.eq( 2 ).find( 'option:last' ).prop( 'selected', true );
									} else {
										$inputs.eq( 2 ).find( 'option:first' ).prop( 'selected', true );
									}
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
			'utcOffset'     => $this->_args['utc_offset'],
			// Keep the format in the `date()` format for JS.
			'format'        => $this->get_format( false ),
		);

		$script = 'new GWPopulateDate( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array_filter( array( 'gw_populate_date', $this->_args['form_id'], $this->_args['source_field_id'], $this->_args['target_field_id'], rgar( $this->_args['modifier'], 'inputId' ) ) ) );

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

