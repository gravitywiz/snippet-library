<?php
/**
 * Gravity Perks // Populate Anything // Explode Commas into Selected Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Plugin Name:  GPPA Explode Commas into Selected Choices
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description:  Convert comma-delimited values into selected choices when populated into a choice-based field.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 *
 * If you have an array-like value mapped to a choice-based field, Populate Anything does not yet know when it should be
 * converted to choices or imploded into a string so it defaults to the latter.
 *
 * This snippet adds the `gppa-explode-commas-into-selected-choices` designator to all you to specify when array-like values
 * should be converted into choices.
 *
 * See https://github.com/gravitywiz/snippet-library/blob/master/gp-populate-anything/gppa-explode-commas-as-choices.php for using comma-delimited values
 * as the choices rather than selecting choices.
 *
 * Usage:
 *
 * 1. Install this code as a plugin or as a snippet.
 * 2. Add the `gppa-explode-commas-into-selected-choices` designator to your field's CSS Class Name setting.
 */
add_filter( 'gppa_process_template_value', function ( $template_value, $field, $template_name, $populate, $object, $object_type, $objects, $template ) {
	if ( strpos( $field->cssClass, 'gppa-explode-commas-into-selected-choices' ) === false ) {
		return $template_value;
	}

	return array_map( 'trim', explode( ',', $template_value ) );
}, 10, 8 );
