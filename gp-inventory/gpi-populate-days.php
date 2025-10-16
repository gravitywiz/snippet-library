<?php
/**
 * Gravity Perks // Inventory // Populate Days into Radio Field
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Populate a Radio field with the next `n` days, and assign each choice to have `x` inventory.
 *
 * For example, you can populate a Radio field with the next 10 Thursdays, with each Thursday having an inventory of 25.
 * This is useful if you offer a service on a specific day only, and need users to select which date they want.
 *
 * Additionally, you can set a cutoff day and time for showing a day this week. For example, you can only show the current
 * week's Thursday if it is before 4pm on Tuesday. This is useful if you need to set a cut-off time for bookings this week.
 *
 * Instructions:
 *
 * 1. Install the snippet.
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Follow the inline instructions to configure the snippet for your form.
 */
add_filter( 'gform_pre_render', 'gw_populate_days_into_radio', 5, 1 );
add_filter( 'gform_pre_validation', 'gw_populate_days_into_radio', 5, 1 );
function gw_populate_days_into_radio( $form ) {

	// Change `123` to your form ID
	if ( (int) rgar( $form, 'id' ) !== 123 ) {
		return $form;
	}

	// Update `1` with your Radio Button field ID
	$field_id = 1;

	// Update `thursday` with the day you want to populate
	$day = 'thursday';

	// Update `2` to your cut-off day for booking this week. Monday is 1, Tuesday is 2 etc.
	$cutoff_day = 2;

	// Update `16` to your cut-off time for booking this week. The time is in 24 hour format, so 16 is 4pm.
	$cutoff_time = 16;

	// Update `10` to the number of days to populate
	$number_of_days = 10;

	// Update `25` to the inventory limit each day should have
	$inventory = 25;

	// Update `l, F j, Y` to the PHP date format you want the populated days to be shown in.
	// More information about formats can be found here: https://www.php.net/manual/en/datetime.format.php
	$format = 'l, F j, Y';

	// That's it, stop editing!

	static $has_run = false;
	if ( $has_run ) {
		return $form;
	}
	$has_run = true;

	foreach ( $form['fields'] as &$field ) {
		if ( $field->id == $field_id && $field->type == 'radio' ) {

			$choices   = array();
			$today     = new DateTime();
			$start_day = new DateTime( 'this ' . $day );

			// If it's past the cutoff, also skip this week's day
			if ( ( $today->format( 'N' ) == $cutoff_day && (int) $today->format( 'H' ) >= $cutoff_time ) || $today->format( 'N' ) > $cutoff_day && $today->format( 'N' ) <= $start_day->format( 'N' ) ) {
				$start_day->modify( '+1 week' );
			}

			// Generate next n days
			for ( $i = 0; $i < $number_of_days; $i++ ) {
				$label     = $start_day->format( $format );
				$choices[] = array(
					'text'            => $label,
					'value'           => $label,
					'inventory_limit' => $inventory,
				);
				$start_day->modify( '+1 week' );
			}

			$field->choices = $choices;
		}
	}

	return $form;
}
