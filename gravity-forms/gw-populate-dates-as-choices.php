<?php
/**
 * Gravity Wiz // Gravity Forms // Populate Dates as Choices
 * https://gravitywiz.com/
 *
 * By default, this snippet will populate the next 14 "valid" dates as choices in any choice-based fields (e.g. Drop Down,
 * Radio Buttons, Checkboxes, etc). Date validity is based on dates that are not specifically excluded or fall on days
 * of the week that are excluded.
 */
// Update "123" to your form ID.
add_filter( 'gform_pre_render_123', function( $form ) {

	// Update "4" to your choice field ID.
	$target_field_id = 4;

	// Update these dates to those you wish to specifically exclude (e.g. holidays).
	$excluded_dates = array(
		'2022-06-22',
		'2022-06-24',
		'2022-06-30',
		'2022-07-04',
	);

	// Update these to days of the week that should be excluded (typically weekend or week days).
	$excluded_days_of_week = array(
		'Saturday',
		'Sunday',
	);

	foreach ( $form['fields'] as &$field ) {
		if ( $field->id != $target_field_id ) {
			continue;
		}
		$choices = array();
		$date    = new DateTime();
		$i       = 14;
		while ( $i > 0 ) {
			$date = $date->modify( '+1 day' );
			if ( in_array( $date->format( 'Y-m-d' ), $excluded_dates ) ) {
				continue;
			}
			if ( in_array( $date->format( 'l' ), $excluded_days_of_week ) ) {
				continue;
			}
			$choices[] = array(
				'text'  => $date->format( 'l, F jS' ),
				'value' => $date->format( 'Y-m-d' ),
			);
			$i--;
		}
		$field->choices = $choices;
	}

	return $form;
} );
