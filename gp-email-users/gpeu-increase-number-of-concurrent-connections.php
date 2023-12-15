<?php
/**
 * Gravity Perks // Email Users // Increase the Number of Concurrent Connections
 * https://gravitywiz.com/documentation/gravity-forms-email-users/
 *
 * The default number of concurrent connections is `5`. Use this filter to increase the that number.
 * Emails will be sent faster but it will create an additional load on your server.
 */
add_filter('gpeu_connection_threshold', function( $conn_threshold ) {
	// Update "10" the desired number of concurrent connections.
	return 10;
} );
