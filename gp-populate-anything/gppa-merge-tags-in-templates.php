<?php
/**
 * Gravity Perks // Populate Anything // Use Standard Merge Tags in Choice/Value Templates
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_process_template', function( $template_value, $field, $template, $populate, $object, $object_type, $objects ) {
	return GFCommon::replace_variables( $template_value, GFAPI::get_form( $object->form_id ), (array) $object );
}, 10, 7 );
