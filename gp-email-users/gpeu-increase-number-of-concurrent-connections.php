<?php
/**
 * Gravity Perks // Email Users // Increase the Number of Concurrent Connections to 10.
 * https://gravitywiz.com/documentation/gravity-forms-email-users/
 *
 * The default number of concurrent connection is 5. 
 * You can use this snippet to increase it to 10.
 */
add_filter('gpeu_connection_threshold', function( $conn_threshold ) {
	return 10;
}, 10, 1 );
