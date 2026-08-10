<?php
/**
 * Gravity Wiz // Gravity Forms // Disable Expired Forms
 * https://gravitywiz.com/
 *
 * Video: https://www.loom.com/share/e2eff9a9b4744f1eb9fc8e1f2e8fd1cb
 */
add_action( 'init', function() {
	$threshold = 1;

	$forms = GFAPI::get_forms();
	foreach ( $forms as $form ) {
		$schedule_end = rgar( $form, 'scheduleEnd' );
		if ( ! $schedule_end ) {
			continue;
		}

		$date = DateTime::createFromFormat( 'm/d/Y', $schedule_end );
		$date->modify( "+{$threshold} days" );

		$current_date = new DateTime();
		if ( $date <= $current_date ) {
			$form['is_active'] = 0;
			GFAPI::update_form( $form );
		}
	}
}, 5 );
