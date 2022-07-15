<?php
/**
 * Gravity Perks // Nested Forms // Send Nested Form notification when child entry is submitted
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_filter( 'gpnf_should_send_notification', function( $should_send_notification, $notification, $context ) {
	return $context == 'child';
}, 10, 3 );
