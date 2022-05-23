<?php
/**
 * Gravity Perks // Nested Forms // Modify Display Value by Date
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * This experimental snippet allows you to modify the display value of a Date field if the current date is greater than
 * the date saved in the given field.
 *
 * For example, if today was May 23, 2022 and the field's saved date was May 18, 2022, a "✔" would be displayed. For any
 * date on or after May 23, 2022, the saved date would be displayed.
 * 
 * Screenshot: https://gwiz.io/3sRAgu4
 */
add_filter( 'gpnf_display_value_144_3', function( $value, $field, $form, $entry ) {
	if ( strtotime( 'midnight' ) < strtotime( 'midnight', strtotime( rgar( $entry, $field->id ) ) ) ) {
		$value['label'] = '✔';
	}
	return $value;
}, 10, 4 );
