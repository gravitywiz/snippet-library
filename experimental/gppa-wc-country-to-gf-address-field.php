<?php
/**
 * Gravity Perks // Populate Anything // Populate Country from WooCommerce into Gravity Forms Address field
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
// Update "26" to your Address field ID. Leave the ".6" alone.
add_filter( 'gppa_process_template_26.6', function( $template_value, $field, $template_name, $populate, $object, $object_type, $objects, $template ) {
	if ( $template === 'meta_billing_country' ) {
		$countries = GF_Fields::get( 'address' )->get_default_countries();
		$template_value = rgar( $countries, $template_value, $template_value );
	}
	return $template_value;
}, 10, 8 );
