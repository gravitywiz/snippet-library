<?php
/**
 * Gravity Perks // Limit Submissions // Set Limit Period by User Meta
 * https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 */
add_action( 'gpls_before_query', function( $ruletest ) {
	global $wpdb;

	// Update "123" to your form ID.
	if ( $ruletest->form_id != 123 ) {
		return;
	}

	// Update "subscription_start_date" to your user meta key.
	$subscription_start_date = get_user_meta( get_current_user_id(), 'subscription_start_date', true );
	$time_period_sql         = $wpdb->prepare( 'date_created BETWEEN %s AND DATE_ADD( CURDATE(), INTERVAL 1 DAY )', $subscription_start_date );
	$ruletest->where[]       = $time_period_sql;

} );
