<?php
/**
 * Gravity Perks // Address Autocomplete // Store Static Google Maps in Entry Meta
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * Store the Map generated on Map Field as a Static Google Map in Entry Meta.
 *
 * Plugin Name:  GP Address Autocomplete
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 * Description:  Store the Map generated on Map Field as a Static Google Map in Entry Meta.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_action( 'gform_after_submission', function ( $entry, $form ) {

	// Target Form ID, and the Address (not Map) Field ID.
	$form_id  = '310';
	$field_id = '3';

	// Values for Static Map Setting
	$zoom    = 13;
	$width   = 640;
	$height  = 480;
	$api_key = 'Enter your Google Maps API Key';

	if ( $form['id'] != $form_id ) {
		return;
	}

	$latitude  = $entry[ 'gpaa_lat_' . $field_id ];
	$longitude = $entry[ 'gpaa_lng_' . $field_id ];
	$url       = "https://maps.googleapis.com/maps/api/staticmap?center={$latitude},{$longitude}&zoom={$zoom}&size={$width}x{$height}&maptype=roadmap&key={$api_key}";

	gform_update_meta( $entry['id'], 'gppa_' . $field_id, $url );
}, 10, 2 );
