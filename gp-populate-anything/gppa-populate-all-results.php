<?php
/**
 * Gravity Perks // Populate Anything // Populate All Results
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instruction Video: https://www.loom.com/share/f324b8477a1140d7808b49b6b553258f
 *
 * By default, when populating a field's value, Populate Anything will only populate the first found result. This
 * snippet adds support for populating all found results. Just add the "gppa-populate-all" CSS class to the field's
 * CSS Class Name setting.
 */
add_filter( 'gppa_process_template_value', function( $template_value, $field, $template_name, $populate, $object, $object_type, $objects, $template ) {
	if ( ! empty( $objects ) && strpos( $field->cssClass, 'gppa-populate-all' ) !== false ) {
		$values = array( $template_value );
		foreach ( $objects as $_object ) {
			if ( $_object === $object ) {
				continue;
			}
			$values[] = gp_populate_anything()->process_template( $field, $template_name, $_object, $populate, array() );
		}
		$template_value = implode( ', ', $values );
	}
	return $template_value;
}, 10, 8 );

// Required by GPPA 2.0+ to ensure that all results are returned.
add_filter( 'gppa_query_all_value_objects', function( $should_query_all, $field ) {
	if ( strpos( $field->cssClass, 'gppa-populate-all' ) !== false ) {
		$should_query_all = true;
	}
	return $should_query_all;
}, 10, 2 );
