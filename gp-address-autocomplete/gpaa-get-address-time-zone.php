<?php
/**
 * Gravity Perks // Address Autocomplete // Get Address Time Zone
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * Get the time zone of the submitted address and save it to the entry.
 */
// Update "123" to the form ID.
add_action( 'gform_pre_submission_filter_123', 'gw_set_timezone', 10, 2 );
function gw_set_timezone( $entry, $form ) {

	// Update "4" to the Address field ID.
	$address_field_id = 4;

	// Update "5" to the ID of the field in which the time zone should be saved.
	$timezone_field_id = 5;

	$location_lat  = rgar( $entry, 'gpaa_lat_' . $address_field_id );
	$location_long = rgar( $entry, 'gpaa_lng_' . $address_field_id );
	$timezone      = gw_get_nearest_timezone( $location_lat, $location_long );

	$_POST[ "input_{$time_zone_field_id}" ] = $timezone;

}

// Thank you, Bramus! https://www.bram.us/2020/07/10/php-convert-a-geolocation-latitude-longitude-to-timezone/
function gw_get_nearest_timezone( $cur_lat, $cur_long, $country_code = '' ) {
	$timezone_ids = ( $country_code ) ? DateTimeZone::listIdentifiers( DateTimeZone::PER_COUNTRY, $country_code ) : DateTimeZone::listIdentifiers();

	if ( $timezone_ids && is_array( $timezone_ids ) && isset( $timezone_ids[0] ) ) {

		$time_zone   = '';
		$tz_distance = 0;

		//only one identifier?
		if ( count( $timezone_ids ) == 1 ) {
			$time_zone = $timezone_ids[0];
		} else {

			foreach ( $timezone_ids as $timezone_id ) {
				$timezone = new DateTimeZone( $timezone_id );
				$location = $timezone->getLocation();
				$tz_lat   = $location['latitude'];
				$tz_long  = $location['longitude'];

				$theta    = $cur_long - $tz_long;
				$distance = ( sin( deg2rad( $cur_lat ) ) * sin( deg2rad( $tz_lat ) ) )
					+ ( cos( deg2rad( $cur_lat ) ) * cos( deg2rad( $tz_lat ) ) * cos( deg2rad( $theta ) ) );
				$distance = acos( $distance );
				$distance = abs( rad2deg( $distance ) );

				if ( ! $time_zone || $tz_distance > $distance ) {
					$time_zone   = $timezone_id;
					$tz_distance = $distance;
				}
			}
		}
		return $time_zone;
	}
	return 'unknown';
}
