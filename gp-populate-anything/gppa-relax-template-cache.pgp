<?php
/**
 * Gravity Perks // GP Populate Anything // Relax Template Cache To Not Include Field ID
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_process_template_cache_key', function( $cache_key, $field, $object, $template, $template_name, $object_type, $primary_property ) {
	return serialize(
		array(
			$template,
			$object_type->get_object_id( $object, $primary_property ),
		)
	);
}, 10, 7 );
