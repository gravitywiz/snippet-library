<?php
/**
 * Gravity Perks // Populate Anything // Populate All Results
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instruction Video: https://www.loom.com/share/f324b8477a1140d7808b49b6b553258f
 *
 * By default, when populating a field's value, Populate Anything will only populated the first found result. This
 * snippet adds support for populating all found results. Just add the "gppa-populate-all" CSS class to the field's
 * CSS Class Name setting.
 *
 *
 *
 *
 *
 */
add_filter( 'gppa_process_template_value', function( $template_value, $field, $template_name, $populate, $object, $object_type, $objects, $template ) {
	if ( strpos( $field->cssClass, 'gppa-populate-all' ) !== false ) {
		$values = array();
		foreach ( $objects as $_object ) {
			$values[] = $object_type->get_object_prop_value( $_object, $template );
		}
		$template_value = implode( ', ', $values );
	}
	return $template_value;
}, 10, 8 );
