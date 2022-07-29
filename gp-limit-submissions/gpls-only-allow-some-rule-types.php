<?php
/**
 * Gravity Perks // Limit Submissions // Only Allow Some Rule Types
 * https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 */
add_filter( 'gpls_rule_type_choices', function( $choices ) {

	// Supports "ip", "user", "embed_url", "role", "value".
	$allowed_rule_types = array( 'ip', 'user', 'role' );
	$filtered_choices   = array();

	foreach ( $choices as $choice ) {
		if ( in_array( $choice['value'], $allowed_rule_types ) ) {
			$filtered_choices[] = $choice;
		}
	}

	return $filtered_choices;
} );
