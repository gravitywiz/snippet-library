<?php
/**
 * Gravity Perks // Populate Anything // Diff Modifier for Live Merge Tags
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Use the :diff modifier with Date field merge tags to get the difference between the user-selected date and a static
 * date. Use the :format modifier to retrieve years, months, days or any [support unit](https://www.php.net/manual/en/dateinterval.format.php).
 *
 * # Return the age on Oct 10, 2023 based on the date selected in the Date of Birth field.
 * @{Date of Birth:1:diff[2023-10-01],format[y]}
 *
 * # Return the age in remainder months on Oct 10, 2023 based on the date selected in the Date of Birth field.
 * @{Date of Birth:1:diff[2023-10-01],format[m]}
 */
add_filter( 'gppa_live_merge_tag_value', function( $merge_tag_match_value, $merge_tag, $form, $field_id, $entry_values ) {

	$bits      = explode( ':', $merge_tag );
	$modifiers = gw_parse_modifiers( array_pop( $bits ) );
	$diff      = rgar( $modifiers, 'diff' );

	if ( ! isset( $modifiers['diff'] ) ) {
		return $merge_tag_match_value;
	}

	// If :diff modifier is set but no value is specified, assume we are diffing against today's date.
	if ( $diff === 'diff' ) {
		$diff = date( 'Y-m-d' );
	}
	// Check if $diff contains any non-numeric characters except a dash. If so, assume we've passed a relative date (e.g. +1 year).
	else if ( preg_match( '/[^0-9\-]/', $diff ) ) {
		$diff = date( 'Y-m-d', strtotime( $diff ) );
	}

	$date1 = date_create( rgar( $entry_values, $field_id ) );
	$date2 = date_create( $diff );
	$diff  = date_diff( $date1, $date2 );

	$format = rgar( $modifiers, 'format', 'y' );

	return $diff->format( "%{$format}" );
}, 10, 5 );

if ( ! function_exists( 'gw_parse_modifiers' ) ) {
	function gw_parse_modifiers( $modifiers_str ) {

		preg_match_all( '/([a-z]+)(?:(?:\[(.+?)\])|,?)/i', $modifiers_str, $modifiers, PREG_SET_ORDER );
		$parsed = array();

		foreach ( $modifiers as $modifier ) {

			list( $match, $modifier, $value ) = array_pad( $modifier, 3, null );
			if ( $value === null ) {
				$value = $modifier;
			}

			// Split '1,2,3' into array( 1, 2, 3 ).
			if ( strpos( $value, ',' ) !== false ) {
				$value = array_map( 'trim', explode( ',', $value ) );
			}

			$parsed[ strtolower( $modifier ) ] = $value;

		}

		return $parsed;
	}
}
