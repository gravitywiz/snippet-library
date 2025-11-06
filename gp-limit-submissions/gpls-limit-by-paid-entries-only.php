<?php
/**
 * Gravity Perks // Limit Submissions // Limit by Paid Entries Only
 * https://gravitywiz.com/documentation/gravity-forms-limit-submissions
 * 
 * Only count entries with a Paid payment status when enforcing Limit Submissions rules so users with failed payments can submit again.
 */
add_action( 'gpls_before_query', function ( $ruletest ) {

	// Replace '123' with your Form ID. Leave empty to apply to all forms.
	$form_ids = array( 123 );

	if ( ! empty( $form_ids ) && ! in_array( (int) $ruletest->form_id, array_map( 'intval', $form_ids ), true ) ) {
		return;
	}

	static $processed = array();
	$key              = spl_object_hash( $ruletest );

	if ( isset( $processed[ $key ] ) ) {
		return;
	}

	$ruletest->where[] = "( e.payment_status = 'Paid' OR e.payment_status IS NULL OR e.payment_status = '' )";

	$processed[ $key ] = true;
} );
