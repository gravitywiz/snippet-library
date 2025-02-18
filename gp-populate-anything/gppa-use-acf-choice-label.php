<?php
/**
 * Gravity Perks // Populate Anything // Use Advanced Custom Field's Choice Label
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * By default, Populate Anything will populate the value of any choice-based ACF custom field that is mapped. This snippet
 * allows you to populate the label of an ACF choice (e.g. Radio Button, Checkbox, etc) instead.
 *
 * Instructions
 *
 * 1. Install the snippet.
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Add the CSS class "gppa-use-acf-choice-label" to the CSS Class Name field setting for any field that should use the
 *    ACF choice label.
 */
add_filter( 'gppa_process_template_value', function( $template_value, $field, $template_name, $populate, $object, $object_type, $objects ) {

	if ( strpos( $field->cssClass, 'gppa-use-acf-choice-label' ) === false ) {
		return $template_value;
	}

	$label = get_field( str_replace( 'meta_', '', $field->{'gppa-values-templates'}['value'] ), $object->ID );

	// When the ACF field's return value as Both (Value and Label), the `$label` is an array. So, we need to get the label from the array.
	if ( rgars( $label, '0/label' ) ) {
		$label[0] = $label[0]['label'];
	}

	return $label;
}, 10, 7 );
